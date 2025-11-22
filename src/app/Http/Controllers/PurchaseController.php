<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
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

        if ($item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みです。');
        }

        // 3. 商品情報の取得（特定したIDがある場合のみ検索を実行）
        if ($id_to_find) {
            // ここでデータベースから商品IDに一致するItemを取得します
            $item = Item::find($id_to_find);

            if (!$item) {
                // 商品が見つからない場合は404エラー
                // 例: http://localhost/purchase?item_id=9999 のような無効なIDの場合
                abort(404, '商品が見つかりませんでした。');
            }
        }

        // ビューに商品情報（見つからなければnull）とユーザー情報を渡します
        return view('new_purchases', [
            'item' => $item,
            'user' => $user, // ユーザー情報（住所など）をビューに渡す
        ]);
    }

    /**
     * 支払い処理を実行する（モック）。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // ここに支払い処理のロジックを実装します
        // 例：バリデーション、Stripeなどの決済API連携、在庫更新など
        // 1. 認証チェックと商品情報の取得
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', '購入にはログインが必要です。');
        }

        $item = Item::findOrFail($item_id); // storeメソッド内で$itemを定義
        $user_id = Auth::id();

        // 2. 最終購入条件のチェック
        if ($user_id === $item->user_id || $item->is_sold) {
            return redirect()->route('items.show', $item->id)->with('error', 'この商品は既に販売済みか、ご自身の出品商品です。');
        }

        // 3. トランザクション処理を開始 (原子性の確保)
        try {
            DB::transaction(function () use ($item, $user_id, $request) {

                // 【要件 1】 itemsテーブルのis_soldをtrueに更新
                $item->is_sold = true;
                $item->save();

                // 【要件 2】 Purchasesテーブルに購入履歴を作成
                // リクエストから支払い・配送情報を取得する（今回はモックとしています）
                Purchase::create([
                    'item_id' => $item->id,
                    'user_id' => $user_id,
                    'payment_method_id' => $request->input('payment_method_id', 1), // フォームから受け取る
                    'shipping_post_code' => $request->input('shipping_post_code', '123-4567'), // フォームから受け取る
                    'shipping_address' => $request->input('shipping_address', '東京都新宿区'), // フォームから受け取る
                    'shipping_building' => $request->input('shipping_building', 'ビル名'), // フォームから受け取る
                    'transaction_status' => true, // 取引完了ステータス
                ]);
            });

            // 4. 成功したら商品一覧画面へリダイレクト (ご要望の通り、基本機能の完了後のリダイレクト先は items.index に変更)
            return redirect()->route('items.index')->with('success', '商品の購入が完了しました。');

        } catch (\Exception $e) {
            \Log::error('購入処理エラー: ' . $e->getMessage());
            // エラー時は商品詳細画面に戻す
            return redirect()->route('items.show', $item->id)->with('error', '購入処理中にエラーが発生しました。時間をおいて再度お試しください。');
        }
    }
}
