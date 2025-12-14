<?php

namespace Database\Factories;

use App\Models\Condition; // Conditionモデルを使用する場合
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Condition>
 */
class ConditionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Condition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 実際のConditionモデルのFactory実装に置き換えてください
        return [
            'name' => $this->faker->unique()->word(),
            // 必要に応じて他のフィールドを追加
        ];
    }
}

