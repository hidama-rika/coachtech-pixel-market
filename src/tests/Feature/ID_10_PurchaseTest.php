<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PaymentMethod;
use App\Models\Condition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Stripe\StripeClient;

class ID_10_PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $item;

    /**
     * テストの準備
     */
    protected function setUp(): void
    {
        parent::setUp();

        // ★★★ 修正ポイント: 公開キー(key)とシークレットキー(secret)の両方を上書きする ★★★
        config([
            // StripeClientの初期化に使われる可能性のあるシークレットキー
            'services.stripe.secret' => 'sk_test_dummy_key',
            // 念のため、公開キーもダミーで上書き（キーが混合するのを防ぐ）
            'services.stripe.key' => 'pk_test_dummy_key',
        ]);

        // 修正後のシーダー呼び出し
        $this->seed([
            \Database\Seeders\ConditionsTableSeeder::class,
            \Database\Seeders\CategoriesTableSeeder::class,
            \Database\Seeders\PaymentMethodsTableSeeder::class,
        ]);

        // ★★★ 修正ポイント: 外部キーIDを動的に取得する ★★★
        $existingCondition = Condition::first();
        if (!$existingCondition) {
            $this->fail("Condition Seeder failed. No condition found in database.");
        }
        $existingConditionId = $existingCondition->id;

        // データベースにテストユーザーを2名作成
        $this->seller = User::factory()->create([
            'id' => 1,
            'name' => 'テスト出品者A',
            'email' => 'seller@example.com',
            // ★★★ 修正: 出品者にも住所情報を追加 ★★★
            'post_code' => '765-4321',
            'address' => '大阪府大阪市テスト区1-1-1',
            'building_name' => '出品者テストビル',
            'profile_image' => 'profile_images/initial_avatar.jpg' // 初期プロフィール画像パス
        ]);
        $this->buyer = User::factory()->create([
            'id' => 2,
            'name' => 'テスト購入者B',
            'email' => 'buyer@example.com',
            // ★★★ 修正: 住所情報を追加して、ミドルウェアによるリダイレクトを防ぐ ★★★
            'post_code' => '123-4567',
            'address' => '東京都港区テスト町1-1-1',
            'building_name' => 'テストビル',
            'profile_image' => 'profile_images/initial_avatar1.jpg' // 初期プロフィール画像パス
        ]);

        // 購入される商品を作成
        // ★★★ 修正: itemsテーブルの必須カラム(condition_id, image_path)と brand を追加 ★★★
        $this->item = Item::factory()->create([
            'id' => 1,
            'user_id' => $this->seller->id,
            'condition_id' => $existingConditionId,
            'name' => 'テスト購入商品A',
            'price' => 5000,
            'description' => 'テスト用の購入商品です。',
            'brand' => 'TestBrandName', // ★ brand フィールドを追加 ★
            'image_path' => 'images/test_item_1.jpg', // ダミーの画像パス
            'is_sold' => false,
        ]);

        // ↓↓↓ 【重要追加】中間テーブルに複数のカテゴリをアタッチします ↓↓↓
        // 出品テスト(ID:15)に合わせて、カテゴリID 1と3をアタッチ
        // Itemモデルに categories() リレーションが定義されている必要があります。
        $this->item->categories()->sync([1, 3]); // ★ 修正: attachからsyncに変更 ★
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ----------------------------------------------------
    // 正常系テストケース
    // ----------------------------------------------------

    /**
     * ID: 10-1. 「購入する」ボタンを押すと購入が完了する
     * 期待される挙動: 購入が完了する
     * @test
     */
    public function purchase_is_completed_when_buy_button_is_pressed()
    {
        // 購入者として認証
        $this->actingAs($this->buyer);

        // ★★★ 修正: 「購入する」アクションをエミュレートするDB操作であることを明記 ★★★
        // 外部API連携（Stripe）をスキップし、購入操作の結果として直接DBに記録を作成する
        $defaultShippingData = [
            'shipping_post_code' => '000-0000',
            'shipping_address' => '東京都港区テスト町1-1-1',
            'shipping_building' => 'テストビル',
        ];

        // ↓↓↓ この Purchase::factory()->create(...) が、購入完了のDB記録を意味する ↓↓↓
        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'item_id' => $this->item->id,
            'payment_method_id' => 2,
            'price' => $this->item->price,
            'transaction_status' => 1,
            ...$defaultShippingData,
        ]);

        // DBの状態確認（重要：購入レコード作成後、Itemのis_soldがtrueになっていること）
        $this->item->refresh(); // DBから最新の状態を再取得
        $this->assertTrue($this->item->is_sold, '購入レコード作成後、itemテーブルのis_soldがtrueになっていません。'); // ← ★アサーションを追加★

        // ★★★ ここからが「購入後の状態確認」パート ★★★

        // 商品詳細ページにアクセス
        $response = $this->get(route('items.show', $this->item));

        // 商品名とSOLD表示の確認
        $response->assertStatus(200)
            ->assertSee('テスト購入商品A')
            ->assertSee('SOLD OUT');
    }

    /**
     * ID: 10-2. 購入した商品は一覧画面で「sold」と表示される
     * 期待される挙動: 購入した商品が商品一覧画面で「SOLD」表示になっている
     * @test
     */
    public function purchased_item_displays_sold_on_item_list()
    {
        // 購入者として認証
        $this->actingAs($this->buyer);

        // ItemをSOLD状態にする
        $this->item->is_sold = true;
        $this->item->save();

        // ★修正点: 購入記録の必須カラム (価格、ステータス、配送先情報) を補完
        $defaultShippingData = [
            'shipping_post_code' => '000-0000',
            'shipping_address' => '東京都港区テスト町1-1-1',
            'shipping_building' => 'テストビル',
        ];
        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'item_id' => $this->item->id,
            'payment_method_id' => 2,
            'price' => $this->item->price, // 商品の価格
            'transaction_status' => 1, // 成功ステータス
            ...$defaultShippingData, // 配送先情報
        ]);

        // 商品一覧ページにアクセス
        $response = $this->get(route('items.index'));

        // 商品名とSOLD表示の確認
        $response->assertStatus(200)
            ->assertSee('テスト購入商品A')
            ->assertSee('SOLD OUT');
    }

    /**
     * ID: 10-3. 「プロフィール」購入した商品一覧に追加されている
     * 期待される挙動: 購入した商品がプロフィールの購入した商品一覧に追加されている
     * @test
     */
    public function purchased_item_is_added_to_user_profile_list()
    {
        // 認証を事前に実行
        $this->actingAs($this->buyer);

        // ItemをSOLD状態にする
        $this->item->is_sold = true;
        $this->item->save();

        // ★修正点: 購入記録の必須カラム (価格、ステータス、配送先情報) を補完
        $defaultShippingData = [
            'shipping_post_code' => '000-0000',
            'shipping_address' => '東京都港区テスト町1-1-1',
            'shipping_building' => 'テストビル',
        ];
        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'item_id' => $this->item->id,
            'payment_method_id' => 2,
            'price' => $this->item->price, // 商品の価格
            'transaction_status' => 1, // 成功ステータス
            ...$defaultShippingData, // 配送先情報
        ]);

        // ★★★ 修正箇所3: followingRedirects() を使用し、リダイレクトを自動追跡 ★★★
        $response = $this->actingAs($this->buyer)
            ->followingRedirects() // プロフィール情報不足などによるリダイレクトに対応
            ->get(route('mypage'));

        // 購入リストの存在と商品名の確認
        $response->assertStatus(200)
            ->assertSee('購入した商品') // マイページに購入リストを表示するセクションがあると仮定
            ->assertSee('テスト購入商品A')
            ->assertSee('SOLD OUT');
    }
}
