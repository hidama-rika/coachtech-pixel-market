<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // テストに必要なモデル
use App\Http\Middleware\VerifyCsrfToken; // ログアウトテストも同様に、ミドルウェア無効化でCSRFチェックをスキップすることで、本質的なログアウトロジックのみを検証できるようになる。

class ID_3_LogoutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // DBを使用するテストがあるため、リフレッシュします
    use RefreshDatabase;

    /**
     * 各テストメソッドの前に実行され、CSRFミドルウェアを無効化します。
     */
    protected function setUp(): void // ★★★ 追加 ★★★
    {
        parent::setUp();
        // すべてのテストでCSRFミドルウェアを無効にする
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /**
     * ID: 3-1. ログアウトができること
     * 期待される挙動: ログアウト処理が実行される
     * @test
     */
    public function a_logged_in_user_can_logout()
    {
        // 1. 準備 (Arrange)
        // テスト用のユーザーを作成し、そのユーザーとしてログイン状態にします。
        $user = User::factory()->create();

        // $this->actingAs($user) で、このテストの間、ユーザーがログインしている状態をシミュレートします。
        $this->actingAs($user);

        // ログイン状態であることを確認（省略可能ですが、テストの信頼性を高めます）
        $this->assertAuthenticatedAs($user);

        // 2. 実行 (Act)
        // ログアウトのエンドポイント（Laravelのデフォルトは /logout）にPOSTリクエストを送ります。
        $response = $this->post('/logout');

        // 3. 検証 (Assert)

        // ログアウト後、トップページやログインページ（例として / ）にリダイレクトされることを確認
        $response->assertRedirect('/login');

        // ユーザーが非認証状態（ログアウト状態）になったことを確認 (重要な検証)
        $this->assertGuest();
    }
}
