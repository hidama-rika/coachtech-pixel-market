<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item; // 商品モデルを仮定
use App\Models\User; // Userモデルのインポートを追加
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\VerifyCsrfToken;

class ID_6_SearchTest extends TestCase
{
    // DBを使用し、テストごとにリフレッシュします
    use RefreshDatabase;

    // テストに使用する特定のアイテム
    protected $foundItem;
    protected $notFoundItem;

    public function setUp(): void
    {
        parent::setUp();

        // ★★★ 修正箇所: シーダークラスが見つからないエラーに対処するため、フルネームスペースを指定 ★★★
        // Laravelのデフォルトネームスペース 'Database\Seeders\' を使用します。

        // ConditionsTableSeederとCategoriesTableSeederを実行
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\ConditionsTableSeeder']);
        // ユーザーが提供したシーダー名は CategoriesTableSeeder でした。
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\CategoriesTableSeeder']);

        // ★★★ 修正箇所はここまで ★★★

        // 1. 検索で見つかる商品を作成
        // '商品名'で検索するケースを想定し、名前に特定のキーワードを含ませます
        $this->foundItem = Item::factory()->create([
            'name' => '限定品_Tシャツ_ブラック',
            'description' => 'レアなTシャツです',
        ]);

        // 2. 検索で見つからない商品を作成
        $this->notFoundItem = Item::factory()->create([
            'name' => '別のスニーカー',
            'description' => '関係のない商品です',
        ]);

        // 3. 他にもランダムな商品を数点作成しておくと、より現実的なテストになります
        Item::factory(5)->create();
    }

    // ----------------------------------------------------
    // テストケース
    // ----------------------------------------------------

    /**
     * ID: 6-1. 「商品名」で部分一致検索ができること
     * 期待される挙動: 部分一致する商品が表示される
     * @test
     */
    public function can_search_items_by_partial_name_match()
    {
        // 1. 準備 (Arrange): 検索キーワードを設定
        $keyword = 'Tシャツ';

        // 2. 実行 (Act)
        // 商品一覧ページ（/items）に対して検索クエリ（?keyword=Tシャツ）を付加してGETリクエストを送ることを想定
        $response = $this->get('/?keyword=' . urlencode($keyword));

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // 検索結果に、見つかる商品の名前が表示されていること（重要な検証）
        $response->assertSeeText($this->foundItem->name);

        // 検索結果に、見つからない商品の名前が表示されていないこと
        $response->assertDontSeeText($this->notFoundItem->name);
    }

    /**
     * ID: 6-2. 検索状態がマイリストでも保持されていること
     * 期待される挙動: 検索キーワードが保持されている
     * @test
     */
    public function search_status_is_maintained_across_pages()
    {
        // 1. 準備 (Arrange): 検索キーワードを設定し、検索を実行
        $keyword = 'ブラック';

        // 2. 実行 (Act)
        // Home/商品ページで検索
        $response = $this->get('/?keyword=' . urlencode($keyword));

        // マイリストページに遷移
        // 検索状態を保持するロジック（例：セッションやクエリパラメータの引き継ぎ）がControllerにあると仮定
        // ここでは、クエリパラメータが引き継がれないと仮定し、画面上の入力フォームの値を検証します。

        $user = $this->actingAs(User::factory()->create());
        $response_mylist = $user->get('/?tab=mylist'); // マイリストページに遷移

        // 3. 検証 (Assert)

        // 検索結果ページ（/items）で、検索フォームの入力値が保持されていることを確認
        // HTMLフォームの<input name="keyword" value="ブラック">を検証
        $response->assertSee('value="' . $keyword . '"', false); // HTML属性の検証

        // マイリストページ（/mylist）でも、検索キーワードが保持されていることを確認
        // 検索フォームが共通であることを想定
        $response_mylist->assertSee('value="' . $keyword . '"', false);
    }
}
