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
            // 注意: 中間テーブルでは、一般的に $table->id() は不要。
            // 外部キー (Foreign Keys)

            // itemsテーブルを参照し、符号なしBIGINTとして定義
            $table->foreignId('item_id')
                ->constrained('items')
                ->onDelete('cascade')
                ->comment('商品ID');

            // categoriesテーブルを参照し、符号なしBIGINTとして定義
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade')
                ->comment('カテゴリID');

            // 複合主キーを設定して、同じ組み合わせの重複挿入を禁止する
            // item_idとcategory_idの組み合わせが一意であることを保証します。
            $table->primary(['item_id', 'category_id']);

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
