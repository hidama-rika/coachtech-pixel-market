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

            // [2] 決済IDを記録 (stripe_payment_intent_id)
            // nullを許可し、二重購入防止IDとして利用します。
            $table->string('stripe_payment_intent_id', 255)->nullable()->after('price')->comment('Stripe決済インテントID');

            // [3] 支払い方法のタイプを記録 (payment_method_type)
            // カードかコンビニかを識別するために利用します。
            $table->string('payment_method_type', 50)->nullable()->after('stripe_payment_intent_id')->comment('支払い方法のタイプ');
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
            $table->dropColumn(['price', 'stripe_payment_intent_id', 'payment_method_type']);
        });
    }
}
