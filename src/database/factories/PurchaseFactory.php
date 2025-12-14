<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Purchase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // 購入には購入者（User）と購入された商品（Item）が必要です
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            // ★★★ 追加: NOT NULL制約に対応するため、payment_method_id を追加 ★★★
            // この値が外部キーとして有効であるためには、PaymentMethodsTableSeederの実行が必要です。
            'payment_method_id' => 1,

            // ★★★ 修正: NOT NULL制約を持つ配送先情報フィールドを追加 ★★★
            'shipping_post_code' => $this->faker->postcode(),
            'shipping_address' => $this->faker->address(),
            'shipping_building' => $this->faker->secondaryAddress(), // 建物名はNULL許容でもデータを入れる

            // ★★★ 追加: NOT NULL制約を持つ 'price' フィールドを追加 ★★★
            'price' => $this->faker->numberBetween(100, 10000), // 100円から10,000円の間のランダムな価格

            // ★★★ 追加: マイグレーションで追加された NOT NULL カラムに対応 ★★★
            'transaction_status' => false, // boolean型でデフォルト値 (0: 未完了)
        ];
    }
}
