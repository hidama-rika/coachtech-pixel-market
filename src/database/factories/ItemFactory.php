<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Category; // Categoryモデルのインポートを追加
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Condition: 1から4までのIDが存在すると想定
        $condition_ids = [1, 2, 3, 4];

        return [
            // itemsテーブルに直接挿入するフィールドのみを定義します。
            // category_id は中間テーブル item_category に挿入されるため、ここでは含めません。
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->numberBetween(100, 100000),
            'brand' => $this->faker->optional()->company,
            'description' => $this->faker->paragraph,
            'image_path' => 'img/placeholder.jpg',

            // 外部キー制約を満たすID範囲からランダムに選択
            'condition_id' => $this->faker->randomElement($condition_ids),

            'is_sold' => false,
        ];
    }

    /**
     * Define the model's after creation callbacks.
     *
     * @return ItemFactory
     */
    public function configure()
    {
        return $this->afterCreating(function (Item $item) {
            // アイテム作成後、中間テーブル (item_category) にカテゴリを関連付けます。
            // 複数のカテゴリを関連付けたい場合はここでロジックを調整します。
            // ここでは、シーダーで存在するIDからランダムに1つのカテゴリIDを選んで関連付けます。
            $category_ids = range(1, 14);
            $item->categories()->attach($this->faker->randomElement($category_ids));
        });
    }
}