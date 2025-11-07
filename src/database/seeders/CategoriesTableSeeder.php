<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'ファッション', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '家電', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'インテリア', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'レディース', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'メンズ', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'コスメ', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => '本', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'ゲーム', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'スポーツ', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'キッチン', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'ハンドメイド', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'アクセサリー', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'name' => 'おもちゃ', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'name' => 'ベビー・キッズ', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
