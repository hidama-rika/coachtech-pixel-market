<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ここを修正！Fortifyのクラスに「as RegisterResponseContract」で別名を設定
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse; // 自分のカスタムクラスをインポート
use Laravel\Fortify\Fortify; // Fortifyを追加
use Illuminate\Support\Facades\URL; // URLクラスを追加

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Fortifyのデフォルト設定を、カスタムクラスで上書きします
        // 第1引数: Fortifyのインターフェース
        // 第2引数: 実際に使いたいカスタム応答クラス
        $this->app->singleton(
            RegisterResponseContract::class, // Fortifyのインターフェース（RegisterResponseContract::class）
            RegisterResponse::class          // 自分で作成したクラス（RegisterResponse::class）
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // URLのスキーマを強制的にhttpsにする設定（環境がprodの場合など）
        // if (config('app.env') === 'prod') {
        //     URL::forceScheme('https');
        // }

        // ★★★ Fortifyのカスタムビュー設定をここに移動 ★★★
        // Fortifyのメール認証画面として使用するビューを指定します
        Fortify::verifyEmailView(function () {
            // resources/views/auth/verify-email.blade.php を表示する
            // ここで返すビューは resources/views/auth/verify-email.blade.php または resources/views/verify-email.blade.php のどちらか適切な方
            return view('auth.verify-email');
        });
        // ★★★ ここまでFortifyのカスタムビュー設定 ★★★
    }
}
