<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'condition_id',
        'name',
        'brand',
        'description',
        'price',
        'image_path',
        'is_sold',
    ];

    /**
     * このアイテムに対する「いいね」を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        // Itemは複数のLikeを持つ
        return $this->hasMany(Like::class);
    }

    /**
     * このアイテムを「いいね」しているユーザーを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedUsers()
    {
        // Itemはlikesテーブルを介して複数のUserに属する（多対多）
        return $this->belongsToMany(User::class, 'likes', 'item_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * 商品が販売済みかどうかを判定します。
     * itemsテーブルのis_soldカラム（boolean）を参照します。
     *
     * @return bool
     */
    public function is_sold()
    {
        // データベースのis_soldカラムの値（true/false）をそのまま返します
        return $this->is_sold;
    }

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    // --- 参照元 (多 対 1) ---

    /**
     * 商品の出品者 (多 対 1)
     * items.user_id が users.id を参照
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 商品の状態 (多 対 1)
     * items.condition_id が conditions.id を参照
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class, 'condition_id');
    }

    // --- 参照先 (多 対 多) ---

    /**
     * 商品が属するカテゴリ (多 対 多)
     * item_category中間テーブルを使用
     */
    public function categories(): BelongsToMany
    {
        // 第二引数: 中間テーブル名, 第三引数: 自身の外部キー, 第四引数: 相手の外部キー
        return $this->belongsToMany(Category::class, 'item_category', 'item_id', 'category_id')->withTimestamps();
    }

    // --- 参照先 (1 対 多) ---

    /**
     * 商品へのコメント一覧 (1 対 多)
     * items.id が comments.item_id を参照
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'item_id');
    }

    /**
     * 商品の購入履歴 (1 対 多)
     * items.id が purchases.item_id を参照
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'item_id');
    }
}
