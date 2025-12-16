<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

class ID_16_EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストの準備
     */
    protected function setUp(): void
    {
        parent::setUp();
        // 通知（メール）の送信を偽装
        Notification::fake();
    }

    // 共通のテストユーザーデータ
    private function getTestUserData()
    {
        return [
            'name' => '認証テストユーザー',
            'email' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    /**
     * ID: 16-1. 会員登録後、認証メールが送信されること
     *
     * @test
     */
    public function after_register_verification_email_is_sent()
    {
        $userData = $this->getTestUserData();

        // 1. Act: 会員登録処理
        $response = $this->post('/register', $userData);

        // 2. Assert: 登録とメール送信の検証

        // a. データベースにユーザーが作成されたことを確認 (未認証状態)
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'email_verified_at' => null, // 未認証であることを確認
        ]);

        // b. 認証メールがユーザーに送信されたことを確認
        $user = User::where('email', $userData['email'])->first();
        Notification::assertSentTo($user, VerifyEmail::class);

        // c. プロフィール設定画面へリダイレクトされたことを確認 (実際の動作に合わせる)
        $response->assertRedirect(route('profile_edit'));
    }

    /**
     * ID: 16-2. メール認証誘導画面の表示と、メール認証サイトへの遷移（仮想）
     * * ※「認証はこちらから」ボタンは、実際はメール内のリンクを促すダミーであり、
     * ここでは、認証誘導画面の表示と、再送ボタンの存在（フォーム）を確認します。
     * MailHogへの遷移は、メール内のリンクを踏む行為でエミュレートされます。
     * * @test
     */
    public function verification_notice_page_is_displayed()
    {
        // 1. Arrange: 未認証ユーザーを作成し、ログイン
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);

        // 2. Act: メール認証誘導画面へアクセス (Fortifyにより誘導されるルート)
        // 通常は /email/verify にアクセスすると表示されます
        $response = $this->get(route('verification.notice'));

        // 3. Assert: 画面内容の検証
        $response->assertStatus(200);
        $response->assertSee('メール認証誘導画面', false); // 画面タイトルの検証 (Viewのコメント参照)
        $response->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');

        // 「認証はこちらから」ボタン（aタグ）が表示されていることを確認
        // Viewの出力に合わせて、href="http://localhost:8025" にする。
        $response->assertSee('<a href="http://localhost:8025" class="verify-email-btn">', false);

        // 認証メールを再送するフォームが存在することを確認
        $response->assertSeeInOrder([
            '<form method="POST" action="'.route('verification.send').'" class="resend-form">',
            '認証メールを再送する',
        ], false);
    }

    /**
     * ID: 16-3. メール認証を完了すると、プロフィール設定画面に遷移する
     *
     * @test
     */
    public function complete_verification_redirects_to_profile_edit()
    {
        // 1. Arrange: 未認証ユーザーを作成
        $user = User::factory()->create([
            'email' => 'verify@example.com',
            'email_verified_at' => null,
        ]);

        // 認証URLを取得
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // 2. Act: メール認証リンクへアクセス（MailHogのメール内のリンクを踏む行為をエミュレート）
        $authResponse = $this->actingAs($user)->get($verificationUrl);

        // 3. Assert: 認証完了と遷移の検証

        // a. ユーザーが認証済みになったことを確認
        $this->assertNotNull($user->fresh()->email_verified_at, 'メールアドレスが認証済みになったこと');

        // b. プロフィール設定画面（/mypage/profile）へリダイレクトされたことを確認
        // ★修正点: Fortifyの認証完了後のリダイレクトは通常 ?verified=1 が付く
        $authResponse->assertRedirect(route('profile_edit', ['verified' => 1]));

        // c. リダイレクト先の画面表示を検証（プロフィール設定画面）
        $profileResponse = $this->actingAs($user)->get(route('profile_edit'));

        // プロフィール設定画面のタイトルを確認
        $profileResponse->assertSee('プロフィール設定', false); // image_46709d.png参照
    }
}
