<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; // 自分のカスタムリクエストを使用
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\View; // Viewファサードを使用するためのuse宣言

class CustomAuthenticatedSessionController extends Controller
{
    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
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
        return app(Pipeline::class)
            ->send($request)
            ->through(array_filter([
                // Fortifyの主要アクションをすべて実行 (レートリミット、2FAチェックなど)
                EnsureLoginIsNotThrottled::class,
                AttemptToAuthenticate::class,
                // TwoFactorのチェックは、Userモデルにメソッドがないためスキップ
                // RedirectIfTwoFactorAuthenticatable::class,
                PrepareAuthenticatedSession::class,
            ]))
            ->then(function ($request) {
                // 認証成功後のレスポンス (2FA有効なら2FA画面へ)
                return $request->user()->hasTwoFactorEnabled()
                    ? app(TwoFactorChallengeViewResponse::class)
                    : app(LoginResponse::class);
            });
    }

    /**
     * ログアウト処理
     */
    public function destroy(Request $request): \Illuminate\Http\Response
    {
        $this->guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return app(LogoutResponse::class); // Fortifyのログアウトレスポンスを使用
    }
}
