<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id(); // unsigned bigint の主キー（id）を作成

            // 外部キー (Foreign Keys)
            // 商品を出品したユーザー
            $table->foreignId('user_id')->constrained('users')->comment('出品ユーザーID');
            // 状態 (conditionsテーブルを参照)
            $table->foreignId('condition_id')->constrained('conditions')->comment('商品状態ID');

            // 商品基本情報
            $table->string('name', 255)->comment('商品名');
            $table->string('brand', 255)->nullable()->comment('ブランド名');
            $table->text('description')->comment('商品説明'); // 長文対応のためTEXT型を採用
            $table->integer('price')->comment('価格');
            $table->string('image_path', 255)->comment('商品画像パス');

            // フラグ
            $table->boolean('is_sold')->default(false)->comment('販売ステータス (0:未販売, 1:販売済)');

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
        Schema::dropIfExists('items');
    }
}
