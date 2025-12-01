<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Hashファサードのインポートを忘れずに！

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'test',
                'email' => 'test@example.com',
                'password' => Hash::make('test1106'),
                'email_verified_at' => now(), // ★検証済みとして追加★
                'post_code' => '100-0001', // ダミーの郵便番号を追加
                'address' => '東京都千代田区', // ダミーの住所を追加
                'building_name' => 'テストビル101', // (もしあれば) ダミーの建物名を追加
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'rika',
                'email' => 'rika@example.com',
                'password' => Hash::make('rika0930'),
                'email_verified_at' => now(), // ★検証済みとして追加★
                'post_code' => '100-0002', // ダミーの郵便番号を追加
                'address' => '神奈川県横浜市', // ダミーの住所を追加
                'building_name' => 'もみじ103', // (もしあれば) ダミーの建物名を追加
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
