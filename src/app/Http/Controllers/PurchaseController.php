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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * 商品購入画面を表示する。
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

        // ★★★ 修正点2: 購入中のitem_idをセッションに保存 ★★★
        Session::put('purchasing_item_id', $item_id);

        $lastKeywordForView = session('last_search_keyword') ?? '';

        // 3. ビューに商品情報、ユーザー情報、配送先情報を渡す
        return view('new_purchases', [
            'item' => $item,
            'user' => $user,
            'shipping' => $shipping, // ★配送先情報をビューに渡す★
            'lastKeyword' => $lastKeywordForView,
        ]);
    }

    // -------------------------------------------------------------
    // ★★★ Stripe Checkoutセッションを開始 ★★★
    // -------------------------------------------------------------

    /**
     * Stripe Checkoutセッションを作成し、Stripeにリダイレクトする。
     */
    public function checkout($item_id, PurchaseRequest $request)
    {
        // 1. バリデーション済みの支払い方法IDを取得し、セッションに保存
        $paymentMethodId = $request->input('payment_method_id');
        Session::put('selected_payment_type', $paymentMethodId);

        $item = Item::findOrFail($item_id);

        // 最終購入条件のチェック
        if (Auth::id() === $item->user_id || $item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みか、ご自身の出品商品です。');
        }

        $secretKey = config('services.stripe.secret');

        $stripe = new StripeClient($secretKey);

        // 2. IDからStripeの payment_method_types に必要な値に変換
        $stripePaymentType = '';
        if ($paymentMethodId == 1) {
            $stripePaymentType = 'konbini'; // ID 1 = コンビニ払い
        } elseif ($paymentMethodId == 2) {
            $stripePaymentType = 'card'; // ID 2 = カード支払い
        } else {
            // IDが見つからない場合のエラー処理
            Log::error('無効な支払い方法IDを受信: ' . $paymentMethodId);
            return back()->with('error', '無効な支払い方法が選択されました。');
        }

        try {
            $session = $stripe->checkout->sessions->create([

                // 修正: ユーザーが選択した支払い方法のみを許可するように動的に設定
                'payment_method_types' => [$stripePaymentType],

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'unit_amount' => $item->price,
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
                'cancel_url' => route('purchase_cancel'),
            ]);

            // Stripe Checkoutへリダイレクト
            return redirect($session->url, 303);

        } catch (\Exception $e) {
            Log::error('Stripe Checkoutセッション作成エラー: ' . $e->getMessage());
            return back()->with('error', '決済システムの準備中にエラーが発生しました。');
        }
    }

    // -------------------------------------------------------------
    // ★★★ success メソッド (購入記録の作成) - 修正済み ★★★
    // -------------------------------------------------------------

    public function success(Request $request)
    {
        // 1. StripeセッションIDの取得
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            Log::error('SUCCESS METHOD ERROR: Session IDがありません。');
            return redirect()->route('items.index')->with('error', '決済情報が見つかりません。');
        }

        try {
            // 2. Stripeクライアントの初期化とセッション情報の取得
            $secretKey = config('services.stripe.secret');
            if (empty($secretKey)) {
                throw new \Exception("Stripe Secret Keyが設定されていません。");
            }
            $stripe = new StripeClient($secretKey);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            // 3. メタデータから購入者IDと商品IDを取得
            $metadata = $session->metadata;
            $buyerId = $metadata['buyer_id'] ?? null;
            $itemId = $metadata['item_id'] ?? null;

            // 購入者ユーザーの取得
            if (!$buyerId || !($user = User::find($buyerId))) {
                Log::error('購入確定エラー: メタデータに無効な購入者IDが見つかりました。ID: ' . $buyerId);
                return redirect()->route('items.index')->with('error', '購入者情報が見つからなかったため、処理を中断しました。');
            }

            // 4. DBトランザクション処理
            DB::beginTransaction();

            // (A) Itemモデルの取得とロック
            $item = Item::lockForUpdate()->find($itemId);

            // 念のため商品が存在し、かつ未販売であることを確認
            if (!$item || $item->is_sold) {
                DB::rollBack();
                Log::error('購入処理失敗: 商品IDが見つからないか、既に販売済みです。Item ID: ' . $itemId);
                return redirect()->route('items.index')->with('error', 'この商品は購入できませんでした。（販売済み）');
            }

            // (B) PaymentMethodのIDの決定
            $stripePaymentType = $session->payment_method_types[0];
            $dbPaymentName = ($stripePaymentType === 'konbini') ? 'konbini' : 'card';
            $paymentMethod = PaymentMethod::where('name', $dbPaymentName)->first();

            // 配送先情報の取得
            $shippingData = Session::get('purchase_shipping');

            // セッションに配送先情報がない場合のフォールバック（ユーザーの基本情報を使う）
            if (empty($shippingData)) {
                $shippingData = [
                    'shipping_post_code' => $user->post_code ?? null,
                    'shipping_address' => $user->address ?? null,
                    'shipping_building' => $user->building_name ?? null,
                ];
            }

            // (C) 購入記録の作成 (purchasesテーブル)
            Purchase::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                // ★★★ 配送先情報 ★★★
                // purchasesテーブルのカラム名に合わせる
                'shipping_post_code' => $shippingData['shipping_post_code'] ?? null,
                'shipping_address' => $shippingData['shipping_address'] ?? null,
                'shipping_building_name' => $shippingData['shipping_building'] ?? null,
                'payment_method_id' => $paymentMethod->id,
                'price' => $session->amount_total / 100,
                // 合計金額をセントから円に戻す (Stripeの価格表現に対応)
                'transaction_status' => 1,
            ]);

            // (D) Itemモデルの更新
            $item->update(['is_sold' => true]);

            // (E) トランザクションの確定
            DB::commit();

            // (F) セッションに保存していた一時データをクリア
            Session::forget(['purchase_shipping', 'purchasing_item_id', 'selected_payment_type']);

            $lastKeywordForView = session('last_search_keyword') ?? '';

            // 5. 成功ビューの表示
            return view('purchase_success')
                ->with('success', '決済手続きが完了しました。')
                ->with('lastKeyword', $lastKeywordForView);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
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