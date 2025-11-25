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
        /** @var User|null $user */
        $user = Auth::user();

        // ユーザーが認証されていない場合は、次の処理に進む（'auth'ミドルウェアが先に処理するため通常は発生しないが保険として）
        if (!$user) {
            return $next($request);
        }

        // Userモデルに定義されたメソッドでプロフィール未登録をチェック
        if ($user->isProfileUnregistered()) {

            $currentRouteName = $request->route()->getName();

            // 現在アクセス中のルートがプロフィール設定画面（profile_edit）ではない、
            // かつ、プロフィール更新処理（mypage.profile.update）でもない場合にリダイレクト
            // これらのルートは未設定でもアクセス可能にする必要がある
            if (!in_array($currentRouteName, ['profile_edit', 'mypage.profile.update'])) {

                // profile_edit登録フォームへ強制リダイレクト
                return redirect()->route('profile_edit')->with('status', 'プロフィール情報の必須項目を設定してください。');
            }
        }

        // プロフィール設定済み、または現在設定画面にいる場合は、リクエストを続行
        return $next($request);
    }
}
