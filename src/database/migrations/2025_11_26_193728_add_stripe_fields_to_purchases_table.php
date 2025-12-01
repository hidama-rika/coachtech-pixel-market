<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeFieldsToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // [1] 商品価格を記録 (price)
            // 外部キーの次、既存のカラムの前に挿入します。
            $table->unsignedInteger('price')->after('payment_method_id')->comment('購入時の商品価格（円）');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // up()で追加したカラムを削除します
            $table->dropColumn('price');
        });
    }
}
