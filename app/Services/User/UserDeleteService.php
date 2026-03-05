<?php

namespace App\Services\User;

use App\Models\TUser;
use App\Models\TUserDelete;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserDeleteService
{

    /**
     * ユーザーの論理削除処理
     *
     * @param int|string $user_id ユーザーID
     * @return array 処理結果ステータス
     */
    public function userDeleteService($user_id)
    {
        return DB::transaction(function () use ($user_id) {

            // 1. ユーザーが存在するかどうかを確認する
            $user = TUser::where('user_id', $user_id)->firstOrFail();

            if (!$user) {
                return ['status' => 'error', 'message' => 'ユーザーが見つかりません'];
            }

            // 2. すでに削除されているかどうかを確認します（t_user_deletesにエントリがあるかどうか）
            $alreadyDeleted = TUserDelete::where('user_id', $user_id)->exists();
            if ($alreadyDeleted) {
                return ['status' => 'error', 'message' => 'このユーザーは既に削除済みです'];
            }

            // 3. 削除レコードの作成
            $deleted = TUserDelete::create([
                'user_id' => $user_id,
                'regist_date' => Carbon::now(),
            ]);

            if ($deleted) {
                return ['status' => 'success'];
            }

            return ['status' => 'error', 'message' => '削除に失敗しました'];
        });
    }
}
