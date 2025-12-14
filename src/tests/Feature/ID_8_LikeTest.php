<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\ItemCategory;
use App\Models\Category;
use App\Models\Condition;

class ID_8_LikeTest extends TestCase
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

        // 3. テストに必要なユーザー（出品者といいねをする人）を作成
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
    // テストケース
    // ----------------------------------------------------

    /**
     * ID: 8-1. いいねアイコンを押すことによって、いいねした商品として登録できること
     * 期待される挙動: いいねした商品として登録され、いいね合計数が増加する
     * @test
     */
    public function can_register_an_item_as_like()
    {
        // 1. 準備 (Arrange): ユーザーとしてログイン(setUpで実行済み)

        // 2. 実行 (Act): いいね登録のエンドポイントにPOSTリクエストを送る
        // 💡 修正: withoutMiddleware() を削除し、post + CSRFトークンに戻す
        $response = $this->actingAs($this->user)
            ->post(route('like.toggle', ['item' => $this->item->id]), [
                '_token' => csrf_token(),
            ]);

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // データベースにいいねレコードが作成されたことを確認 (重要な検証)
        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);

        // いいね数が1に増加したことを確認 (APIレスポンスまたはDBの総数を検証)
        $this->assertEquals(1, Like::where('item_id', $this->item->id)->count());
    }

    /**
     * ID: 8-2. 追加済みのアイコンは色が変化すること
     * 期待される挙動: いいねアイコンが押された状態（色が変化した状態）で表示される
     *
     * 【補足】これはJavaScriptによるフロントエンドの検証要素が強いですが、ここでは「いいね登録後、商品詳細ページに再アクセスした際に、いいね済みを示す要素が含まれていること」を検証します。
     * @test
     */
    public function like_icon_color_changes_after_liking()
    {
        // 1. 準備 (Arrange): 事前にいいねを登録しておく
        Like::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);

        // 2. 実行 (Act): ログインユーザーとして商品詳細ページにアクセス (いいね済み状態を確認するため)
        $response = $this->actingAs($this->user)->get('/item/' . $this->item->id); // GETリクエストに変更

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // 'liked' クラスが img タグの class 属性に含まれていることを検証 (正規表現でスペースを許容)
        $this->assertTrue(
            (bool) preg_match('/like-icon-img\s+liked|liked\s+like-icon-img/', $response->content()),
            "いいねアイコンの画像タグに 'liked' クラスが正しく付与されていません。"
        );
    }

    /**
     * ID: 8-3. 再度いいねアイコンを押すことによって、いいねを解除することができる
     * 期待される挙動: いいねが解除され、いいね合計数が減少される
     * @test
     */
    public function can_unlike_an_item()
    {
        // 1. 準備 (Arrange): 事前にいいねを登録し、いいね数が1の状態にする
        Like::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);

        $this->assertEquals(1, Like::where('item_id', $this->item->id)->count());

        // 2. 実行 (Act): いいね解除のエンドポイントにリクエストを送る
        // 💡 修正: withoutMiddleware() を削除し、post + CSRFトークンに戻す
        $response = $this->actingAs($this->user)
            ->post(route('like.toggle', ['item' => $this->item->id]), [
                '_token' => csrf_token(),
            ]);

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // ... データベース検証は変更なし ...
        $this->assertDatabaseMissing('likes', [
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
        ]);

        // 💡 Like モデルを参照
        $this->assertEquals(0, Like::where('item_id', $this->item->id)->count());
    }
}
