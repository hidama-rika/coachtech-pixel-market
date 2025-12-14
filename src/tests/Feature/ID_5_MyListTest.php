<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item; // 商品モデルを仮定
use App\Models\Like; // いいね（お気に入り）モデルを仮定
use App\Models\Purchase; // 購入モデルを仮定
use App\Models\Condition; // 修正: モデルのインポートを追加
use App\Models\PaymentMethod; // 修正: モデルのインポートを追加
// シーダーのインポート
use Database\Seeders\ConditionsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\PaymentMethodsTableSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\VerifyCsrfToken;

class ID_5_MyListTest extends TestCase
{
    // DBを使用し、テストごとにリフレッシュします
    use RefreshDatabase;

    protected $user;
    protected $likeItem;
    protected $soldItem;
    protected $unlikedItem; // いいねしていない商品（比較用）を追加

    public function setUp(): void
    {
        // ----------------------------------------------------------------------
        // 【DBセットアップロジックの統合】
        // ----------------------------------------------------------------------
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class); // CSRF回避

        // 外部キー制約を満たすため、参照系シーダーを明示的に実行
        $this->seed(ConditionsTableSeeder::class);
        $this->seed(CategoriesTableSeeder::class);
        $this->seed(PaymentMethodsTableSeeder::class);

        // 外部キーIDを事前に取得する (存在保証)
        $existingConditionId = Condition::first()->id ?? 1;
        $paymentMethodId = PaymentMethod::first()->id ?? 1;

        // Itemファクトリに渡すデフォルト属性
        $defaultItemAttributes = [
            'condition_id' => $existingConditionId,
        ];
        // ----------------------------------------------------------------------

        // 1. テストユーザーを作成
        $this->user = User::factory()->create();

        // 2. いいねした商品を作成 (is_sold=falseがデフォルト)
        $this->likeItem = Item::factory()->create(array_merge($defaultItemAttributes, [
            'name' => 'お気に入り商品'
        ]));
        // ユーザーと商品の間に「いいね」レコードを作成
        Like::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->likeItem->id,
        ]);

        // 2-2. いいねしていない商品を作成 (比較用)
        $this->unlikedItem = Item::factory()->create(array_merge($defaultItemAttributes, [
            'name' => '興味のない商品'
        ]));

        // 3. 購入済み商品を作成
        $this->soldItem = Item::factory()->create(array_merge($defaultItemAttributes, [
            'name' => '購入済み商品',
            'is_sold' => true, // Purchaseレコードがあれば自動でSold扱いになるが、念のためファクトリで設定
        ]));

        // ★★★ 修正: 購入済み商品もマイリストに表示されるようにLikeレコードを作成 ★★★
        // これでsoldItemが/mypage(いいね一覧)に表示されるようになる
        Like::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->soldItem->id,
        ]);

        // この商品は、ログインユーザーが購入したと仮定し、Purchaseレコードを作成します。
        // PurchaseFactoryはPaymentMethodIdなど他の必須フィールドを処理しますが、ここではPaymentMethod::first()で取得したIDを明示的に使用
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->soldItem->id,
            'payment_method_id' => $paymentMethodId, // 必須フィールド
            // PurchaseFactoryで定義されている必須フィールドをすべて満たす必要がありますが、
            // ここではFactoryに依存して、IDだけを上書きします。
        ]);
    }

    // ----------------------------------------------------
    // テストケース
    // ----------------------------------------------------

    /**
     * ID: 5-1. いいねした商品だけが表示されること (いいね一覧タブを想定)
     * 期待される挙動: いいねをした商品が表示される
     * @test
     */
    public function only_favorited_items_are_displayed()
    {
        // 1. 準備 (Arrange): ユーザーとしてログインし、マイリストページ（いいね一覧タブ）にアクセス
        // /mylist/favorites のようなURLを仮定
        $response = $this->actingAs($this->user)->get('/?tab=mylist');

        // 2. 検証 (Assert)
        $response->assertStatus(200);

        // A. いいねした未売却商品が表示されていることを確認
        $response->assertSeeText($this->likeItem->name);

        // B. いいねした売却済み商品（購入済み商品）が表示されていることを確認
        $response->assertSeeText($this->soldItem->name); // ★★★ 売却済み商品も表示されることを明確に検証 ★★★

        // C. いいねしていない商品（興味のない商品）が表示されていないことを確認
        $response->assertDontSeeText($this->unlikedItem->name);
    }

    /**
     * ID: 5-2. 購入済み商品は「Sold」と表示されること
     * 期待される挙動: 購入済み商品に「Sold」のラベルが表示される
     * @test
     */
    public function sold_item_displays_sold_label_in_mylist()
    {
        // 1. 準備 (Arrange): ユーザーとしてログインし、マイリストページにアクセス
        $response = $this->actingAs($this->user)->get('/?tab=mylist');

        // 2. 検証 (Assert)
        $response->assertStatus(200);

        // 購入済み商品（$soldItem）の名前が表示されていることを確認
        $response->assertSeeText($this->soldItem->name);

        // 購入済み商品の近くに「Sold」の文字列が含まれているかを確認 (重要な検証)
        $response->assertSeeText('SOLD OUT');
    }

    /**
     * ID: 5-3. 未認証の場合、何も表示されないこと
     * 期待される挙動: マイリストページにアクセスできず、ログイン画面などにリダイレクトされる
     * @test
     */
    public function unauthenticated_user_cannot_view_mylist()
    {
        // 1. 準備 (Arrange): ログアウト状態でマイリストページにアクセス
        // $this->get() はゲストユーザーとして実行されます。
        $response = $this->get('/mypage');

        // 2. 検証 (Assert)

        // 未認証ユーザーはログインページ（/login）にリダイレクトされることを確認 (Laravelのミドルウェアの標準挙動)
        $response->assertRedirect('/login');

        // ページが生成されないため assertStatus(200) は使わず、リダイレクトを確認します。
    }
}
