<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Response; // ステータスコード定数のために追加

class ID_13_MypageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $listedItem;
    protected $purchasedItem;

    /**
     * テストの準備 (出品・購入データをセットアップ)
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 必須データのシーディング（カテゴリ、状態、支払い方法）
        $this->seed([
            \Database\Seeders\CategoriesTableSeeder::class,
            \Database\Seeders\ConditionsTableSeeder::class,
            \Database\Seeders\PaymentMethodsTableSeeder::class,
        ]);

        // 1. テストユーザー (出品者兼購入者) を作成
        $this->user = User::factory()->create([
            'id' => 2,
            'name' => 'テスト購入者B',
            'email' => 'buyer@example.com',
            'post_code' => '123-4567',
            'address' => '東京都港区テスト町1-1-1',
            'building_name' => 'テストビル',
            // ユーザー情報にプロフィール画像パスを設定（任意、表示テストのため）
            'profile_image' => 'profile_images/avatar.jpg'
        ]);

        // 2. このユーザーが出品した商品を作成
        $this->listedItem = Item::factory()->create([
            'id' => 1,
            'user_id' => $this->user->id,
            'condition_id' => 1, // conditionsテーブルの最初のIDを仮定
            'name' => '出品テスト商品A',
            'price' => 2000,
            'description' => 'テスト用の出品商品です。',
            'brand' => 'TestBrandName', // ★ brand フィールドを追加 ★
            'image_path' => 'images/test_item_1.jpg', // ダミーの画像パス
            'is_sold' => true,
        ]);

        // 3. 他のユーザーが出品し、このユーザーが購入した商品を作成
        $seller = User::factory()->create(['id' => 11]);
        $this->purchasedItem = Item::factory()->create([
            'id' => 2,
            'user_id' => $seller->id,
            'condition_id' => 1, // conditionsテーブルの最初のIDを仮定
            'name' => 'テスト購入商品B',
            'price' => 5000,
            'description' => 'テスト用の購入商品です。',
            'brand' => 'TestBrandName', // ★ brand フィールドを追加 ★
            'image_path' => 'images/test_item_2.jpg', // ダミーの画像パス
            'is_sold' => true,
        ]);

        // 4. 購入レコードを作成
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->purchasedItem->id,
            'payment_method_id' => 1, // ダミーの値
            'price' => $this->purchasedItem->price,
        ]);

        // ユーザーをログイン状態にする
        $this->actingAs($this->user);
    }

    /**
     * ID: 13-1. ログイン時にマイページへアクセスし、プロフィール情報と商品一覧が表示されることを確認する
     * @test
     */
    public function mypage_displays_profile_info_and_item_lists_for_authenticated_user()
    {
        // 1. Act: マイページにアクセス
        $response = $this->get(route('mypage'));

        // 2. Assert:
        $response->assertStatus(Response::HTTP_OK);

        // プロフィールセクションの表示確認
        $response->assertSeeText('テスト購入者B');
        $response->assertSeeText('プロフィールを編集');
        $response->assertSee('/mypage/profile');

        // タブメニューの表示確認
        $response->assertSeeText('出品した商品');
        $response->assertSeeText('購入した商品');

        // **出品商品一覧の確認**
        $response->assertSeeText($this->listedItem->name);

        // ★修正箇所1: $this->assertRegExp を $response->assertSeeInOrder に戻し、
        // active-contentが正しく含まれているか、スペースを挟んで確認する（警告回避）
        $response->assertSeeInOrder([
            '<div id="listed-content" class="item-grid-wrapper',
            'active-content',
        ], false);

        // **購入商品一覧の確認**
        $response->assertSeeText($this->purchasedItem->name);

        // 購入タブのURLとテキストの並びを確認 (テスト1失敗時の対応を維持)
        $response->assertSeeInOrder([
            'href="/mypage?page=buy"',
            '購入した商品',
        ], false);

        // デフォルトでは 'buy' コンテンツは hidden-content クラスを持つことを確認 (元のまま)
        $response->assertSeeInOrder([
            '<div id="purchased-content" class="item-grid-wrapper',
            'hidden-content',
        ], false);

        // ビュー変数として $listedItems, $purchasedItems, $currentPage が渡されていることを確認
        $response->assertViewHas('listedItems');
        $response->assertViewHas('purchasedItems');
        $response->assertViewHas('currentPage', 'sell');
    }

    /**
     * ID: 13-2. URLパラメータ 'page=buy' でアクセスした際、購入商品一覧がアクティブになることを確認する
     * @test
     */
    public function mypage_activates_purchased_items_when_page_is_buy()
    {
        // 1. Act: マイページに 'page=buy' パラメータ付きでアクセス
        $response = $this->get(route('mypage', ['page' => 'buy']));

        // 2. Assert:
        $response->assertStatus(Response::HTTP_OK);

        // ビュー変数 $currentPage が 'buy' であることを確認
        $response->assertViewHas('currentPage', 'buy');

        // '購入した商品' タブのリンクが active クラスを持っていることを確認
        // ★修正箇所2: $this->assertRegExp を $response->assertSeeInOrder に戻す
        // <a href="/mypage?page=buy" class="tab-link active" の形式を確認
        $response->assertSeeInOrder([
            'href="/mypage?page=buy"',
            'class="tab-link',
            'active',
        ], false);

        // 'purchased-content' セクションが active-content クラスを持っていることを確認
        // ★修正箇所3: $this->assertRegExp を $response->assertSeeInOrder に戻す
        $response->assertSeeInOrder([
            '<div id="purchased-content" class="item-grid-wrapper',
            'active-content',
        ], false);

        // 'listed-content' セクションが hidden-content クラスを持っていることを確認
        $response->assertSeeInOrder([
            '<div id="listed-content" class="item-grid-wrapper',
            'hidden-content'
        ], false);
    }
}
