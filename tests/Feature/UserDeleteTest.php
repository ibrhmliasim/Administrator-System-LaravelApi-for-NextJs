<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;
use App\Models\TUserDelete;
use App\Services\User\UserDeleteService;

class UserDeleteTest extends TestCase
{
    /**
     * ユーザー削除の正常系テスト
     */
    public function test_user_can_be_deleted(): void
    {

        $user = TUser::factory()->create();
        $service = new UserDeleteService();

        $result = $service->userDeleteService($user->user_id);
        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('t_user_delete', [
            'user_id' => $user->user_id
        ]);
    }

    /**
     * ユーザーIDが存在しない場合の削除テスト
     */
    public function test_cannot_delete_already_deleted_user(): void
    {
        $user = TUser::factory()->create();
        TUserDelete::create([
            'user_id' => $user->user_id,
            'regist_date' => now()
        ]);

        $service = new UserDeleteService();
        $result = $service->userDeleteService($user->user_id);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('このユーザーは既に削除済みです', $result['message']);
    }
}
