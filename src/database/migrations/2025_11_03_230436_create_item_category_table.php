<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_category', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id(); // unsigned bigint の主キー（id）を作成

            // 外部キー (Foreign Keys)
            // itemsテーブルを参照
            $table->foreignId('item_id')->constrained('items')->comment('商品ID');
            // categoriesテーブルを参照
            $table->foreignId('category_id')->constrained('categories')->comment('カテゴリID');

            // 複合ユニーク制約：同じ商品IDと同じカテゴリIDの組み合わせは一意
            $table->unique(['item_id', 'category_id']);

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
        Schema::dropIfExists('item_category');
    }
}
