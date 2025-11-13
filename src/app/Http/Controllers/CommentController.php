<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment; // Commentモデルをインポート
use App\Models\Item; // Itemモデルをインポート

class CommentController extends Controller
{
    /**
     * 新しいコメントを保存する
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $item_id コメント対象のアイテムID
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, $item_id)
    {
        // バリデーション（コメント内容が必須であることなどを確認）
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        // コメントを作成し、保存する
        Comment::create([
            'user_id' => Auth::id(), // 認証ユーザーのID
            'item_id' => $item_id,   // コメント対象のアイテムID
            'comment' => $request->comment, // コメント内容
        ]);

        // 💡 修正箇所 💡
        // JavaScript (Ajax) によるフォーム送信に対応するため、
        // リダイレクトではなく JSON レスポンス (200 OK) を返します。
        // これにより、フロントエンドの JavaScript が成功を検知してページをリロードします。
        return response()->json(['message' => 'コメントが投稿されました'], 200);

        // コメント投稿後、商品詳細画面にリダイレクトする
        // return redirect()->route('item.show', ['item' => $item_id])->with('message', 'コメントを投稿しました。');
    }
}
