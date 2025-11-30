<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// --- 【修正】リレーション先のモデルを全てインポートします ---
use App\Models\Item;
use App\Models\User;
use App\Models\PaymentMethod;

class Purchase extends Model
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
        'payment_method_id',
        'shipping_post_code',
        'shipping_address',
        'shipping_building',
        'transaction_status',
        'price',
        'stripe_payment_intent_id',
        'payment_method_type',
    ];

    // =======================================================
    // リレーションシップ定義
    // =======================================================

    /**
     * 購入対象の商品 (多 対 1)
     * purchases.item_id が items.id を参照
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * 購入を行ったユーザー (多 対 1)
     * purchases.user_id が users.id を参照
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 利用された支払い方法 (多 対 1)
     * purchases.payment_method_id が payment_methods.id を参照
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
