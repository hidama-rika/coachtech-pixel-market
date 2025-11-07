<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Condition extends Model
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
     * この状態（コンディション）に該当する商品一覧 (1 対 多)
     * conditions.id が items.condition_id を参照
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'condition_id');
    }
}
