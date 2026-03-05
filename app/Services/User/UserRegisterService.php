<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Services\User\UserRegisterService.php

namespace App\Services\User;

use App\Models\TUser;
use Illuminate\Support\Facades\DB;

class UserRegisterService {

    /**
     * 新規ユーザー作成処理
     *
     * @param array $data 登録データ配列
     * @return \App\Models\TUser 作成されたユーザーインスタンス
     */
    public function registerService (array $data) {
        return DB::transaction(function () use ($data) {
            $user = TUser::create($data);

            // 所属保存 
            if (!empty($data['t_user_belongs'])) {
                $belongsData = [];
                $index = 1;

                foreach ($data['t_user_belongs'] as $item) {
                    // 名前が空でない場合にのみ保存する
                    if (!empty($item['belongs_name'])) {
                        $belongsData[] = [
                            'belongs_name' => $item['belongs_name'],
                            'seq_no'       => $index++, 
                        ];
                    }
                }

                if (!empty($belongsData)) {
                    // 準備した配列を（正しいseq_noで）保存します。
                    $user->userBelongs()->createMany($belongsData);
                }
            }

            return $user;
        });
    }
}