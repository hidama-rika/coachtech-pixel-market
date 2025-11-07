<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'payment_status',
    ];

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    /**
     * この支払い方法で行われた購入取引一覧 (1 対 多)
     * payment_methods.id が purchases.payment_method_id を参照
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'payment_method_id');
    }
}
