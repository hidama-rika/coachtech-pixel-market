<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // 中間テーブルのためPivotクラスをインポート

class ItemCategory extends Model
{
    use HasFactory;
}
