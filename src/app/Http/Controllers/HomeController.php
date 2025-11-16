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
                    ->withCount('likes') // Likesリレーションの数を likes_count としてカウント
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
        $item->load([
            // ... (コメントのロード)
            'likedUsers', // ここで「いいねしたユーザー」リレーションをロードしている
        ]);

        // ログインユーザーがいいねしているかどうかの確認
        $isLiked = false;

        // いいね合計数の取得 (ロード済みの likedUsers リレーションからカウント)
        $likeCount = $item->likedUsers->count();

        // 商品詳細ビューにデータを渡して表示
        return view('items.show', compact('item', 'isLiked', 'likeCount'));
    }
}