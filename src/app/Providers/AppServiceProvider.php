<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ここを修正！Fortifyのクラスに「as RegisterResponseContract」で別名を設定
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse; // 自分のカスタムクラスをインポート

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
        //
    }
}
