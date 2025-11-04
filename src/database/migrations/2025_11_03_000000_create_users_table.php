<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id(); // unsigned bigint の主キー（id）を作成

            // 基本情報
            $table->string('name', 255)->comment('ユーザー名');
            $table->string('email', 255)->unique()->comment('メールアドレス');
            $table->string('password', 255)->comment('ハッシュ化されたパスワード');
            $table->string('profile_image', 255)->nullable()->comment('プロフィール画像パス'); // 画像パスは任意とする
            // 住所情報
            $table->string('post_code', 8)->comment('郵便番号（ハイフン含む8桁）');
            $table->string('address', 255)->comment('住所');
            $table->string('building_name', 255)->nullable()->comment('建物名'); // 建物名は任意とする

            // 認証・タイムスタンプ
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken(); // ログイン情報保持トークン
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
        Schema::dropIfExists('users');
    }
}
