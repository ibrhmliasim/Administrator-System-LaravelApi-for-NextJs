<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Models\TUserDelete.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ユーザー削除情報モデル (論理削除記録用)
 */
class TUserDelete extends Model {
    use HasFactory;

    protected $table = 't_user_delete';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'regist_date',
    ];

    /**
     * ユーザー情報とのリレーション (逆方向)
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo {
        return $this->belongsTo(
            TUser::class,
            'user_id',
            'user_id'
        );
    }
}
