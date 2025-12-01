<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // ★追加：セッションを使うために必要★
use Illuminate\Support\Facades\Log; // ★追加：エラーログ用★
use Stripe\StripeClient;
// use Stripe\Stripe;
// use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    /**
     * 商品購入画面を表示する。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null $item_id ルートパラメータ（例: /purchase/10）
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request, $item_id)
    {
        // 1. 商品情報の取得
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 2. 購入条件のチェック（自分の出品商品ではないこと、かつ未販売であること）
        if (Auth::id() === $item->user_id) {
            return redirect()->route('items.show', $item->id)->with('error', 'ご自身の出品商品は購入できません。');
        }

        // is_soldプロパティ（DBのカラム）を使って販売済みかチェック
        if ($item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みです。');
        }

        // 配送先住所の初期値設定ロジック
        $shipping = Session::get('purchase_shipping');

        if (!$shipping) {
            // セッションに情報がない場合、ユーザーの基本住所を初期データとして設定
            $initialShipping = [
                // DBのカラム名（post_codeなど）からビュー/セッションが期待するキー名（shipping_...）に変換
                'shipping_post_code' => $user->post_code ?? '',
                'shipping_address' => $user->address ?? '',
                'shipping_building' => $user->building_name ?? '',
            ];
            // この初期データをセッションに保存
            Session::put('purchase_shipping', $initialShipping);
            $shipping = $initialShipping;
        }

        // 配送先住所をセッションから取得 (ビューに渡すためにオブジェクト化)
        $shipping = (object)Session::get('purchase_shipping');

        // これで、ShippingAddressController::edit で item_id を取得できるようになる
        // ★★★ 修正点2: 購入中のitem_idをセッションに保存 ★★★
        Session::put('purchasing_item_id', $item_id);

        $lastKeywordForView = session('last_search_keyword') ?? '';

        // 3. ビューに商品情報、ユーザー情報、配送先情報を渡す
        return view('new_purchases', [
            'item' => $item,
            'user' => $user,
            'shipping' => $shipping, // ★配送先情報をビューに渡す★
            'lastKeyword' => $lastKeywordForView
        ]);
    }

    // -------------------------------------------------------------
    // ★★★ 元の store メソッドを置き換え：Stripe Checkoutセッションを開始 ★★★
    // -------------------------------------------------------------

    /**
     * Stripe Checkoutセッションを作成し、Stripeにリダイレクトする。
     *
     * @param  \App\Http\Requests\PurchaseRequest  $request <- ★ PurchaseRequest を使用 ★
     * @param  int $itemId
     * @return \Illuminate\Http\Response
     */
    public function checkout(PurchaseRequest $request, $itemId) // <- ★ ここも修正 ★
    {
        // 1. バリデーション済みの支払い方法をセッションに保存 ★★★ 修正箇所1: $request->input の取得位置を修正 ★★★
        // バリデーションされたデータはここでアクセス可能
        $selectedPaymentType = $request->input('payment_method'); // payment_method は 'card' または 'konbini'
        Session::put('selected_payment_type', $selectedPaymentType); // セッションに保存

        $item = Item::findOrFail($itemId);

        // 最終購入条件のチェック
        if (Auth::id() === $item->user_id || $item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みか、ご自身の出品商品です。');
        }

        // $secretKey = env('STRIPE_SECRET_KEY'); // 以前の記述
        $secretKey = config('services.stripe.secret'); // ← このように修正

        $stripe = new StripeClient($secretKey);

        try {
            $session = $stripe->checkout->sessions->create([
                // カードとコンビニ決済に対応
                'payment_method_types' => ['card', 'konbini'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => $item->price, // 商品の価格を使用
                        'product_data' => ['name' => $item->name],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',

                // success メソッドで利用するためのメタデータ
                'metadata' => [
                    'item_id' => $item->id,
                    'buyer_id' => Auth::id(),
                ],

                // 成功・キャンセル時のルートにセッションIDを渡す
                'success_url' => route('purchase_success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.cancel'),
            ]);

            // Stripe Checkoutへリダイレクト
            return redirect($session->url, 303);

        } catch (\Exception $e) {
            Log::error('Stripe Checkoutセッション作成エラー: ' . $e->getMessage());
            return back()->with('error', '決済システムの準備中にエラーが発生しました。');
        }
    }

    // -------------------------------------------------------------
    // ★★★ success メソッド (購入記録の作成) ★★★
    // -------------------------------------------------------------

    /**
     * 決済成功後の処理
     */
    public function success(Request $request)
    {
        // ----------------------------------------------------
        // 1. StripeセッションIDの取得
        // ----------------------------------------------------
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            Log::error('SUCCESS METHOD ERROR: Session IDがありません。');
            // セッションIDがない場合は、トップページなどにリダイレクト
            return redirect()->route('items.index')->with('error', '決済情報が見つかりません。');
        }

        // ----------------------------------------------------
        // 1.5. ★★★ 購入者ユーザーの取得（修正点1） ★★★
        // ----------------------------------------------------
        $user = Auth::user(); // 現在ログインしているユーザー（購入者）
        if (!$user) {
             // ログインしていない場合はエラー
            Log::error('購入確定エラー: 認証済みユーザーが見つかりません。');
            return redirect()->route('login')->with('error', 'ログインしてから再度お試しください。');
        }

        // ----------------------------------------------------
        // 2. Stripeクライアントの初期化とセッション情報の取得
        // ----------------------------------------------------
        try {
            // .envからシークレットキーを読み込む
            $secretKey = env('STRIPE_SECRET_KEY');
            if (empty($secretKey)) {
                // キーが空の場合は、明確な例外を投げるか、エラー処理を行う
                throw new \Exception("Stripe Secret Keyが設定されていません。");
            }

            $stripe = new StripeClient($secretKey);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            // ----------------------------------------------------
            // 4. DBトランザクション処理
            // ----------------------------------------------------
            DB::beginTransaction();

            // (A) ItemモデルとユーザーIDの取得
            $itemId = Session::get('purchasing_item_id');
            $item = Item::lockForUpdate()->find($itemId);

            // 念のため商品が存在し、かつ未販売であることを確認
            if (!$item || $item->is_sold) {
                DB::rollBack();
                Log::error('購入処理失敗: 商品IDが見つからないか、既に販売済みです。Item ID: ' . $itemId);
                return redirect()->route('items.index')->with('error', 'この商品は購入できませんでした。');
            }

            // (B) PaymentMethodのIDを動的に決定
            $stripePaymentType = $session->payment_method_types[0]; // Stripeから取得した値を使う
            // ... $dbPaymentName の決定ロジックは $stripePaymentType を使う ...
            // 'payment_method_type' => $stripePaymentType, // Stripeの値を使用

            // ★★★ 修正箇所2: セッションからユーザー選択の支払いタイプを取得 ★★★
            $selectedPaymentType = Session::get('selected_payment_type');
            // 予期せぬエラーでセッションがない場合、Stripeから取得した値を使う
            if (!$selectedPaymentType) {
                $selectedPaymentType = $stripePaymentType;
            }

            // DBの payment_methods テーブルからIDを取得
            // stripePaymentTypeが 'card' または 'konbini' であることを前提とする
            if ($stripePaymentType === 'konbini') {
                $dbPaymentName = 'konbini';
            } elseif ($stripePaymentType === 'card') {
                $dbPaymentName = 'card'; // DBに合わせて 'card'
            } else {
                // 予期せぬ支払いタイプの場合のフォールバック処理 (エラーログを出すなど)
                Log::error("予期せぬStripe支払いタイプを受信しました: " . $stripePaymentType);
                // とりあえず 'カード支払い' に設定するか、エラーを投げる
                $dbPaymentName = 'card';
            }
            $paymentMethod = PaymentMethod::where('name', $dbPaymentName)->first();

            // ★★★ ここに、配送先情報の整形コードを挿入 ★★★

            $shippingData = Session::get('purchase_shipping');

            // (C) 購入記録の作成 (purchasesテーブル)
            Purchase::create([
                'user_id' => $user->id, // 出品者のID
                'item_id' => $item->id,
                // ★★★ 必須カラムを個別に追加します ★★★
                'shipping_post_code' => $shippingData['shipping_post_code'] ?? null,
                'shipping_address' => $shippingData['shipping_address'] ?? null,
                'shipping_building_name' => $shippingData['shipping_building'] ?? null, // purchasesテーブルのカラム名に合わせる
                'payment_method_id' => $paymentMethod->id,
                'price' => $session->amount_total / 100, // 合計金額をセントから円に戻す

                // ★★★ 修正箇所: transaction_status を 1 (完了) で新規作成 ★★★
                'transaction_status' => 1,
            ]);

            // (D) Itemモデルの更新
            $item->update(['is_sold' => true]);

            // (E) トランザクションの確定
            DB::commit();

            // (F) セッションに保存していた一時データをクリア
            Session::forget(['purchase_shipping', 'purchasing_item_id', 'selected_payment_type']);

            $lastKeywordForView = session('last_search_keyword') ?? '';

            // ----------------------------------------------------
            // 5. 成功ビューの表示
            // ----------------------------------------------------
            // ビューファイル名が purchase_success に変更されている前提
            return view('purchase_success')
                ->with('success', '決済手続きが完了しました。') // 成功メッセージを渡す
                ->with('lastKeyword', $lastKeywordForView);    // ← これをチェーンで追加

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack(); // エラー時はロールバック
            }
            Log::error('購入確定エラー: ' . $e->getMessage() . ' on line ' . $e->getLine());

            // ユーザーフレンドリーなエラーメッセージでリダイレクト
            return redirect()->route('items.index')->with('error', '決済処理中に予期せぬエラーが発生しました。');
        }
    }

    /**
     * 決済キャンセル後の画面表示
     */
    public function cancel()
    {
        // 配送先情報が残るためクリア
        Session::forget(['purchase_shipping', 'purchasing_item_id', 'selected_payment_type']);
        return redirect()->route('items.index')->with('error', 'Stripeでの決済がキャンセルされました。');
    }
}

