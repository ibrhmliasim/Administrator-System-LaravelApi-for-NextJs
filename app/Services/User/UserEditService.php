<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Services\User\UserEditService.php

namespace App\Services\User;

use App\Models\TUser;
use App\Models\TUserBelongs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserEditService {

    /**
     * ユーザー情報の更新処理
     *
     * @param \App\Models\TUser $user 対象ユーザーモデル
     * @param array $data 更新データ配列
     * @return void
     */
    public function editService (TUser $user, array $data) {
        // dd($data['t_user_belongs']);
        DB::transaction(function () use ($user, $data) {
            
            // --- 1. t_user_belongs (削除) を処理 ---
            if (!empty($data['t_user_belongs'])) {
                foreach ($data['t_user_belongs'] as $row) {
                    // _delete=1 フラグが設定されていて、seq_no がある場合（レコードがすでにデータベースに存在する場合）
                    if (isset($row['_delete']) && (int)$row['_delete'] === 1 && !empty($row['seq_no'])) {
                        TUserBelongs::where('user_id', $user->user_id)
                                    ->where('seq_no', $row['seq_no'])
                                    ->delete();
                    }
                }

                // 削除対象としてマークされたものを配列から削除します（array_filterと同様）
                $data['t_user_belongs'] = array_filter(
                    $data['t_user_belongs'],
                    fn($row) => (int)($row['_delete'] ?? 0) !== 1
                );
            }

            // --- 2. パスワード処理 ---
            if (empty($data['password'])) {
                // 空の場合は、配列から削除するだけです。Laravel はこのフィールドにアクセスしません。
                unset($data['password']);
            } else {
                // 入力がある場合はハッシュ化して上書き
                $data['password'] = Hash::make($data['password']);
            }

            // --- 3. ユーザーの更新 ---
            $user->update($data);

            // --- 4. 所属データの更新・作成 ---
            if (!empty($data['t_user_belongs'])) {
                $maxSeqNo = TUserBelongs::where('user_id', $user->user_id)->max('seq_no') ?? 0;

                foreach ($data['t_user_belongs'] as $belongData) {

                    $currentSeqNo = $belongData['seq_no'] ?? null;

                    if (empty($currentSeqNo)) {
                        $maxSeqNo++;
                        $currentSeqNo = $maxSeqNo;
                    }

                    // 同期にupdateOrCreateを使用する
                    TUserBelongs::updateOrCreate(
                        [
                            'user_id' => $user->user_id,
                            'seq_no'  => $currentSeqNo,
                        ],
                        [
                            'belongs_name' => $belongData['belongs_name'],
                        ]
                    );
                }
            }
        });
    }
}