<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 既存のデータをすべて削除（シーダーを複数回実行する場合に便利）
        DB::table('item_category')->truncate();

        // カテゴリーのIDを取得
        $categories = DB::table('categories')->pluck('id', 'name');

        // 商品のIDを取得
        $items = DB::table('items')->pluck('id', 'name');

        // 中間テーブルにデータを挿入
        DB::table('item_category')->insert([
            [
                'item_id' => $items['腕時計'],
                'category_id' => $categories['ファッション'],
            ],
            [
                'item_id' => $items['腕時計'],
                'category_id' => $categories['メンズ'],
            ],
            [
                'item_id' => $items['HDD'],
                'category_id' => $categories['家電'],
            ],
            [
                'item_id' => $items['玉ねぎ3束'],
                'category_id' => $categories['キッチン'],
            ],
            [
                'item_id' => $items['革靴'],
                'category_id' => $categories['ファッション'],
            ],
            [
                'item_id' => $items['革靴'],
                'category_id' => $categories['メンズ'],
            ],
            [
                'item_id' => $items['ノートPC'],
                'category_id' => $categories['家電'],
            ],
            [
                'item_id' => $items['マイク'],
                'category_id' => $categories['家電'],
            ],
            [
                'item_id' => $items['ショルダーバッグ'],
                'category_id' => $categories['ファッション'],
            ],
            [
                'item_id' => $items['ショルダーバッグ'],
                'category_id' => $categories['レディース'],
            ],
            [
                'item_id' => $items['タンブラー'],
                'category_id' => $categories['キッチン'],
            ],
            [
                'item_id' => $items['コーヒーミル'],
                'category_id' => $categories['キッチン'],
            ],
            [
                'item_id' => $items['メイクセット'],
                'category_id' => $categories['コスメ'],
            ],
        ]);
    }
}
