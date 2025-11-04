<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    /**
     * このカテゴリに属する商品一覧 (多 対 多)
     * item_category中間テーブルを使用
     */
    public function items(): BelongsToMany
    {
        // 第二引数: 中間テーブル名, 第三引数: 自身の外部キー, 第四引数: 相手の外部キー
        return $this->belongsToMany(Item::class, 'item_category', 'category_id', 'item_id')->withTimestamps();
    }
}
