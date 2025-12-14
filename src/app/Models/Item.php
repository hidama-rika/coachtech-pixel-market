<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// ★★★ 修正: Illuminateの名前空間のHasOneを使用することを明示
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    // =======================================================
    // アクセサ/ミューテタ定義
    // =======================================================

    /**
     * 商品が購入済みかどうかを示すアクセサ（$item->is_sold としてアクセス）
     * Purchaseリレーションの有無に基づいて動的に判定します。
     * * @return bool
     */
    public function getIsSoldAttribute(): bool // Laravel 8 互換の古い記法
    {
        // relationLoaded('purchase')：リレーションが事前にEager Loadされているかチェック
        if ($this->relationLoaded('purchase')) {
            // リレーションがロードされていれば、リレーションインスタンスの有無で判定
            return !is_null($this->purchase);
        }

        // ロードされていない場合はDBクエリを実行して判定
        // Purchaseリレーションの有無を確認
        return (bool) $this->purchase()->exists();
    }

    /**
     * ビューからの $item->is_sold() の呼び出しに対応するためのシムメソッド。
     * 既存のアクセサ ($this->is_sold) の値を利用します。
     *
     * @return bool
     */
    public function is_sold(): bool
    {
        // アクセサの値をプロパティとして取得
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

    // --- 参照先 (1 対 1) ---
    // isSoldアクセサが依存する、購入レコード (単数形) を取得するためのリレーション
    /**
     * 商品の購入履歴 (1 対 1) - 商品が一度だけ購入されることを前提
     * items.id が purchases.item_id を参照
     */
    public function purchase(): HasOne // ★HasOneに変更
    {
        // Itemは一つのPurchaseを持つ（HasOne）
        return $this->hasOne(Purchase::class, 'item_id');
    }
}
