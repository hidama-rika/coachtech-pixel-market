<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // 中間テーブルのためPivotクラスをインポート
use Illuminate\Database\Eloquent\Model; // Eloquent Modelをインポート

class ItemCategory extends Model
{
    use HasFactory;

    // 中間テーブルの名前を明示的に指定します（Laravelの命名規則に従う場合は不要ですが、ここでは安全のため記載）
    protected $table = 'item_category';

    // 複合プライマリキーを持つ中間テーブルのため、incrementingをfalseに設定
    public $incrementing = false;

    // 複合キーを使うため、リレーションのデフォルトのキー名を使用しないように設定
    protected $primaryKey = ['item_id', 'category_id'];

    // 挿入可能なフィールドを定義（テスト内の create() で使用するため）
    protected $fillable = [
        'item_id',
        'category_id',
    ];
}
