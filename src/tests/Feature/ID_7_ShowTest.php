<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;        // 商品モデルを仮定
use App\Models\Like;    // いいね（お気に入り）モデルを仮定
use App\Models\Comment;     // コメントモデルを仮定
use App\Models\ItemCategory;
use App\Models\Category;    // カテゴリモデルを仮定
use App\Models\Condition; // Conditionモデルのインポートが必要です
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\VerifyCsrfToken;

class ID_7_ShowTest extends TestCase
{
    // DBを使用し、テストごとにリフレッシュします
    use RefreshDatabase;

    protected $item;
    protected $itemCreator;
    protected $commentUser;
    protected $categories; // テストで使用するカテゴリデータを格納するプロパティ

    public function setUp(): void
    {
        parent::setUp();

        // 1. コメントするユーザーを作成
        $this->itemCreator = User::factory()->create(['name' => '出品者']);
        $this->commentUser = User::factory()->create(['name' => 'コメントユーザー']);

        // 2. Seederを実行して必須データ（Categories, Conditions）を作成
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\ConditionsTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\CategoriesTableSeeder']);

        // 3. ★★★ 修正: カテゴリデータを取得し、$this->categoriesに格納 ★★★
        $this->categories = Category::all();
        // 少なくとも2つ以上のカテゴリがあることを前提とします
        if ($this->categories->count() < 2) {
            $this->markTestSkipped('テスト実行には最低2つのカテゴリが必要です。CategoriesTableSeederを確認してください。');
        }

        // 4. 商品を作成
        $this->item = Item::factory()->create([
            'user_id' => $this->itemCreator->id,
            'name' => 'テスト商品名',
            'brand' => 'テストブランド',
            'price' => 5000,
            'description' => '詳細説明テキスト',
            // Condition::first()->id は ConditionSeederが実行済みなら存在する
            'condition_id' => Condition::first()->id,
        ]);

        // 5. 中間テーブルを使用してカテゴリを関連付け（多対多リレーションを仮定）
        // 【修正箇所】attach()で発生する重複エントリーエラーを回避するため、
        // ItemCategoryモデルを使ってレコードを個別に挿入する
        $categoryIdsToAttach = $this->categories->take(2)->pluck('id');

        // エラーの原因となっていた attach() の代わりに、個別にレコードを作成
        foreach ($categoryIdsToAttach as $categoryId) {
            ItemCategory::create([
                'item_id' => $this->item->id,
                'category_id' => $categoryId,
            ]);
        }

        // 6. いいねを作成（Likeモデルを使用）
        // ★★★ 修正: Like::factory()->create() を使用するなら、Like::class の use が必要 ★★★
        Like::factory()->create(['item_id' => $this->item->id]);

        // 7. コメントを作成
        $comment = Comment::factory()->create([
            'user_id' => $this->commentUser->id,
            'item_id' => $this->item->id,
            'comment' => '最初のテストコメントです。'
        ]);

        // 8. 購入済みデータを作成（今回は表示確認がメインなので省略）
        // Purchase::factory()->create(['item_id' => $this->item->id]);
    }

    // ----------------------------------------------------
    // テストケース
    // ----------------------------------------------------

    /**
     * ID: 7-1. 必要な情報がすべて表示されること
     * 期待される挙動: すべての情報が商品詳細ページに表示されている
     * @test
     */
    public function all_required_item_details_are_displayed()
    {
        // 1. 準備 (Arrange): 商品詳細ページにアクセス
        $response = $this->get('/item/' . $this->item->id);

        // 2. 検証 (Assert)
        $response->assertStatus(200);

        // ▼ 商品基本情報
        $response->assertSeeText($this->item->name); // 商品名
        $response->assertSeeText($this->item->description); // 商品説明
        $response->assertSeeText($this->itemCreator->name); // 出品したユーザー情報
        $response->assertSeeText('¥' . number_format($this->item->price)); // 価格
        $response->assertSeeText('テストブランド'); // ブランド名 (Brand)
        $response->assertSeeText($this->item->condition->name); // 状態 (Condition)

        // ▼ いいね数
        $response->assertSeeText($this->item->likes()->count()); // いいね数

        // ▼ コメント数と内容
        $response->assertSeeText($this->item->comments()->count());
        $response->assertSeeText($this->commentUser->name);
        $response->assertSeeText('最初のテストコメントです。');

        // ▼ カテゴリ
        // 複数のカテゴリ名がページに表示されていることを確認
        foreach ($this->item->categories as $category) {
            $response->assertSeeText($category->name);
        }
    }

    /**
     * ID: 7-2. 複数選択カテゴリが表示されているか
     * 期待される挙動: 複数選択されたカテゴリが商品詳細ページに表示されている
     * @test
     */
    public function multiple_selected_categories_are_displayed()
    {
        // 1. 準備 (Arrange): 複数のカテゴリが関連付けられている
        $categoryNames = $this->item->categories->pluck('name');

        // 2. 実行 (Act)
        $response = $this->get('/item/' . $this->item->id);

        // 3. 検証 (Assert)
        $response->assertStatus(200);

        // 関連付けられたすべてのカテゴリ名がページに含まれていることを確認
        foreach ($categoryNames as $name) {
            $response->assertSeeText($name);
        }

        // 【💡 修正提案 1: HTML要素数を直接検証する 💡】
        // 実際に表示されたカテゴリタグ（.category-tag）の数が、期待される数（2）であることを確認
        // $categoryNames コレクションの要素数（=2）を期待値として使用します。
        $response->assertSeeInOrder(
            $categoryNames->toArray(),
            'span.category-tag' // 修正: カテゴリ名がこのセレクタの内部にあることを期待
        );
    }
}
