<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Models\TUser.php

namespace App\Models; 

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ユーザー情報モデル
 * 
 */
class TUser extends Authenticatable {
    
    use Notifiable;
    use HasFactory;

    protected $table = 't_user';
    protected $primaryKey = 'user_id';
    protected $dates = ['birthday'];
    public $timestamps = true;

    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'update_date';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login_id',
        'password',
        'user_surname',
        'user_name',
        'gender',
        'postal_cd',
        'prefectures',
        'city',
        'address',
        'other_address',
        'birthday',
        'email',
        'tel',
    ];

    /**
     * シリアライズ時に隠蔽する属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性のキャスト設定
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed', 
        'birthday' => 'date',
        'regist_date' => 'datetime',
        'update_date' => 'datetime',
    ];

    /**
     * フルネームのアクセサ
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function fullName():Attribute {
        return Attribute::make(
            get: fn() => "{$this->user_surname} {$this->user_name}",
        );
    }

    /**
     * 氏名検索のスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name 氏名
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFullName($query, $name) {
        $normalized = preg_replace('/\s+/u', '', $name);
        
        return $query->where(function ($q) use ($normalized, $name) {
            $q->whereRaw(
                "REPLACE(CONCAT(user_surname, user_name), ' ', '') LIKE ?",
                ["%{$normalized}%"]
            )
            ->orWhere('user_name', 'LIKE', "%{$name}%")
            ->orWhere('user_surname', 'LIKE', "%{$name}%");
        });
    }

    /**
     * ユーザー所属情報とのリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userBelongs(): HasMany {
        return $this->hasMany(TUserBelongs::class, 'user_id', 'user_id');
    }

    /**
     * 削除レコードとのリレーション（論理削除用）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function deleteRecord(): HasOne {
        return $this->hasOne(TUserDelete::class, 'user_id', 'user_id');
    }

    /**
     * 性別名のアクセサ (config/user.php から取得)
     *
     * @return string
     */
    public function getGenderTextAttribute(): string {
        return config('genders')[$this->gender] ?? '';
    }
}