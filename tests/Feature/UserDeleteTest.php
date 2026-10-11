<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;
use App\Models\TUserDelete;
use App\Services\User\UserDeleteService;

use Illuminate\Foundation\Testing\DatabaseTransactions;


class UserDeleteTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ユーザー削除の正常系テスト
     *
     * @return void
     */
    public function test_user_can_be_deleted(): void
    {
        $user = TUser::factory()->create();

        $response = $this->deleteJson(route('users.delete', ['user_id' => $user->user_id]));

        $response->assertOk();
        $this->assertDatabaseHas('t_user_delete', [
            'user_id' => $user->user_id,
        ]);
    }

    /**
     * ユーザーIDが存在しない場合の削除テスト
     *
     * @return void
     */
    public function test_cannot_delete_already_deleted_user(): void
    {
        $user = TUser::factory()->create();
        TUserDelete::create([
            'user_id'     => $user->user_id,
            'regist_date' => now(),
        ]);

        $response = $this->deleteJson(route('users.delete', ['user_id' => $user->user_id]));

        $response->assertStatus(409)
            ->assertJsonPath('message', 'このユーザーは既に削除済みです');
    }
}
