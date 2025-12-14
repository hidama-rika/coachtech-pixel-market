<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item; // Item モデルをインポート
use App\Models\Category; // Category モデルをインポート
use App\Models\ItemCategory;

class ItemCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // ItemCategory は中間テーブルなので、既存の Item と Category に紐づく ID をランダムに生成します。
        // ただし、テストケースでは setUp() で Item と Category を作成済みなので、
        // 外部キー制約を保つための最小限のランダムなIDを使用します。
        return [
            // 既存の Item レコードのIDを取得、存在しない場合は Item::factory() で作成する
            'item_id' => Item::factory(),

            // 既存の Category レコードのIDを取得
            // ここでは Category::factory() を呼び出すと、テストケース内のシードデータと競合する可能性があるので、
            // 簡潔にIDをランダムに指定（または、ItemCategoryFactoryを使う前にCategoryがDBに存在することを前提とする）
            // ItemCategory::factory() を単独で呼び出すことがないよう、デフォルトは 1 に設定しておきます。
            'category_id' => $this->faker->numberBetween(1, 10), // 1~10 の範囲でランダムなID
        ];
    }
}
