<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id(); // unsigned bigint の主キー（id）を作成

            // 外部キー (Foreign Keys)
            $table->foreignId('item_id')->constrained('items')->comment('購入された商品ID');
            $table->foreignId('user_id')->constrained('users')->comment('購入者ユーザーID');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->comment('支払い方法ID');

            // 配送先情報
            // 郵便番号（ハイフン含む8桁）
            $table->string('shipping_post_code', 8)->comment('配送先郵便番号');
            $table->string('shipping_address', 255)->comment('配送先住所');
            $table->string('shipping_building', 255)->nullable()->comment('配送先建物名');

            // 取引ステータス
            $table->boolean('transaction_status')->comment('取引完了ステータス (1:完了, 0:未完了)');

            // タイムスタンプ
            $table->timestamps(); // created_at と updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
