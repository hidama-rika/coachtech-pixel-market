<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomAuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\CommentController;
 // コントローラーをインポート
use App\Http\Controllers\LikeController;

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
// Route::view('/address', 'shipping-address_edit');
// Route::view('/', 'index');
// Route::view('/mypage/profile', 'profile_edit');
// Route::view('/sell', 'new_items');
// Route::view('/purchase', 'new_purchases');
// Route::view('/mypage', 'mypage');
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

    // --------------------------------------------------
    // A. プロフィール編集・更新ルート（ミドルウェア適用外）
    // --------------------------------------------------
    // プロフィールが未設定の場合でもアクセスできなければならないルート
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])
        // ミドルウェアのリダイレクト先として、ルート名を 'profile_edit' に統一
        ->name('profile_edit');

    Route::patch('/mypage/profile', [ProfileController::class, 'update'])
        ->name('mypage.profile.update');

    // --------------------------------------------------
    // B. プロフィール設定強制ミドルウェア適用ルート
    // --------------------------------------------------
    // これらのルートはプロフィール設定が完了するまでアクセスが強制的に阻止される
    Route::middleware(['check.profile.set'])->group(function () {

        // マイページトップ（最終的な遷移先であり、プロフィール設定後にアクセス可能となる）
        Route::get('/mypage', [MypageController::class, 'index'])
            ->name('mypage.index');

        // トップページ（おすすめ一覧）
        Route::get('/recommend', [ItemController::class, 'index'])->name('item.recommend');

        // マイリスト（いいねした商品一覧）
        // ★★★ このルートを追加します ★★★
        Route::get('/mylist', [ItemController::class, 'mylist'])->name('item.mylist');

        // ==========================================================
        // ★★★ 商品出品ルートの修正 ★★★
        // ==========================================================
        // 商品出品フォームの表示 (GET)
        Route::get('/sell', [ItemController::class, 'create'])->name('items.sell');

        // 商品出品データの保存 (POST) - ここで画像を受け取って保存します
        Route::post('/sell', [ItemController::class, 'store'])->name('items.store');
        // ==========================================================

        // 購入履歴画面
        Route::get('/purchase/{item_id}', [PurchaseController::class, 'create'])->name('purchases.create');

        // コメント投稿用のルート (POSTリクエスト)
        // /{item_id}/comments の形式でアクセスできるように定義します
        Route::post('/items/{item_id}/comments', [CommentController::class, 'store'])
            ->name('comment.store');

        // いいねのトグル用ルート
        Route::post('/like/toggle/{item}', [LikeController::class, 'toggleLike'])->name('like.toggle');


        // ==========================================================
        // ★★★ 配送先住所関連のルート (ShippingAddressControllerを使用) ★★★
        // ==========================================================

        Route::get('/address', [ShippingAddressController::class, 'edit'])->name('address.edit');

        Route::patch('/address', [ShippingAddressController::class, 'update'])->name('address.update');

    });
});