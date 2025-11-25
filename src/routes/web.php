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
use Laravel\Fortify\Fortify; // Fortifyのuseステートメント

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
// Route::view('/verify-email', 'auth.verify-email');
// Route::view('/address', 'shipping-address_edit');
// Route::view('/', 'index');
// Route::view('/mypage/profile', 'profile_edit');
// Route::view('/sell', 'new_items');
// Route::view('/purchase', 'new_purchases');
// Route::view('/mypage', 'mypage');
// Route::view('/item', 'show');




// ==========================================================
// ★★★ 商品一覧・検索機能の修正（ItemControllerを使用） ★★★
// ==========================================================

// ホーム画面 (商品一覧/検索結果) - 未認証/認証ユーザー両方アクセス可能
// ItemController の index メソッドを使用し、クエリパラメータ (keyword) を受け付ける
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品詳細画面 (未認証ルート) - ItemControllerに変更
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

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

// resources/views/auth/verify-email.blade.php を表示する、Fortify::verifyEmailView の設定ブロックは削除し、AppServiceProviderに移動




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
    Route::middleware(['verified', 'check.profile.set'])->group(function () {

        // マイリスト（いいねした商品一覧）
        // ★★★ このルートを追加します ★★★
        Route::get('/mylist', [ItemController::class, 'mylist'])->name('items.mylist');

        // マイページトップ（最終的な遷移先であり、プロフィール設定後にアクセス可能となる）
        Route::get('/mypage', [MypageController::class, 'index'])
            ->name('mypage');


        // ==========================================================
        // ★★★ 商品出品ルートの修正 ★★★
        // ==========================================================
        // 商品出品フォームの表示 (GET)
        Route::get('/sell', [ItemController::class, 'create'])->name('items.sell');

        // 商品出品データの保存 (POST) - ここで画像を受け取って保存します
        Route::post('/sell', [ItemController::class, 'store'])->name('items.store');
        // ==========================================================

        // 購入手続き画面の表示
        Route::get('/purchase/{item_id}', [PurchaseController::class, 'create'])->name('new_purchases');

        // 購入確定処理 (POST)
        Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');

        Route::get('/purchase', function () {
            return redirect()->route('items.index'); // このルートが存在しない場合、エラーの参照元になる
        });

        // コメント投稿用のルート (POSTリクエスト)
        // /{item_id}/comments の形式でアクセスできるように定義します
        Route::post('/items/{item_id}/comments', [CommentController::class, 'store'])
            ->name('comment.store');

        // いいねのトグル用ルート
        Route::post('/like/toggle/{item}', [LikeController::class, 'toggleLike'])->name('like.toggle');


        // ==========================================================
        // ★★★ 配送先住所関連のルート (セッション一時保存用) ★★★
        // ==========================================================

        // 送付先変更フォームの表示 (editメソッドが担当)
        Route::get('/address/edit', [ShippingAddressController::class, 'edit'])->name('shipping_session.edit');

        // 送付先一時保存処理 (storeメソッドが担当)
        // POSTに変更するのが理想ですが、元のPATCHを踏襲しつつ、storeを呼び出す形に修正
        // ただし、/addressというURIはeditと重複しているため、URIも変更します。
        Route::patch('/address/store', [ShippingAddressController::class, 'store'])->name('shipping_session.store');

        // 既存の '/address' ルートは混乱を招くため削除または修正が必要です。
        // Route::get('/address', [ShippingAddressController::class, 'edit'])->name('address.edit'); // ❌ 削除または上記に統合
    });
});