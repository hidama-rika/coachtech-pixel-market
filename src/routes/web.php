<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomAuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 開発用表示ルート Route::view('URI', 'ビュー名');
Route::view('/login', 'auth.login');
Route::view('/register', 'auth.register');
Route::view('/address', 'shipping-address_edit');
// Route::view('/', 'index');
Route::view('/mypage/profile', 'profile_edit');
Route::view('/sell', 'new_items');
Route::view('/purchase', 'new_purchases');
Route::view('/mypage', 'mypage');
// Route::view('/item', 'show');




// ホーム画面 (未認証ルート)
Route::get('/', [HomeController::class, 'index'])->name('items.index');

// 商品詳細画面 (未認証ルート)
Route::get('/item/{item}', [HomeController::class, 'show'])->name('items.show');

// Fortifyが提供する認証ルートをここで定義することもできますが、
// 通常はFortifyのインストールと設定により自動的に有効化されます。


// ==========================================================
// ★★★ 認証ルート (Fortify対応) ★★★
// ==========================================================

// ログイン画面の表示 (LoginControllerを使用し、Viewを返す)
Route::get('/login', [CustomAuthenticatedSessionController::class, 'create'])->name('login');

// ログイン処理 (FortifyのPOST /loginをCustomAuthenticatedSessionControllerで上書き)
Route::post('/login', [CustomAuthenticatedSessionController::class, 'store'])
    ->middleware(['web', 'guest']);

// ログアウト処理 (FortifyのPOST /logoutをCustomAuthenticatedSessionControllerで上書き)
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])
    ->middleware(['web'])
    ->name('logout');




// ---認証済みユーザー向けのルート (authミドルウェア)---

Route::middleware('auth')->group(function () {

    // --- (認証済の場合にアクセス可能) ---

    // 商品一覧画面の表示
    // Route::get('/', [ItemController::class, 'index'])
    //     ->name('items.index');

    // --- マイページ・管理系ルート (Route::view を移動・整理) ---

    // マイページトップ
    Route::view('/mypage', 'mypage')->name('mypage.index');

    // プロフィール編集画面
    Route::view('/mypage/profile', 'profile_edit')->name('mypage.profile.edit');

    // 配送先住所編集画面
    Route::view('/address', 'shipping-address_edit')->name('address.edit');

    // 商品出品画面
    Route::view('/sell', 'new_items')->name('items.sell');

    // 購入履歴画面
    Route::view('/purchase', 'new_purchases')->name('purchases.index');

    });