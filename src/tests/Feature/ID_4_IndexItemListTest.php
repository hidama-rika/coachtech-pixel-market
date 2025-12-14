<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // ★★★ RefreshDatabaseを追加 ★★★
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PaymentMethod;
use App\Models\Condition;
use App\Models\Category;
use Database\Factories\ItemFactory; // ★★★ ItemFactoryのインポート（必須） ★★★
use Database\Seeders\ConditionsTableSeeder; // シーダーのuse文
use Database\Seeders\CategoriesTableSeeder; // シーダーのuse文
use Database\Seeders\PaymentMethodsTableSeeder;
use Illuminate\Support\Facades\DB; // ★★★ 追加: 外部キー有効化のため ★★★
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
// use App\Http\Middleware\VerifyCsrfToken; // ログアウトテストも同様に、ミドルウェア無効化

class ID_4_IndexItemListTest extends TestCase
{
    // ★★★ 修正ポイント1: RefreshDatabase トレイトを使用し、MySQL環境でDBをリフレッシュさせます ★★★
    use RefreshDatabase;

    /**
     * テストで使用する商品データを事前に作成します。
     * @var User
     */
    protected $user;

    /**
     * テストで使用する商品モデルのインスタンス。
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $items;

    protected $sold_item_id;
    protected $my_item;

    public function setUp(): void
    {
        // ----------------------------------------------------------------------
        // 【MySQL環境向けに修正】DB接続設定の強制上書きを削除

        // 1. parent::setUp()を最初に呼び出し、アプリケーションとファサードを初期化します。
        parent::setUp();

        // 2. ★★★ 修正ポイント: 手動の migrate:fresh 呼び出しを削除 ★★★
        // RefreshDatabase トレイトが自動でマイグレーションを実行し、トランザクションを張ります。
        // シーダーのみを明示的に実行します。

        // 外部キー制約を満たすため、参照系シーダーを明示的に実行
        $this->seed(ConditionsTableSeeder::class);
        $this->seed(CategoriesTableSeeder::class);
        $this->seed(PaymentMethodsTableSeeder::class);

        // ★★★ デバッグコードの挿入 ★★★
        $conditionCount = Condition::count();
        if ($conditionCount === 0) {
            // データが0件の場合、シーダーの失敗が確定
            $this->fail('ConditionsTableSeederがデータを投入できていません。');
        }
        // ★★★ デバッグコードここまで ★★★

        // 外部キーIDを事前に取得する
        $existingConditionId = Condition::first()->id ?? 1;

        // Itemファクトリに渡すデフォルト属性
        $defaultItemAttributes = [
            'condition_id' => $existingConditionId,
        ];

        // 1. テストユーザーを作成
        $this->user = User::factory()->create();

        // 2. 複数の商品データを作成
        $this->items = Item::factory(5)->create($defaultItemAttributes);

        // 3. 自分が出品した商品を作成
        $this->my_item = Item::factory()->create(array_merge($defaultItemAttributes, ['user_id' => $this->user->id]));

        // 4. 購入済みの商品（Sold）を作成
        $sold_item = Item::factory()->create($defaultItemAttributes);

        // 5. 支払い方法のIDを動的に取得
        $paymentMethod = PaymentMethod::first();
        if (!$paymentMethod) {
            $this->fail('PaymentMethod Seeder failed to run.');
        }
        $paymentMethodId = $paymentMethod->id;

        // Purchaseテーブルにデータを挿入
        Purchase::create([
            'item_id' => $sold_item->id,
            'user_id' => $this->user->id,
            'payment_method_id' => $paymentMethodId,
            'shipping_post_code' => '123-4567',
            'shipping_address' => '東京都千代田区',
            'shipping_building' => 'テストビル',
            'price' => 1000,
            'transaction_status' => true,
        ]);

        $this->sold_item_id = $sold_item->id;
    }

    /**
     * テスト終了後にDB接続を切断し、インメモリDBを破棄します。
     * * ★★★ 修正ポイント2: MySQL環境ではDB切断は不要なので削除します ★★★
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    // ----------------------------------------------------
    // テストケース
    // ----------------------------------------------------

    /**
     * ID: 4-1. 全商品を取得できること
     * 期待される挙動: すべての商品が表示される
     * @test
     */
    public function all_products_are_displayed()
    {
        // 1. 準備 (Arrange): ログインせずに商品ページにアクセス

        // 2. 実行 (Act)
        $response = $this->get('/'); // 商品一覧ページを仮定

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // 作成した合計 7つの商品 (5 + 1 + 1) が表示されているかを確認
        // Item::count()と同じ数の商品がページに含まれていることを検証
        $response->assertSee(Item::count() . ' items found'); // ページ上にアイテム数が表示されていると仮定
        $this->assertCount(Item::count(), $response->viewData('items')); // Viewに渡されたデータ数を検証
    }

    /**
     * ID: 4-2. 購入済み商品は「Sold」と表示されること
     * 期待される挙動: 購入済み商品に「Sold」のラベルが表示される
     * @test
     */
    public function sold_item_displays_sold_label()
    {
        // 1. 準備 (Arrange): なし

        // 2. 実行 (Act)
        $response = $this->get('/');

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // 購入済み商品（$sold_item）の近くに「Sold」の文字列が含まれているかを確認
        // 例: <div data-item-id="{$this->sold_item_id}">...<span class="sold-label">Sold</span>...</div>
        $response->assertSeeText('SOLD OUT');
    }

    /**
     * ID: 4-3. 自分が出品した商品は表示されないこと
     * 期待される挙動: 自分が**出品した商品**が**一覧に表示されない**
     * @test
     */
    public function user_own_items_are_not_displayed_in_list()
    {
        // 1. 準備 (Arrange): ユーザーとしてログイン
        $response = $this->actingAs($this->user)->get('/');

        // 2. 検証 (Assert)
        $response->assertStatus(200);

        // 自分が作成した商品の名前がページに含まれていないことを確認 (重要な検証)
        $response->assertDontSeeText($this->my_item->name);

        // その他の商品（itemsコレクション）が表示されていることを確認
        foreach ($this->items as $item) {
            $response->assertSeeText($item->name);
        }
    }
}
