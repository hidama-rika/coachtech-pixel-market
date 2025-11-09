<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * 商品購入画面を表示する。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null $item_id ルートパラメータ（例: /purchase/10）
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request, $item_id = null)
    {
        // 1. ユーザー情報の取得
        $user = Auth::user();

        // 2. IDの特定（ルートパラメータが優先、次にクエリパラメータ）
        $id_to_find = $item_id ?? $request->query('item_id');

        $item = null;

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

        // 購入完了後のリダイレクト
        return redirect()->route('mypage.index')->with('success', '商品を購入しました。');
    }
}
