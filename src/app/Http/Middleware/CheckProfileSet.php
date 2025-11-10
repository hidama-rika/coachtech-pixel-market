<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileSet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // ユーザーが認証されていない場合は、次の処理に進む
        if (!Auth::check()) {
            return $next($request);
        }

        // 現在認証されているユーザーを取得
        // Userモデルはファイル冒頭で use App\Models\User; されているので直接使えます
        $user = Auth::user();

        // 💡 修正: usersテーブルのpost_codeまたはaddressが空かどうかでプロフィール設定をチェック
        // ※ 実際にどのフィールドを必須としているかに合わせて条件を変更してください
        $profileNotSet = empty($user->post_code) || empty($user->address);

        // プロフィール情報が設定されていない場合
        if ($profileNotSet) {

            $currentRouteName = $request->route()->getName();

            // 現在アクセス中のルートがプロフィール設定画面（profile_edit）または
            // プロフィール保存処理（register.target.save）ではない場合にリダイレクトする
            // ※ ルート名はアプリケーションの設定に合わせて確認・修正してください
            if (!in_array($currentRouteName, ['profile_edit', 'register.target.save'])) {

                // profile_edit登録フォームへ強制リダイレクト
                return redirect()->route('profile_edit')->with('status', 'プロフィールを先に設定してください。');
            }
        }

        // プロフィール設定済み、または現在設定画面にいる場合は、リクエストを続行
        return $next($request);
    }
}
