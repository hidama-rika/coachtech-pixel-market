<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_methods')->insert([
            ['id' => 1, 'name' => 'コンビニ払い', 'payment_status' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'カード支払い', 'payment_status' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
