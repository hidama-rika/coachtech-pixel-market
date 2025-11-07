<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 挿入する商品データ（画像を参照）
        DB::table('items')->insert([
            [
                'user_id' => 1, // ダミーユーザーのID (test@example.com)
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolex',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_path' => 'img/item_img/Armani+Mens+Clock.jpg',
                'condition_id' => 1,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_path' => 'img/item_img/HDD+Hard+Disk.jpg',
                'condition_id' => 2,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_path' => 'img/item_img/iLoveIMG+d.jpg',
                'condition_id' => 3,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラシックなデザインの革靴',
                'image_path' => 'img/item_img/leather_shoes.jpg',
                'condition_id' => 4,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'image_path' => 'img/item_img/Living+Room+Laptop.jpg',
                'condition_id' => 1,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'image_path' => 'img/item_img/Music+Mic+4632231.jpg',
                'condition_id' => 2,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'image_path' => 'img/item_img/Purse+fashion+pocket.jpg',
                'condition_id' => 3,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'image_path' => 'img/item_img/Tumbler+souvenir.jpg',
                'condition_id' => 4,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_path' => 'img/item_img/Waitress+with+Coffee+Grinder.jpg',
                'condition_id' => 1,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'image_path' => 'img/item_img/makeup_set.jpg',
                'condition_id' => 2,
                'is_sold' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
