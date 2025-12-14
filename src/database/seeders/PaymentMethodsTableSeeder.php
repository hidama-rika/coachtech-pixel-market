<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // purchases.payment_method_idのNOT NULL制約と外部キー制約を満たすため、
        // 少なくともID=1のレコードを挿入します。
        $paymentMethods = [
            // payment_status (1:有効, 0:無効) カラムに合わせて、デフォルトで有効(1)を設定
            ['id' => 1, 'name' => 'konbini', 'payment_status' => 1], // コンビニ決済
            ['id' => 2, 'name' => 'card', 'payment_status' => 1],    // カード決済
            // IDを明示的に指定することで、ファクトリ(payment_method_id => 1)との整合性を保証します。
        ];

        // insertOrIgnore() を使用することで、テーブルにデータが存在しても重複エラーを回避し、
        // テスト環境で安全に実行できます。
        DB::table('payment_methods')->insertOrIgnore($paymentMethods);
    }
}
