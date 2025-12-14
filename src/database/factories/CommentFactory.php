<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // 外部キーはnullになるのを避けるため、既存のレコードまたはファクトリを使用
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'comment' => $this->faker->realText(100),
        ];
    }
}