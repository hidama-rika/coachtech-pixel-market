<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ★ メール認証のため
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; // ← 追加
// ★★★ ここを「Laravel\Fortify」のパスに修正します ★★★
use Laravel\Fortify\TwoFactorAuthenticatable; // 💡 これを修正
// BelongsToManyリレーションを使用するため追加
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// ★★★ isLikingメソッドの引数タイプヒントとlikesリレーションのためにItemモデルをuseします ★★★
use App\Models\Item;

// ★ MustVerifyEmail インターフェースを実装。implements MustVerifyEmail記述追加
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    // ★★★ これで正しくFortifyのトレイトが読み込まれます ★★★
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'post_code',
        'address',
        'building_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 💡 TwoFactorAuthenticatableトレイトを追加した場合、以下の属性も隠す必要があります。
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // =======================================================
    // 💡 エラーの原因となったカスタムメソッドを定義します 💡
    // =======================================================

    /**
     * プロフィール情報が未登録であるかをチェックします。
     * 例として、name, post_code, address のいずれかが空の場合を未登録と判断します。
     */
    public function isProfileUnregistered(): bool
    {
        // name, post_code, addressのいずれかが空であれば true を返します
        return empty($this->name) || empty($this->post_code) || empty($this->address);
    }

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    /**
     * ユーザーが「いいね」した商品一覧 (多 対 多)
     * likes中間テーブルを使用
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes(): BelongsToMany
    {
        // 第二引数: 中間テーブル名, 第三引数: 自身の外部キー, 第四引数: 相手の外部キー
        return $this->belongsToMany(Item::class, 'likes', 'user_id', 'item_id')
                    ->withTimestamps();
    }

    /**
    * 指定された商品(Item)をこのユーザーが「いいね」しているかチェックします。
    *
    * @param \App\Models\Item $item
        * @return bool
    */
    public function isLiking(Item $item): bool
    {
        // likesリレーションのクエリビルダを使用し、指定された商品IDを持つレコードが存在するか確認
        return $this->likes()->where('item_id', $item->id)->exists();
    }

    /**
     * ユーザーが出品した商品 (1 対 多)
     * users.id が items.user_id を参照
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'user_id');
    }

    /**
     * ユーザーが行った購入取引 (1 対 多)
     * users.id が purchases.user_id を参照
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    /**
     * ユーザーが投稿したコメント (1 対 多)
     * users.id が comments.user_id を参照
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }
}
