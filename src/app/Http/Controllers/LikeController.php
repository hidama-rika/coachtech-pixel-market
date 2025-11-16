<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    /**
     * 指定されたアイテムに対するいいねの状態をトグル（切り替え）し、JSONレスポンスを返します。
     * Route Model Bindingにより、URLパラメータから直接Itemインスタンスを受け取ります。（推奨）
     *
     * @param Item $item いいねの対象となるアイテム
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(Item $item)
    {
        // 1. 認証済みユーザーを取得 (ミドルウェアで認証済みであることを前提とします)
        $user = Auth::user();

        // ユーザーが認証されていない場合のエラーハンドリング
        if (!$user) {
            return response()->json(['success' => false, 'message' => '認証されていません。'], 401);
        }

        // 2. ユーザーの likes リレーションを使ってトグル処理を実行
        // toggle()は、Laravelが提供する最もシンプルでアトミックな多対多操作メソッドです。
        $toggleResult = $user->likes()->toggle($item);

        // 3. トグル後の状態を判定
        // 'attached'（追加された）要素の数で、いいねが登録されたか（true）解除されたか（false）を判定
        $isLiked = count($toggleResult['attached']) > 0;

        // 4. 最新のいいね数を取得
        // Itemモデルの likers リレーションのカウントをリロードして最新値を取得します
        $item->loadCount('likes');
        $likeCount = $item->likes_count; // loadCountを使うと末尾に _count が付く

        // 5. JSONレスポンスの返却
        return response()->json([
            'success' => true,
            'isLiked' => $isLiked, // いいねの状態
            'likeCount' => $likeCount, // 最新のいいね合計数
        ]);
    }
}
