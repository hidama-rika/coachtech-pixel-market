<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
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
     * @param Item $item いいねの対象となるアイテム
     * @param Request $request リクエストオブジェクトを追加
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(Item $item) // 💡 Request $request を削除
    {
        // 1. 認証済みユーザーを取得 (元の Auth::user() に戻す)
        $user = Auth::user();

        // ユーザーが認証されていない場合のエラーハンドリング
        if (!$user) {
            return response()->json(['success' => false, 'message' => '認証されていません。'], 401);
        }

        // 2. ユーザーの likes リレーションを使ってトグル処理を実行
        // 修正: $item オブジェクトではなく、明示的に $item->id (キー) を渡すことで、
        // 機能テストでの外部キー制約違反 (SQLSTATE[23000]) を回避します。
        // 💡 $user は null ではないと仮定し、そのまま処理を続行します。
        //    テストが $user が null の状態で通過してしまっていることが問題のため、
        //    $user が null の場合はエラーを出すようにします。
        $toggleResult = $user->likes()->toggle($item->id); // 👈 $item->id に修正

        // 3. トグル後の状態を判定
        // 'attached'（追加された）要素の数で、いいねが登録されたか（true）解除されたか（false）を判定
        $isLiked = count($toggleResult['attached']) > 0;

        // 4. 最新のいいね数を取得
        // 💡 修正: Likeモデルを直接使用して、最新のいいね合計数を取得する
        $likeCount = Like::where('item_id', $item->id)->count();

        // 5. JSONレスポンスの返却
        return response()->json([
            'success' => true,
            'isLiked' => $isLiked, // いいねの状態
            'likeCount' => $likeCount, // 最新のいいね合計数
        ]);
    }
}
