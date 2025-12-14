<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // ログイン機能テストに必要なモデルを追加
use App\Http\Middleware\VerifyCsrfToken; // ID:1会員登録機能テストで追加したCSRF無効化（ログインでも必要）

class ID_2_LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    // DBを使用するテストがあるためリフレッシュ。トレイトの利用はクラス直下で行う。
    use RefreshDatabase;

    // 事前にテスト用のユーザー情報を定義しておく。プロパティの定義もクラス直下で行う。
    protected $user_data = [
        'name' => 'Test User',
        'email' => 'login_test@example.com',
        'password' => 'password123',
    ];

    /**
     * 各テストメソッドの前に実行され、テスト用ユーザーを作成します。
     * ログインが成功するケースのために、DBにユーザーを登録しておきます。
     */
    // ID:1 の修正と同様に、419エラー（CSRFトークン）を回避するため、
    // setUpメソッドでミドルウェアを無効化。
    protected function setUp(): void
    {
        parent::setUp();
        // すべてのテストでCSRFミドルウェアを無効にする
        $this->withoutMiddleware(VerifyCsrfToken::class);

        // ユーザーをDBに作成
        // $this->user_data がクラスプロパティとして利用可能になる
        User::factory()->create([
            'email' => $this->user_data['email'],
            'password' => bcrypt($this->user_data['password']), // パスワードはハッシュ化
        ]);
    }

    // ----------------------------------------------------
    // バリデーションエラーケース
    // ----------------------------------------------------

    /**
     * ID: 2-1. メールアドレスが入力されていない場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「メールアドレスを入力してください」というバリデーションメッセージが表示される
     * @test
     */
    public function email_is_required_for_login()
    {
        // 1. 準備 (Arrange): emailを空にする
        $response = $this->post('/login', [
            'email' => '', // 空
            'password' => $this->user_data['password'],
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        // 'email'フィールドのエラーがあることを確認
        $response->assertSessionHasErrors('email');
    }

    /**
     * ID: 2-2. パスワードが入力されていない場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「パスワードを入力してください」というバリデーションメッセージが表示される
     * @test
     */
    public function password_is_required_for_login()
    {
        // 1. 準備 (Arrange): passwordを空にする
        $response = $this->post('/login', [
            'email' => $this->user_data['email'],
            'password' => '', // 空
        ]);

        // 2. 検証 (Assert)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    // ----------------------------------------------------
    // 認証失敗ケース
    // ----------------------------------------------------

    /**
     * ID: 2-3. 入力情報が間違っている場合、バリデーションメッセージが表示されること
     * 期待される挙動: 「ログイン情報が登録されていません」というバリデーションメッセージが表示される
     * @test
     */
    public function invalid_credentials_shows_error_message()
    {
        // 1. 準備 (Arrange): 誤ったパスワードを使用
        $response = $this->post('/login', [
            'email' => $this->user_data['email'],
            'password' => 'wrong_password', // 誤ったパスワード
        ]);

        // 2. 検証 (Assert)
        // 認証失敗もリダイレクトされる（Laravelのデフォルト）
        $response->assertStatus(302);
        // 認証セッションエラー（Laravelでは通常'email'フィールドにエラーが出るが、LoginRequestに合わせてpasswordに設定する）
        $response->assertSessionHasErrors('password');
        // ユーザーが認証されていないことを確認
        $this->assertGuest();
    }

    // ----------------------------------------------------
    // 正常系ケース
    // ----------------------------------------------------

    /**
     * ID: 2-4. 正しい情報が入力された場合、ログイン処理が実行されること
     * 期待される挙動: ログイン処理が実行される
     * @test
     */
    public function login_is_successful_with_valid_credentials()
    {
        // 1. 準備 (Arrange): 正しい入力データを使用
        $valid_data = [
            'email' => $this->user_data['email'],
            'password' => $this->user_data['password'], // パスワード
        ];

        // 2. 実行 (Act)
        $response = $this->post('/login', $valid_data);

        // 3. 検証 (Assert)

        // ログイン成功後、指定のページ（Laravelデフォルトでは /home や /）にリダイレクトされることを確認
        $response->assertRedirect('/mypage/profile');

        // ユーザーが認証された（ログイン状態になった）ことを確認 (重要な検証)
        $this->assertAuthenticated();

        // 認証されたユーザーが意図したユーザーであることを確認することもできます
        // $this->assertAuthenticatedAs(User::where('email', $this->user_data['email'])->first());
    }
}

