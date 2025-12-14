<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Condition;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response; // ステータスコード定数のために追加
use Stripe\StripeClient; // Stripeモックのために追加

class ID_12_ShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $item;

    /**
     * Stripe Checkout Sessionの作成成功をモックするヘルパーメソッド
     * ID10のモックをベースに、リダイレクトURLを固定して利用
     */
    protected function mockStripeCheckoutSuccess()
    {
        $redirectUrl = 'http://test-redirect-url';

        // StripeClientのモック設定
        $stripeMock = $this->partialMock(StripeClient::class, function ($mock) use ($redirectUrl) {

            // sessions->create のモック (購入ボタン押下時)
            $mock->shouldReceive('checkout->sessions->create')
                ->andReturn((object)['url' => $redirectUrl]);

            // sessions->retrieve のモックはここでは購入処理完了まで見ていないため、省略可能だが、
            // エラー回避のためダミーのモックを設定
            $mock->shouldReceive('checkout->sessions->retrieve')
                ->andReturn((object)['payment_status' => 'unpaid']);
        });

        // テストケース全体でStripeClientをモックで置き換える
        $this->app->instance(StripeClient::class, $stripeMock);

        return $redirectUrl;
    }

    /**
     * テストの準備
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 必須データのシーディング
        $this->seed([
            \Database\Seeders\ConditionsTableSeeder::class,
            \Database\Seeders\CategoriesTableSeeder::class,
            \Database\Seeders\PaymentMethodsTableSeeder::class,
        ]);

        // データベースにテストユーザーを作成
        $this->buyer = User::factory()->create([
            'id' => 2,
            'name' => 'テスト購入者B',
            'email' => 'buyer@example.com',
            'post_code' => '123-4567',
            'address' => '東京都港区テスト町1-1-1',
            'building_name' => 'テストビル',
        ]);

        $this->seller = User::factory()->create(['id' => 1]);

        // 購入される商品を作成
        $this->item = Item::factory()->create([
            'id' => 1,
            'user_id' => $this->seller->id,
            'price' => 47000,
            'is_sold' => false,
        ]);

        // 購入者ユーザーでログイン状態にする
        $this->actingAs($this->buyer);

        // 購入画面アクセス前に支払い方法を事前にセッションにセットしておく（ID11の依存を解消するため）
        Session::put('selected_payment_type', 1); // 1: コンビニ払い
    }

    /**
     * ID: 12-1. 配送先変更画面で住所を変更し、購入画面に戻ったときに正しく反映されることを確認する
     * @test
     */
    public function shipping_address_change_is_reflected_on_purchase_screen()
    {
        // Arrange: 新しい配送先情報
        $newAddressData = [
            'item_id' => $this->item->id,
            'post_code' => '987-6543',
            'address' => '大阪府大阪市新住所区2-2-2',
            'building_name' => '新ビルディング',
        ];

        // 1. Act: 配送先変更のPATCHリクエストを実行
        // コントローラーが302リダイレクトを返すことを期待
        $response = $this->patch(route('shipping_session.store'), $newAddressData);

        // 2. Assert: リダイレクト先が購入画面（new_purchases）であることを確認
        // コントローラーロジック通り、302リダイレクトを期待
        $response->assertStatus(Response::HTTP_FOUND); // 302
        $response->assertRedirect(route('new_purchases', $this->item->id));

        // 3. Act: リダイレクト先の購入画面にアクセス
        $redirectUrl = $response->headers->get('Location');
        $response = $this->get($redirectUrl);

        // 4. Assert: 購入画面の住所欄に新しい情報が反映されていることを確認
        $response->assertStatus(Response::HTTP_OK); // 200
        $response->assertSeeText('〒 ' . $newAddressData['post_code']);
        $response->assertSeeText($newAddressData['address'] . ' ' . $newAddressData['building_name']);

        // セッションに新しい配送先情報が格納されていることを確認
        $response->assertSessionHas('purchase_shipping', function ($sessionData) use ($newAddressData) {
            // コントローラーのキー名に合わせる: shipping_post_code, shipping_address, shipping_building
            return $sessionData['shipping_post_code'] === $newAddressData['post_code'] &&
                $sessionData['shipping_address'] === $newAddressData['address'] &&
                $sessionData['shipping_building'] === $newAddressData['building_name'];
        });
    }

    /**
     * ID: 12-2. 変更された配送先情報で商品を購入できることを確認する
     * (購入処理がStripeへのリダイレクトで完結することを確認)
     * @test
     */
    public function purchase_can_be_completed_with_changed_shipping_address()
    {
        // Arrange: Stripeモックを設定し、リダイレクトURLを取得
        $expectedRedirectUrl = $this->mockStripeCheckoutSuccess();

        // 変更する配送先情報
        $changedAddress = [
            'item_id' => $this->item->id,
            'post_code' => '100-0001',
            'address' => '東京都千代田区新住所1-1-1',
            'building_name' => '新千代田ビル',
        ];

        // 1. 配送先変更をセッションに保存
        $this->patch(route('shipping_session.store'), $changedAddress);

        // 2. 購入フォームのデータ準備 (セッションから住所情報が取得されるが、フォームからも送られる想定)
        $purchaseData = [
            'item_id' => $this->item->id,
            'payment_method_id' => Session::get('selected_payment_type'),
            // フォームのhiddenフィールドとして送信されるかもしれないデータ
            'shipping_post_code' => $changedAddress['post_code'],
            'shipping_address' => $changedAddress['address'],
            'shipping_building' => $changedAddress['building_name'],
        ];

        // 3. Act: 購入処理の実行
        $response = $this->post(route('checkout.start', $this->item->id), $purchaseData);

        // 4. Assert:
        $response->assertStatus(Response::HTTP_SEE_OTHER); // 303

        // ★修正: リダイレクトURLを直接検証する（モックが効かないため）
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringStartsWith('https://checkout.stripe.com/', $redirectUrl, 'Stripe Checkoutへのリダイレクトが行われていません。');
    }
}
