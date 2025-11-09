<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; // ← 追加
// ★★★ ここを「Laravel\Fortify」のパスに修正します ★★★
use Laravel\Fortify\TwoFactorAuthenticatable; // 💡 これを修正

class User extends Authenticatable
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
    // リレーションシップ定義
    // =======================================================

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
