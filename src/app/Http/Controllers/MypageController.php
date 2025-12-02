<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 認証済みユーザーを取得するために必要
use App\Models\Item; // Itemモデルをインポート
use App\Models\Purchase;

class MypageController extends Controller
{
    /**
     * マイページトップ画面を表示する
     */
    public function index(Request $request)
    {
        // 認証済みユーザー情報を取得
        $user = Auth::user();

        // アクティブタブの決定
        // ★ 修正2: URLクエリパラメータから 'page' を取得。なければ 'sell' をデフォルトとする。
        $currentPage = $request->query('page', 'sell');

        // 'listed' か 'purchased' 以外の値が来た場合は 'listed' に矯正 (安全のため)
        if (!in_array($currentPage, ['sell', 'buy'])) {
            $currentPage = 'sell';
        }

        // 1. ログインユーザーが出品した商品を取得
        // Itemテーブルから、user_idが現在のユーザーIDと一致する商品を取得
        $listedItems = Item::where('user_id', $user->id)
            // 新しい商品が上に来るように並び替える
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. ログインユーザーが購入した商品のIDリストを取得し、そのIDに一致するItemを取得
        // Purchaseテーブルは 'user_id' と 'item_id' を持つことを前提としています。
        $purchasedItems = Item::whereIn('id', function ($query) use ($user) {
            // Purchaseテーブルから、現在のユーザーIDが持つ item_id のリストを取得
            $query->select('item_id')
                ->from('purchases')
                ->where('user_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

        $lastKeywordForView = session('last_search_keyword') ?? '';

        // 3. ユーザー情報と商品リストをビューに渡して表示
        return view('mypage', [
            'user' => $user,
            'listedItems' => $listedItems,     // 出品した商品リスト
            'purchasedItems' => $purchasedItems, // 購入した商品リスト
            'currentPage' => $currentPage,        // URLから取得した $currentPage を渡す
            'lastKeyword' => $lastKeywordForView
        ]);
    }
}
