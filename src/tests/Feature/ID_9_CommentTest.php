<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use App\Models\ItemCategory;
use App\Models\Category;
use App\Models\Condition;

class ID_9_CommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // DBを使用し、テストごとにリフレッシュします
    use RefreshDatabase;

    protected $user;
    protected $item;

    public function setUp(): void
    {
        parent::setUp();

        // VerifyCsrfToken ミドルウェアを無効化します
        // $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // 1. 外部キー制約に必要なCondition（状態）データをファクトリで作成
        // Itemファクトリが参照するIDが存在するようにする
        Condition::factory()->create(['id' => 1, 'name' => '良好']);
        Condition::factory()->create(['id' => 2, 'name' => '目立った傷や汚れなし']);
        Condition::factory()->create(['id' => 3, 'name' => 'やや傷や汚れあり']);
        Condition::factory()->create(['id' => 4, 'name' => '状態が悪い']);
        // ID 1, 2 ができたので、Itemファクトリはこれらを参照できるようになる

        // --- テストデータ作成（Item作成部分） ---

        // 2. Category データを作成 (NEW!)
        // ItemFactoryが参照するカテゴリIDが存在するようにします。
        Category::factory()->create(['id' => 1, 'name' => 'ファッション']);
        Category::factory()->create(['id' => 2, 'name' => '家電']);
        Category::factory()->create(['id' => 3, 'name' => 'インテリア']);
        Category::factory()->create(['id' => 4, 'name' => 'レディース']);
        Category::factory()->create(['id' => 5, 'name' => 'メンズ']);
        Category::factory()->create(['id' => 6, 'name' => 'コスメ']);
        Category::factory()->create(['id' => 7, 'name' => '本']);
        Category::factory()->create(['id' => 8, 'name' => 'ゲーム']);
        Category::factory()->create(['id' => 9, 'name' => 'スポーツ']);
        Category::factory()->create(['id' => 10, 'name' => 'キッチン']);
        Category::factory()->create(['id' => 11, 'name' => 'ハンドメイド']);
        Category::factory()->create(['id' => 12, 'name' => 'アクセサリー']);
        Category::factory()->create(['id' => 13, 'name' => 'おもちゃ']);
        Category::factory()->create(['id' => 14, 'name' => 'ベビー・キッズ']);

        // 3. テストに必要なユーザー（出品者とコメントをする人）を作成
        // User作成、Item::factory()->create() ... の順で記述
        $this->user = User::factory()->create();
        // 💡 追加: ユーザーがプロフィール設定済みである状態を模擬する (例: address と post_code を設定)
        $this->user->update([
            'post_code' => '123-4567',
            'address' => '東京都港区テスト町1-1-1',
            // 他にも check.profile.set ミドルウェアがチェックしているフィールドがあればここに追加
        ]);
        $this->itemCreator = User::factory()->create();

        // 4. 商品を作成
        $this->item = Item::factory()->create([
            'user_id' => $this->itemCreator->id,
            'condition_id' => 1, // 👆 作成したConditionのIDを指定
        ]);

        // 5. テストユーザーとしてログイン（いいねは通常ログインが必要なため）
        $this->actingAs($this->user);
    }

    // ----------------------------------------------------
    // 正常系テストケース
    // ----------------------------------------------------

    /**
     * ID: 9-1. ログイン済みのユーザーはコメントを送信できる
     * 期待される挙動: コメントが保存され、コメント数が増加する
     * @test
     */
    public function logged_in_user_can_post_a_comment()
    {
        // 1. 準備 (Arrange): ユーザーとしてログイン(setUpで実行済み)後、コメントを投稿
        $commentContent = '新しいコメントを投稿します。';

        // 2. 実行 (Act): コメント送信のエンドポイント（例: /item/{item_id}/comments）にPOSTリクエスト
        // 💡 ルート名は /items/{item_id}/comments が正しいかもしれませんが、テストコードの /item/... を維持します
        // 💡 注意: Ajaxリクエストをシミュレートするため、`from()` を削除します
        $response = $this->post('/items/' . $this->item->id . '/comments', [
            'comment' => $commentContent,
        ]);

        // 3. 検証 (Assert)

        // 💡 修正: JSONレスポンス(200)が返ってくることを確認
        $response->assertStatus(200);
        $response->assertJson(['message' => 'コメントが投稿されました']); // オプション：JSONボディも検証

        // データベースにコメントレコードが作成されたことを確認 (重要な検証)
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'comment' => $commentContent,
        ]);

        // コメント数が1に増加したことを確認
        $this->assertEquals(1, Comment::where('item_id', $this->item->id)->count());
    }

    // ----------------------------------------------------
    // 異常系テストケース
    // ----------------------------------------------------

    /**
     * ID: 9-2. ログイン前のユーザーはコメントを送信できない
     * 期待される挙動: コメントが送信されない（ログイン画面にリダイレクトされる）
     * @test
     */
    public function guest_user_cannot_post_a_comment()
    {
        // setUpでログインしているので、明示的にログアウトを試みる
        $this->post('/logout'); // ログアウトエンドポイントがあれば実行

        // 2. 実行 (Act)
        $response = $this->post('/items/' . $this->item->id . '/comments', [
            'comment' => 'ゲストコメント',
        ]);

        // 3. 検証 (Assert)

        // ログインページ（/login）にリダイレクトされることを確認
        $response->assertRedirect('/login');

        // データベースにコメントが作成されていないことを確認
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * ID: 9-3. コメントが入力されていない場合、バリデーションメッセージが表示される
     * @test
     */
    public function comment_is_required()
    {
        // 1. 準備 (Arrange): ユーザーとしてログイン
        $this->actingAs($this->user);

        // 2. 実行 (Act): コメント内容を空にして送信
        // 💡 修正: $this->post を $this->json('POST', ...) に変更し、Ajaxリクエストをシミュレート
        $response = $this->json('POST', '/items/' . $this->item->id . '/comments', [
            'comment' => '', // 空
        ]);

        // 3. 検証 (Assert)

        // 💡 修正: Ajaxバリデーションエラー時のステータス(422)を確認
        $response->assertStatus(422);

        // 'comment'フィールドのエラーがあることを JSON で確認 (assertJsonValidationErrorsを使う)
        // ❌ assertSessionHasErrors は 302 リダイレクト時のみ有効
        // ✅ 修正: 422 JSONレスポンス内のエラーを検証
        $response->assertJsonValidationErrors('comment');
    }

    /**
     * ID: 9-4. コメントが255字以上の場合、バリデーションメッセージが表示される
     * @test
     */
    public function comment_content_cannot_be_over_255_characters()
    {
        // 1. 準備 (Arrange): ユーザーとしてログインし、256文字のコメントを作成
        $this->actingAs($this->user);
        $longContent = str_repeat('あ', 256); // 256文字

        // 2. 実行 (Act)
        // 💡 修正: $this->post を $this->json('POST', ...) に変更
        $response = $this->json('POST', '/items/' . $this->item->id . '/comments', [
            'comment' => $longContent,
        ]);

        // 3. 検証 (Assert)

        // 💡 修正: Ajaxバリデーションエラー時のステータス(422)を確認
        $response->assertStatus(422);

        // 'comment'フィールドのエラーがあることを JSON で確認
        // ❌ assertSessionHasErrors は 302 リダイレクト時のみ有効
        // ✅ 修正: 422 JSONレスポンス内のエラーを検証
        $response->assertJsonValidationErrors('comment');
    }
}
