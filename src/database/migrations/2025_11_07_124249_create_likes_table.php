<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            // プライマリキー
            $table->id();

            // ユーザーID (usersテーブルへの外部キー)
            // ユーザーが削除されたら、関連するいいねもカスケード削除されるように設定
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // アイテムID (itemsテーブルへの外部キー)
            // アイテムが削除されたら、関連するいいねもカスケード削除されるように設定
            $table->foreignId('item_id')
                ->constrained()
                ->onDelete('cascade');

            // 複合ユニークキーの設定:
            // 1つのユーザーが1つのアイテムに2回「いいね」できないようにする
            $table->unique(['user_id', 'item_id']);

            // 作成日時と更新日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
