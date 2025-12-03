<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Session; // ★追加：セッションを使うために必要★
// use App\Models\Purchase; // ★削除：このコントローラーでPurchaseモデルは不要になる★

class ShippingAddressController extends Controller
{
    /**
     * ★役割変更: 配送先入力フォームを表示する★
     * フォームには、セッションに保存された住所、またはユーザーの基本住所を初期値として渡す
     */
    public function edit()
    {
        // ユーザーの基本住所を取得
        $user = Auth::user();

        // ★修正点1: セッションから取得するキーをDBカラム名に統一★
        $shipping = Session::get('purchase_shipping', [
            'shipping_post_code' => $user->post_code ?? '',
            'shipping_address' => $user->address ?? '',
            'shipping_building' => $user->building_name ?? ''
        ]);

        // セッションから item_id を取得
        $item_id = Session::get('purchasing_item_id', null);

        $lastKeywordForView = session('last_search_keyword') ?? '';

        // ★修正点3: item_idをビューに渡す
        return view('shipping-address_edit', [
            'shipping' => (object)$shipping,
            'item_id' => $item_id,
            'lastKeyword' => $lastKeywordForView
        ]);
    }

    /**
     * ★役割変更: 送付先住所をセッションに一時保存し、購入画面に戻る★
     * updateメソッドからstoreメソッドに改名し、引数($purchase_id)を削除します。
     */
    public function store(Request $request)
    {
        // 1. バリデーション
        $validatedData = $request->validate([
            // リクエストのキー名（フォームのname属性）を使用
            'post_code' => 'required|string|regex:/^\d{3}-?\d{4}$/',
            'address' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'item_id' => 'required|integer|exists:items,id',
        ]);

        // 2 & 3. セッションに一時保存
        // ★修正点3: セッションに保存するキーをDBカラム名に統一★
        $new_address = [
            'shipping_post_code' => $validatedData['post_code'],
            'shipping_address' => $validatedData['address'],
            'shipping_building' => $validatedData['building_name'],
        ];
        Session::put('purchase_shipping', $new_address);

        // 4. 購入画面へリダイレクト
        // 購入画面のルート名が /purchase/10 のような形式であれば、
        // 適切なitem_idを渡す必要があります。ここでは仮に /purchase にリダイレクトします。
        // ★修正点5: item_idをルートパラメータとして渡し、正しい購入画面に戻る★
        return redirect()->route('new_purchases', [
            'item_id' => $validatedData['item_id']
        ])->with('success', '配送先住所が一時的に更新されました。');
    }
}
