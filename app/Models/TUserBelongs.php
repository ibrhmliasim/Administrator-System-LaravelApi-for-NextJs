<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ユーザー所属情報モデル
 */
class TUserBelongs extends Model {
    
    use HasFactory;
    protected $table = 't_user_belongs';

    public $timestamps = false;

    protected $primaryKey = ['user_id', 'seq_no'];
    public $incrementing = false;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'seq_no',
        'belongs_name',
    ];

    /**
     * ユーザー情報とのリレーション (多対1)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo {
        return $this->belongsTo(
            TUser::class,
            'user_id', // 外部キー
            'user_id'  // 親モデルのキー
        );
    }
}
