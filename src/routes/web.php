<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomAuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\CommentController;
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
// Route::view('/login', 'auth.login');
// Route::view('/register', 'auth.register');
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

// ==========================================================
// ★★★ Stripeリダイレクトルート (認証不要) - ここに移動します ★★★
// ==========================================================

// ✅ Stripe決済成功後のリダイレクト先（外部リクエストのため認証不要）
Route::get('/purchase/success', [App\Http\Controllers\PurchaseController::class, 'success'])->name('purchase_success');

// ✅ Stripe決済キャンセル後のリダイレクト先 (キャンセル時はGETリクエストで戻る)
Route::get('/purchase/cancel', [PurchaseController::class, 'cancel'])->name('purchase_cancel');
// 💡 注意: ルート名を 'purchase.cancel' から 'purchase_cancel' に変更し、統一します。

// ==========================================================

// Fortifyが提供する認証ルートをここで定義することもできますが、
// 通常はFortifyのインストールと設定により自動的に有効化されます。


// ==========================================================
// ★★★ 認証ルート (Fortify対応) ★★★
// ==========================================================

// 会員登録画面の表示 (CustomAuthenticatedSessionControllerを使用)
Route::get('/register', [CustomAuthenticatedSessionController::class, 'registerForm'])->name('register');

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
    // A. プロフィール編集・更新ルート（verifiedミドルウェアを追加）
    // --------------------------------------------------
    // プロフィール設定自体も、メール認証が完了するまでアクセスできないようにする
    Route::middleware('verified')->group(function () { // ★★★ verifiedミドルウェアを追加 ★★★

        Route::get('/mypage/profile', [ProfileController::class, 'edit'])
            ->name('profile_edit');

        Route::patch('/mypage/profile', [ProfileController::class, 'update'])
            ->name('mypage.profile.update');
    });

    // --------------------------------------------------
    // B. プロフィール設定強制ミドルウェア適用ルート
    // --------------------------------------------------
    // これらのルートはプロフィール設定が完了するまでアクセスが強制的に阻止される
    Route::middleware(['verified', 'check.profile.set'])->group(function () {

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
        Route::get('/purchase/address/{item_id}', [ShippingAddressController::class, 'edit'])->name('shipping_session.edit');

        // ★★★ 修正後の挿入場所: authミドルウェアの内側、かつ check.profile.setグループの外側 ★★★
        // 購入確定処理 (POST) /purchase は削除し、Stripe Checkout開始に置き換え
        // ✅ Stripe Checkoutセッション開始ルート (POSTリクエストでStripeへ遷移)
        Route::post('/purchase/checkout/{item_id}', [PurchaseController::class, 'checkout'])->name('checkout.start');

        // 送付先一時保存処理 (storeメソッドが担当)
        // POSTに変更するのが理想ですが、元のPATCHを踏襲しつつ、storeを呼び出す形に修正
        // ただし、/addressというURIはeditと重複しているため、URIも変更します。
        Route::patch('/purchase/address/store', [ShippingAddressController::class, 'store'])->name('shipping_session.store');
    });
});
