<?php

namespace App\Http\Responses; // 名前空間を Responses に変更

use Illuminate\Http\Resources\Json\JsonResponses;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Redirect; // Redirect ファサードを追加

/**
 * Fortifyによる新規登録が成功した後の応答を定義します。
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * 新規登録が成功した後、レスポンスを組み立てます。
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // ★ APIリクエストの場合の対応:
        // wantsJson()でAPI経由のリクエストか判断し、リダイレクトせずに201 Createdを返します。
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        // 通常のWebリクエストの場合、プロフィール編集画面へリダイレクト
        return redirect()->intended(route('profile_edit'));
    }
}
