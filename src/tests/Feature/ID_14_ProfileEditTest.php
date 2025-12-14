<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;

class ID_14_ProfileEditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    /**
     * テストの準備 (テストユーザーを作成し、ログイン状態にする)
     */
    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create([
            'id' => 1,
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'post_code' => '100-0001',
            'address' => '東京都千代田区千代田1-1',
            'building_name' => '皇居ビル',
            'profile_image' => 'profile_images/initial_avatar.jpg' // 初期プロフィール画像パス
        ]);

        // ユーザーをログイン状態にする
        $this->actingAs($this->user);
    }

    /**
     * ID: 14-1. プロフィール設定画面にアクセスした際、
     * 初回登録時のデータが初期データとして反映されていることを確認する
     * * @test
     */
    public function profile_edit_screen_initial_data_is_correct()
    {
        // 1. Act: マイページのプロフィール編集画面にアクセス
        // ルーティングは /mypage/profile を使用
        $response = $this->get(route('profile_edit'));

        // 2. Assert:
        // ステータスコード 200 (OK) が返されることを確認
        $response->assertStatus(Response::HTTP_OK);

        // プロフィール画像パスが正しく表示されていることを確認
        // asset() ヘルパは絶対URLを生成するため、HTMLソース内にそのパスが含まれているかチェックする
        // 実際のHTMLでは asset('storage/' . $user->profile_image) と記述されている。
        $expectedImagePath = asset('storage/' . $this->user->profile_image);
        $response->assertSee('src="' . $expectedImagePath . '"', false);

        // 各フォームフィールドの value 属性にユーザーデータが初期値として反映されていることを確認

        // ユーザー名 (name="name")
        $response->assertSee('name="name"', false); // name属性の確認
        $response->assertSee('value="' . $this->user->name . '"', false); // value属性の確認

        // 郵便番号 (name="post_code")
        $response->assertSee('name="post_code"', false);
        $response->assertSee('value="' . $this->user->post_code . '"', false);

        // 住所 (name="address")
        $response->assertSee('name="address"', false);
        $response->assertSee('value="' . $this->user->address . '"', false);

        // 建物名 (name="building_name")
        $response->assertSee('name="building_name"', false);
        $response->assertSee('value="' . $this->user->building_name . '"', false);

        // プロフィール設定画面のタイトルが表示されていることを確認
        $response->assertSeeText('プロフィール設定');

        // 更新ボタンが表示されていることを確認
        $response->assertSeeText('更新する');
    }
}
