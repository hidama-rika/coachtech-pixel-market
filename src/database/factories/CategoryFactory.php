<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        // CategoriesTableSeeder で定義されている固定データに基づいてファクトリを定義します。
        $categories = [
            1 => 'ファッション',
            2 => '家電',
            3 => 'インテリア',
            4 => 'レディース',
            5 => 'メンズ',
            6 => 'コスメ',
            7 => '本',
            8 => 'ゲーム',
            9 => 'スポーツ',
            10 => 'キッチン',
            11 => 'ハンドメイド',
            12 => 'アクセサリー',
            13 => 'おもちゃ',
            14 => 'ベビー・キッズ',
        ];

        // ランダムなIDを生成し、そのIDに対応する名前を割り当てます。
        // これにより、ItemFactoryがCategory::factory()を呼び出した際に、
        // 必ずシーダーで定義されたID範囲（1〜14）内の値が使用されます。
        $id = $this->faker->randomElement(array_keys($categories));

        return [
            // 注意: テスト環境でシーダーが実行されない場合、
            // これらのIDが重複してエラーになる可能性があるため、テスト実行前に
            // Category::factory() が呼ばれる場合は注意が必要です。
            'id' => $id,
            'name' => $categories[$id],
        ];
    }
}
