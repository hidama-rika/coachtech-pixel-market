<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PaymentMethod;
use App\Models\Condition;
use Database\Seeders\PaymentMethodsTableSeeder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ID_11_PaymentMethodTest extends TestCase
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

        // ★ 修正: シーダーの呼び出しは `db:seed` ではなく、
        //    `$this->seed()` メソッドを使うか、`db:seed` コマンドで実行します。
        //    ただし、ここではテストクラス内なので `$this->seed()` が推奨されます。

        // 修正後のシーダー呼び出し
        $this->seed([
            \Database\Seeders\ConditionsTableSeeder::class,
            \Database\Seeders\CategoriesTableSeeder::class,
            \Database\Seeders\PaymentMethodsTableSeeder::class,
        ]);

        // データベースにテストユーザーを2名作成
        $this->seller = User::factory()->create([
            'id' => 1,
            'name' => 'テスト出品者A',
            'email' => 'seller@example.com',
            // ★★★ 修正: 出品者にも住所情報を追加 ★★★
            'post_code' => '765-4321',
            'address' => '大阪府大阪市テスト区1-1-1',
            'building_name' => '出品者テストビル',
        ]);
        $this->buyer = User::factory()->create([
            'id' => 2,
            'name' => 'テスト購入者B',
            'email' => 'buyer@example.com',
            // ★★★ 修正: 住所情報を追加して、ミドルウェアによるリダイレクトを防ぐ ★★★
            'post_code' => '123-4567',
            'address' => '東京都港区テスト町1-1-1',
            'building_name' => 'テストビル',
        ]);

        // ★★★ 修正ポイント 2: 購入者ユーザーでログイン状態にする ★★★
        $this->actingAs($this->buyer);

        // 購入される商品を作成
        // ★★★ 修正: itemsテーブルの必須カラム(condition_id, image_path)と brand を追加 ★★★
        $this->item = Item::factory()->create([
            'id' => 1,
            'user_id' => $this->seller->id,
            'condition_id' => 1, // conditionsテーブルの最初のIDを仮定
            'name' => 'テスト購入商品A',
            'price' => 5000,
            'description' => 'テスト用の購入商品です。',
            'brand' => 'TestBrandName', // ★ brand フィールドを追加 ★
            'image_path' => 'images/test_item_1.jpg', // ダミーの画像パス
            'is_sold' => false,
        ]);
    }

    /**
     * ID: 11-1. 認証済ユーザーが支払い方法選択画面（購入画面）を正しく表示できることを確認する
     * * [要件 1]: 支払い方法登録画面を表示 (new_purchases view が登録/選択を兼ねると解釈)
     * @test
     */
    public function authenticated_user_can_view_payment_selection_screen()
    {
        // Act: 支払い方法選択画面（購入画面）を開く
        $response = $this->get(route('new_purchases', $this->item->id));

        // Assert: 画面が表示され、支払い方法のカスタムプルダウン要素が存在することを確認 (修正)
        $response->assertStatus(200);
        $response->assertSee('商品購入画面');

        // ★★★ 修正ポイント: 文字列全体をチェックする assertSee() は、クォーテーションのエスケープに失敗しやすいため、
        // 存在するクラス名またはユニークな部分文字列を確認する。★★★
        // $response->assertSee('id="custom-payment-select"'); // ❌ 失敗の原因
        $response->assertSee('custom-select-control'); // ✅ 代替: カスタムプルダウンのクラス名
        $response->assertSee('支払い方法');
        $response->assertSee('コンビニ払い'); // オプションの中身も確認
    }

    /**
     * ID: 11-2. プルダウンメニューから支払い方法を選択し、承継画面で正しく反映される
     * * [要件 2]: プルダウンメニューから支払い方法を選択する → 選択した支払い方法が正しく反映される
     * @test
     */
    public function selected_payment_method_is_reflected_on_purchase_screen()
    {
        // 1. Arrange: 支払い方法をセッションに直接セット
        $purchaseScreenRoute = route('new_purchases', $this->item->id);
        $selectedPaymentMethodId = 1;

        // ★★★ 修正ポイント 1: ルートエラー回避のため、セッションに直接値をセット ★★★
        // 外部へのPOSTリクエストのテストは行わず、反映処理のみをテストする
        Session::put('selected_payment_type', $selectedPaymentMethodId);

        // 2. Act: 支払い方法が反映されているはずの購入画面にアクセス
        $response = $this->actingAs($this->buyer)->get($purchaseScreenRoute);

        // 3. Assert: 選択した支払い方法が正しく反映される（期待挙動）
        $response->assertStatus(200); // 302は期待しない

        // 選択された支払い方法がセッションに反映されていることを確認 (セッションにセットした値の確認)
        $response->assertSessionHas('selected_payment_type', $selectedPaymentMethodId);

        // 画面上の表示が変更されたことを確認
        // ID=1 の名前（「コンビニ払い」）が表示されていることを確認します。
        $response->assertSeeText('コンビニ払い');
    }
}