<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
// ★★★ ログインリクエストを追記 ★★★
use App\Http\Requests\LoginRequest;
// ★★★ ここも追加: Fortifyのオリジナルのリクエストをインポートし、FortifyLoginRequestというエイリアスを付ける ★★★
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LogoutResponse; // FortifyのLogoutResponseをインポート
use Illuminate\Http\RedirectResponse; // リダイレクト処理のためにインポート
use App\Http\Responses\RegisterResponse;
// Laravelのコントラクト（規約）はそのままインポート
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            RegisterResponseContract::class,
            RegisterResponse::class // 👈 App\Http\Resources\RegisterResponseを参照
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
