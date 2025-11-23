<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // ★追加：セッションを使うために必要★
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

        // 3. ビューに商品情報、ユーザー情報、配送先情報を渡す
        return view('new_purchases', [
            'item' => $item,
            'user' => $user,
            'shipping' => $shipping, // ★配送先情報をビューに渡す★
        ]);
    }

    /**
     * 支払い処理を実行する。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PurchaseRequest $request)
    {
        // PurchaseRequestが注入されているため、バリデーションと認可（Auth::check()）は既に完了しています。
        // ここでの冗長な validate() や Auth::check() の確認は不要です。

        $validated = $request->validated();
        $item_id = $validated['item_id'];

        // $itemを再度取得（$item_idはバリデーション済み）
        $item = Item::findOrFail($item_id);
        $user_id = Auth::id();

        //  最終購入条件のチェック
        if ($user_id === $item->user_id || $item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みか、ご自身の出品商品です。');
        }

        // 🚨 修正点1: Session Fallback のキーを 'shipping_' に合わせる 🚨
        // セッションにない場合、フォームデータ（$validated）をフォールバックとして使用する。
        $shipping = Session::get('purchase_shipping', [
            'shipping_post_code' => $validated['shipping_post_code'],
            'shipping_address' => $validated['shipping_address'],
            'shipping_building' => $validated['shipping_building'] ?? null,
        ]);

        // データを取得した後、セッションをクリアする（一度きりの注文のため）
        Session::forget('purchase_shipping');
        Session::forget('purchasing_item_id'); // 購入完了に伴い、item_idもクリア

        // 🚨 修正点2: トランザクションの use 変数に $validated と $shipping を追加 🚨
        // トランザクション処理を開始 (原子性の確保)
        try {
            DB::transaction(function () use ($item, $user_id, $validated, $shipping) {

                // 【要件 1】 itemsテーブルのis_soldをtrueに更新
                // ★修正箇所: $fillableに依存しない、より確実な更新方法を使用する
                Item::where('id', $item->id)->update(['is_sold' => true]);

                // 【要件 2】 Purchasesテーブルに購入履歴を作成
                Purchase::create([
                    'item_id' => $item->id,
                    'user_id' => $user_id,
                    'payment_method_id' => $validated['payment_method_id'],

                    // 🚨 修正点3: Purchasesテーブルのカラム名（shipping_...）に合わせる 🚨
                    // セッションキーとDBカラム名が一致していることを確認（マイグレーションファイルに基づく）
                    'shipping_post_code' => $shipping['shipping_post_code'],
                    'shipping_address' => $shipping['shipping_address'],
                    'shipping_building' => $shipping['shipping_building'] ?? null,

                    'transaction_status' => true, // 取引完了ステータス
                ]);
            });

            // 4. 成功したら商品詳細画面へリダイレクト
            // ★★★ 修正点: $item->id を引数として渡す必要があります ★★★
            return redirect()->route('items.show', $item->id)->with('success', '商品の購入が完了しました。');

        } catch (\Exception $e) {
            // エラーログを記録
            \Log::error('購入処理エラー: ' . $e->getMessage());

            // エラー時は商品詳細画面に戻す
            return redirect()->route('items.show', $item->id)
                            ->withInput()
                            ->with('error', '購入処理中にエラーが発生しました。時間をおいて再度お試しください。');
        }
    }
}
