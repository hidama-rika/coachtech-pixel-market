<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id(); // unsigned bigint の主キー（id）を作成

            // 外部キー (Foreign Keys)
            $table->foreignId('item_id')->constrained('items')->comment('コメント対象の商品ID');
            $table->foreignId('user_id')->constrained('users')->comment('コメント投稿者ID');

            // コメント本文
            $table->text('comment')->comment('コメント本文');

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
        Schema::dropIfExists('comments');
    }
}
