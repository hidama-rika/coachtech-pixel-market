<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // 3. ビューに商品情報とユーザー情報を渡す
        // 以前のロジック（$id_to_find）が重複していたため削除しました
        return view('new_purchases', [
            'item' => $item,
            'user' => $user, // ユーザー情報（住所など）をビューに渡す
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

        //  トランザクション処理を開始 (原子性の確保)
        try {
            DB::transaction(function () use ($item, $user_id, $request) {

                // 【要件 1】 itemsテーブルのis_soldをtrueに更新
                $item->is_sold = true;
                $item->save();

                // 【要件 2】 Purchasesテーブルに購入履歴を作成
                Purchase::create([
                    'item_id' => $item->id,
                    'user_id' => $user_id,
                    'payment_method_id' => $validated['payment_method_id'],

                    // フォームリクエストのフィールド名（shipping_...）から
                    // Purchasesテーブルのカラム名（post_code, address, building_name）へマッピング
                    'post_code' => $validated['shipping_post_code'],
                    'address' => $validated['shipping_address'],
                    'building_name' => $validated['shipping_building'],

                    'transaction_status' => true, // 取引完了ステータス
                ]);
            });

            // 4. 成功したら商品一覧画面へリダイレクト
            return redirect()->route('items.index')->with('success', '商品の購入が完了しました。');

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
