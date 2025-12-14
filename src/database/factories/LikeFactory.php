<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\User; // 外部キーとしてUserモデルを使用
use App\Models\Item; // 外部キーとしてItemモデルを使用
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // いいね（お気に入り）は、どのユーザーがどの商品に付けたかを記録
            // 外部キー制約を満たすため、UserとItemのファクトリを呼び出します。
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
        ];
    }
}
