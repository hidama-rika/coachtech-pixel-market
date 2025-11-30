<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // ★これを追記★

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 【修正点 1】外部キーチェックを無効にする
        Schema::disableForeignKeyConstraints();

        // 既存のデータを全て削除してから投入します
        DB::table('payment_methods')->truncate();

        DB::table('payment_methods')->insert([
            // ID指定を外します。これが ID: 1 として投入されます
            [
                'name' => 'コンビニ払い',
                'payment_status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // ID指定を外します。これが ID: 2 として投入されます
            [
                'name' => 'カード支払い',
                'payment_status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // 【修正点 2】外部キーチェックを元に戻す
        Schema::enableForeignKeyConstraints();
    }
}
