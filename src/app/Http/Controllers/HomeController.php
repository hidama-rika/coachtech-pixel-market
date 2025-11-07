<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * ホーム画面 (商品一覧) を表示する
     * 未認証ユーザーもアクセス可能な、サービス紹介を兼ねたエントリポイント。
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        // 販売ステータスが未販売（is_sold = 0/false）の商品のみを取得
        // 最新の商品が上に来るように降順で取得
        $items = Item::where('is_sold', false)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 'items.index' ビューにデータを渡して表示（トップページ）
        return view('items.index', compact('items'));
    }

    /**
     * 商品詳細画面を表示する
     * 未認証ユーザーもアクセス可能。
     *
     * @param  \App\Models\Item  $item ルートモデルバインディングにより自動注入
     * @return \Illuminate\View\View
     */
    public function show(Item $item)
    {
        // 商品詳細ビューにデータを渡して表示
        return view('items.show', compact('item'));
    }
}
