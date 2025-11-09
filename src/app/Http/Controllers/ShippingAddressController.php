<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Purchase;

class ShippingAddressController extends Controller
{
    /**
     * 基本住所の編集フォームを表示する
     */
    public function edit()
    {
        $user = Auth::user();

        return view('shipping-address_edit', ['user' => $user]);
    }

    /**
     * 新規配送先登録フォームを表示する。
     * ユーザーの基本住所（usersテーブル）を初期値として設定する。
     */
    public function create()
    {
        // ユーザーの基本住所を取得
        $user = Auth::user();

        // 【修正】$userオブジェクトをそのままBladeに渡す
        return view('shipping-addresses_edit.create', [
            'user' => $user
        ]);
    }

    /**
     * 新しい配送先をデータベースに保存する（usersテーブルの基本住所更新を想定した仮実装）
     * * このフォームが usersテーブルの基本住所を更新する用途だと仮定して実装しています。
     */
    public function update(Request $request)
    {
        $request->validate([
            // 'name' => 'required|string|max:255',
            'post_code' => 'required|string|regex:/^\d{3}-?\d{4}$/', // 例: 郵便番号のバリデーション
            'address' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // usersテーブルの基本住所を更新するロジック
        $user->update([
            // 'name' => $request->name,
            'post_code' => $request->post_code,
            'address' => $request->address,
            'building_name' => $request->building_name,
        ]);

        return redirect('/purchase')->with('success', '基本住所が更新されました。');
    }
}
