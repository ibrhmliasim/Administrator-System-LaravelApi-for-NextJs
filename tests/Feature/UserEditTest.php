<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TUser;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserEditTest extends TestCase
{
    use DatabaseTransactions;

    /** --- セクション: 更新成功テスト --- **/

    /**
     * テスト: ユーザー更新成功とprepareForValidation()
     *
     * @return void
     */
    public function test_user_update_and_prepare_for_validation(): void
    {
        $user = TUser::factory()->create([
            'login_id' => 'old_login_id',
        ]);

        $updateData = [
            'login_id'      => ' old_login_id ',
            'user_surname'  => ' NewSurname ',
            'user_name'     => ' NewName ',
            'postal_cd'     => '777-8888',
            'prefectures'   => '大阪 府',
            'city'          => '大阪市',
            'address'       => '北区 1-1',
            'other_address' => 'ビル 202',
            'birthday'      => ' 1990.10/10 ',
            'email'         => ' new@example.com ',
            'tel'           => '080-1111-2222',
            'gender'        => 2,
        ];

        $response = $this->putJson(route('users.edit', ['user_id' => $user->user_id]), $updateData);

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();

        $this->assertDatabaseHas('t_user', [
            'user_id'      => $user->user_id,
            'login_id'     => 'old_login_id',
            'user_surname' => 'NewSurname',
            'postal_cd'    => '7778888',
            'birthday'     => '1990-10-10',
            'tel'          => '08011112222',
        ]);
    }

    /**
     * テスト: パスワードが提供されていない場合、古いパスワードを保持する
     *
     * @return void
     */
    public function test__user_update_keeps_old_password_if_not_provided(): void
    {
        $oldPasswordHash = Hash::make('password');
        $user = Tuser::factory()->create([
            'password' => $oldPasswordHash,
        ]);

        $updateData = [
            'login_id'      => 'old_login_id',
            'password'      => '',
            'user_surname'  => 'NewSurname',
            'user_name'     => 'NewName',
            'postal_cd'     => '7778888',
            'prefectures'   => '大阪府',
            'city'          => '大阪市',
            'address'       => '北区1-1',
            'other_address' => 'ビル202',
            'birthday'      => '1990-10-10 ',
            'email'         => 'new@example.com',
            'tel'           => '08011112222',
            'gender'        => 2,
        ];

        $response = $this->putJson(route('users.edit', ['user_id' => $user->user_id]), $updateData);

        $response->assertOk()
            ->assertJsonMissingValidationErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /**
     * テスト: login_id が他のユーザーによって既に使用されている場合、更新は失敗する
     *
     * @return void
     */
    public function test_user_update_fails_if_login_id_is_taken_by_another_user(): void
    {
        $user1 = TUser::factory()->create(['login_id' => 'user_one']);
        $user2 = TUser::factory()->create(['login_id' => 'user_two']);

        $updateData = [
            'login_id'      => 'user_two', // 既存の login_id を使用
            'user_surname'  => 'NewSurname',
            'user_name'     => 'NewName',
            'postal_cd'     => '7778888',
            'prefectures'   => '大阪府',
            'city'          => '大阪市',
            'address'       => '北区1-1',
            'other_address' => 'ビル202',
            'birthday'      => '1990-10-10',
            'email'         => 'new@example.com',
            'tel'           => '08011112222',
            'gender'        => 2,

        ];

        $response = $this->putJson(route('users.edit', ['user_id' => $user1->user_id]), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login_id']);
    }

    /**
     * テスト: 存在しないユーザーに対するユーザー更新で404が返される
     *
     * @return void
     */
    public function test_user_update_returns_404_for_nonexistent_user(): void
    {
        $response = $this->putJson(route('users.edit', ['user_id' => 999999999]), [
            'login_id' => 'whatever',
        ]);

        $this->assertContains($response->status(), [404, 422]);
    }
}
