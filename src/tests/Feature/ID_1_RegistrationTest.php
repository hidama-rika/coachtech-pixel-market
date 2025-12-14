<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Middleware\VerifyCsrfToken; // ★ この行を追加

class ID_1_RegistrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // DBを使用するテストがあるため、リフレッシュします
    use RefreshDatabase;

    // ★ ここにミドルウェア無効化の行を追加
    protected function setUp(): void
    {
        parent::setUp();
        // すべてのテストでCSRFミドルウェアを無効にする
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    // ----------------------------------------------------
    // バリデーションエラーケース
    // ----------------------------------------------------

    /**
     * ID: 1-1. 名前が入力されていない場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「お名前を入力してください」というバリデーションメッセージが表示される
     * @test
     */
    public function name_is_required_for_registration()
    {
        // 1. 準備 (Arrange): 必須項目だがnameを空にする
        $response = $this->post('/register', [
            'name' => '', // 空
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 2. 検証 (Assert)
        // HTTPステータスが302（リダイレクト=バリデーションエラー）であることを確認
        $response->assertStatus(302);
        // 'name'フィールドのエラーがあることを確認（メッセージの具体的な検証は省略）
        $response->assertSessionHasErrors('name');
    }

    /**
     * ID: 1-2. メールアドレスが入力されていない場合、バリデーションメッセージが表示されること
     * @test
     */
    public function email_is_required_for_registration()
    {
        // 1. 準備 (Arrange): emailを空にする
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '', // 空
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /**
     * ID: 1-3. パスワードが入力されていない場合、バリデーションメッセージが表示されること
     * @test
     */
    public function password_is_required_for_registration()
    {
        // 1. 準備 (Arrange): passwordを空にする
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '', // 空
            'password_confirmation' => '', // 確認用も空
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /**
     * ID: 1-4. パスワードが7文字以下の場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
     * @test
     */
    public function password_must_be_at_least_8_characters()
    {
        // 1. 準備 (Arrange): 7文字のパスワードを設定
        $password_short = 'short77';

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => $password_short,
            'password_confirmation' => $password_short,
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /**
     * ID: 1-5. パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「パスワードと一致しません」というバリデーションメッセージが表示される
     * @test
     */
    public function password_must_match_confirmation()
    {
        // 1. 準備 (Arrange): パスワードと確認用パスワードを不一致にする
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch123', // 不一致
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password'); // 'password_confirmation'ではなく、'password'にエラーが出るのがLaravelの慣習
    }

    // ----------------------------------------------------
    // 正常系ケース
    // ----------------------------------------------------

    /**
     * ID: 1-6. 全ての項目が適切に入力されている場合、会員情報が登録され、プロフィール設定画面に遷移すること
     * @test
     */
    public function registration_is_successful_with_valid_data()
    {
        // 1. 準備 (Arrange): 正しい入力データを設定
        $valid_data = [
            'name' => '新規ユーザー',
            'email' => 'newuser@example.com',
            'password' => 'validpass123',
            'password_confirmation' => 'validpass123',
        ];

        // 2. 実行 (Act)
        $response = $this->post('/register', $valid_data);

        // 3. 検証 (Assert)

        // データベースにユーザーが登録されたことを確認 (教材で学んだ assertDatabaseHas)
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            // パスワードはハッシュ化されるため、ここでは検証しないか、name, emailのみで確認します。
        ]);

        // ユーザーが自動的に認証され（ログイン状態になり）、プロフィール設定画面（例として /mypage/profile にリダイレクト）に遷移することを確認
        // Laravelのデフォルトでは、登録後に /home または定義されたリダイレクト先に遷移するため、リダイレクト先は状況に応じて修正する。
        $response->assertRedirect('/mypage/profile');

        // （補足）認証されたことを確認
        $this->assertAuthenticated();
    }
}
