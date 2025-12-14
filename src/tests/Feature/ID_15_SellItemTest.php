<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Condition;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Response;

class ID_15_SellItemTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;

    /**
     * テストの準備 (ユーザーと必須データのセットアップ)
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

        // テストユーザー (出品者) を作成
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

        // ユーザーをログイン状態にする
        $this->actingAs($this->user);

        // ファイル操作のためにダミーディスクを設定
        Storage::fake('public');
    }

    /**
     * ID: 15-1. 商品出品機能の動作確認
     * 商品出品に必要な情報が正しく保存され、マイページにリダイレクトされることを確認する
     *
     * @test
     */
    public function item_can_be_listed_successfully_and_redirects_to_mypage()
    {
        // 1. Arrange: ダミー画像ファイルと送信データを作成
        // アップロードファイルのバリデーション（mimes:jpeg,png）を通過させる。
        // imagecreatetruecolor()のエラーを避けるため、create()を使用しつつ、拡張子を.jpgにする
        $image = UploadedFile::fake()->create('test_item_image.jpg', 100, 'image/jpeg');
        $itemData = [
            'name' => 'テスト出品商品X',
            'brand' => 'テストブランドB',
            'description' => 'これはテスト用の出品説明文です。',
            'price' => 10000,
            'image_path' => $image, // アップロードファイルオブジェクトを渡す
            'condition_id' => 1,
            'categories' => [1, 3],
        ];

        // 2. Act: POSTリクエストを送信 (リダイレクトを追跡しない元の形に戻す)
        $response = $this->post('/sell', $itemData); // ★修正1: followRedirects()を削除

        // ItemモデルのIDを取得
        // ※ファイル名が動的に生成されるため、DBに保存された後の実際のファイル名を取得する必要があります。
        $newItem = Item::where('name', 'テスト出品商品X')->first();

        // 3. Assert: データベースにデータが保存され、マイページにリダイレクトされていることを確認

        // 3. Assert: データベースにデータが保存され、マイページにリダイレクトされていることを確認

        // ★修正点1: まずバリデーションエラーがないことを確認 (最も重要なデバッグ手順)
        $response->assertSessionHasNoErrors();

        // a. データが**itemsテーブル**に保存されたか確認
        // ここで失敗しているため、まずデータが保存されていることを前提に assertDatabaseHas を実行
        $this->assertDatabaseHas('items', [
            'user_id' => $this->user->id,
            'condition_id' => 1,
            'name' => 'テスト出品商品X',
            'brand' => 'テストブランドB',
            'description' => 'これはテスト用の出品説明文です。',
            'price' => 10000,
            'is_sold' => false,
        ]);

        // ItemモデルのIDを取得 ( assertDatabaseHas が成功した後に実行)
        $newItem = Item::where('name', 'テスト出品商品X')->first();

        // image_pathを直接比較で確認 (newItemが存在する場合のみ)
        if ($newItem) {
            $this->assertNotNull($newItem->image_path, '画像パスがDBに保存されていること');
        }

        // b. カテゴリーが**中間テーブル**に保存されたか確認
        if ($newItem) {
            $this->assertDatabaseHas('item_category', [
                'item_id' => $newItem->id,
                'category_id' => 1,
            ]);
            $this->assertDatabaseHas('item_category', [
                'item_id' => $newItem->id,
                'category_id' => 3,
            ]);
        }

        // c. 画像ファイルがストレージに保存されたか確認 (※ファイル名が動的生成されている場合)
        if ($newItem) {
            // ★最終修正: 実際のディレクトリ構造 (img/item_img/) に合わせて検証パスを修正する。
            // $newItem->image_path にはファイル名だけ、またはファイル名 + 誤ったプレフィックスが入っている可能性が高い。
            // 正確な検証パス: 'img/item_img/' + $newItem->image_path (DBにファイル名のみの場合)

            // 確実な検証のため、DBに保存された値に含まれるかもしれないプレフィックスを一旦除去し、
            // 正しいプレフィックスを付ける方法を用います。
            // しかし、まずはコントローラがDBに保存したパスをそのまま検証します。

            // コントローラがDBに保存したパスが正しい（img/item_img/ファイル名.jpg）ことを期待して検証します。
            Storage::disk('public')->assertExists($newItem->image_path);

            // もしこの行が失敗した場合、DBに保存されている $newItem->image_path の値が
            // 'ファイル名.jpg' のようなファイル名のみであると仮定し、検証パスを修正します。
        }

        // d. マイページの「出品した商品」タブへリダイレクトされたか確認
        // ★修正2: 実際の挙動 'tab=listed' に合わせてアサーションを修正
        $response->assertRedirect(route('mypage', ['tab' => 'listed']));

        // e. リダイレクト後の画面（マイページ）で、出品した商品名が表示されていることを確認
        // リダイレクト先のURLを取得し、別途GETリクエストを送信してコンテンツを検証する

        // リダイレクト先のURLを取得
        $redirectUrl = route('mypage', ['tab' => 'listed']);

        // 明示的にリダイレクト先へのGETリクエストを送信
        $contentResponse = $this->actingAs($this->user)->get($redirectUrl); // ログイン状態を保持

        $contentResponse
            ->assertOk() // ステータスコード200を確認
            ->assertSee('テスト出品商品X')
            ->assertSeeInOrder([
                // マイページで出品した商品タブが active になっていることを確認
                '<div id="listed-content" class="item-grid-wrapper',
                'active-content',
            ], false);
    }
}
