<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; // 自分のカスタムリクエストを使用
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse; // 💡 追加または確認
use Illuminate\Support\Facades\View; // Viewファサードを使用するためのuse宣言
use Illuminate\Support\Facades\Auth; //明示的にインポート

class CustomAuthenticatedSessionController extends Controller
{
    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * 会員登録ビューを表示します (GET /register)。
     * @return \Illuminate\View\View
     */
    public function registerForm()
    {
        // Fortifyが使用するビュー名 'auth.register' をレンダリングします
        return View::make('auth.register');
    }

    /**
     * ログインビューを表示します (GET /login)。
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Fortifyが使用するビュー名 'auth.login' をレンダリングします
        return View::make('auth.login');
    }

    /**
     * 認証セッションをストアします。
     * Fortifyのパイプラインを使いながら、型ヒントをLoginRequestに変更しています。
     */
    public function store(LoginRequest $request) // ここで型エラーを解決しつつFortifyを維持
    {
        // 🚨 重要: AttemptToAuthenticate::class を削除します 🚨
        // ここに到達した時点で LoginRequest の withValidator によって認証は成功しています。

        return app(Pipeline::class)
            ->send($request)
            ->through(array_filter([
                // 1. ログイン試行回数のレートリミットをチェック
                EnsureLoginIsNotThrottled::class,

                // 2. 認証処理はLoginRequestで完了済みのた、AttemptToAuthenticateはスキップ。

                // 3. 認証成功後のセッション準備
                PrepareAuthenticatedSession::class,

                // 4. (必要であれば) 2FAチェック
                // RedirectIfTwoFactorAuthenticatable::class,
            ]))
            ->then(function ($request) {
                // 認証成功後のレスポンス
                return app(LoginResponse::class);
            });
    }

    /**
     * ログアウト処理
     */
    public function destroy(Request $request): RedirectResponse|LogoutResponse // 戻り値の型ヒントを修正
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // ★★★ ログアウト後にログイン画面へリダイレクト ★★★
        return redirect('/login');
    }
}
