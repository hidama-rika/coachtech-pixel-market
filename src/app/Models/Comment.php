<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'user_id',
        'comment',
    ];

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    /**
     * コメントの対象となっている商品 (多 対 1)
     * comments.item_id が items.id を参照
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * コメントを投稿したユーザー (多 対 1)
     * comments.user_id が users.id を参照
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
