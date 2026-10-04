<?php
//tests\Feature\UserSearchTest.php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;
use App\Models\TUserBelongs;
use App\Models\TUserDelete;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserSearchTest extends TestCase
{
//    use RefreshDatabase;

    /**
     * APIエンドポイントの定数
     */
    private const ENDPOINT = '/api/users/search';

    /**
     * テスト前のセットアップ
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'admin_system_testing') {
            $this->fail('Тテストはテスト用データベースでは実行されませんでした: ' . DB::connection()->getDatabaseName());
        }

        Cache::flush();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('t_user_delete')->delete();
        DB::table('t_user_belongs')->delete();
        DB::table('t_user')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * ユーザー検索APIを呼び出すヘルパーメソッド
     *
     */
    private function search(array $query = []):TestResponse
    {
        return $this->getJson(route('users.search', $query));
    }

    /**
     * レスポンスの構造がフロントエンドの契約に一致するかテスト
     */
    public function test_response_structure_matches_frontend_contract(): void
    {
        $user = TUser::factory()->create();
        TUserBelongs::factory()->create(['user_id' => $user->user_id]);

        $this->search()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user_id', 'login_id', 'user_surname', 'user_name',
                        'birthday', 'gender', 'email', 'tel',
                        'user_belongs' => [['belongs_name']],
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'searchData',
            ])
            ->assertJsonMissingPath('data.0.password');
    }

    /**
     * limitパラメータが正しく動作する
     */
    public function test_limit_parameter_works(): void
    {
        TUser::factory()->count(125)->create();

        $response = $this->search(['limit' => 15]);

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 125)
            ->assertJsonPath('meta.per_page', 15);
    }

    /**
     * limitパラメータが不正な値の場合に拒否される
     */
    public function test_invalid_limit_value_is_rejected(): void
    {
        $response = $this->search(['limit' => 30]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /**
     * 2ページ目が残りのユーザーを返します
     */
    public function test_second_page_returns_remaining_users(): void
    {
        TUser::factory()->count(12)->create();

        $this->search(['limit' => 10, 'page' => 2])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 12);
    }

    /**
     * 削除されたユーザーは返さない
     */
    public function test_returns_paginated_users_without_deleted_ones(): void
    {
        TUser::factory()->create(['login_id' => 'active_user']);

        $deletedUser = TUser::factory()->create(['login_id' => 'deleted_user']);
        TUserDelete::factory()->create(['user_id' => $deletedUser->user_id]);

        $response = $this->search();

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login_id', 'active_user');
    }

    /**
     * 空の条件で検索すると全ユーザーが返される
     */
    public function test_empty_conditions_returns_all_users(): void
    {
        TUser::factory()->count(3)->create();

        $response = $this->search();

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /**
     * 複数の条件で検索すると正しくフィルタリングされる
     */
    public function test_combined_search(): void
    {
        $user1 = TUser::factory()->create([
            'login_id' => 'target_user',
            'gender' => 1,
            'birthday' => '1995-05-05',
        ]);
        TUserBelongs::factory()->create([
            'user_id' => $user1->user_id,
            'belongs_name' => 'IT Department',
        ]);

        TUser::factory()->create([
            'login_id' => 'other_user',
            'gender' => 2,
            'birthday' => '1995-05-05',
        ]);

        $response = $this->search([
            'login_id' => 'target',
            'gender' => 1,
            'belongs_name' => 'IT',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login_id', 'target_user');
    }

    /**
     * ログインIDで検索すると正しくフィルタリングされる
     */
    public function test_search_by_login_id_of_users(): void
    {
        TUser::factory()->create(['login_id' => 'asimib']);
        TUser::factory()->create(['login_id' => 'alpayib']);
        TUser::factory()->create(['login_id' => 'musamm']);

        $response = $this->search(['login_id' => 'asim']);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login_id', 'asimib');

        $response = $this->search(['login_id' => 'musa']);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login_id', 'musamm');

        $response = $this->search(['login_id' => 'moriyama']);
        $response->assertOk()
            ->assertJsonCount(0, 'data');

        $response = $this->search(['login_id' => 'nonexistence']);
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * 氏名で検索すると正しくフィルタリングされる
     */
    public function test_search_by_full_name_of_users(): void
    {
        TUser::factory()->create(['user_surname' => 'ibrahimli', 'user_name' => 'asim']);
        TUser::factory()->create(['user_surname' => 'ipkaev', 'user_name' => 'farid']);

        $response = $this->search(['full_name' => 'ibrahimli']);
        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $response = $this->search(['full_name' => 'alpay']);
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * 生年月日で検索すると正しくフィルタリングされる
     */
    public function test_search_by_birthday_of_users(): void
    {
        TUser::factory()->create(['birthday' => '1990-01-01']);
        TUser::factory()->create(['birthday' => '2000-12-31']);
        TUser::factory()->create(['birthday' => '1995-05-05']);

        $response = $this->search(['birthday' => '2000-12-31']);
        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $data = $response->json('data');
        $this->assertEquals(
            '2000-12-31',
            date('Y-m-d', strtotime($data[0]['birthday']))
        );

        $response = $this->search(['birthday' => '2026-01-01']);
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * 生年月日で検索する際、スラッシュで入力された場合は正しくフィルタリングされる
     */
    public function test_birthday_with_slashes_is_normalized_before_search(): void
    {
        // prepareForValidation()の テスト (Http\Requests\User\SearchUserRequest.php)
        TUser::factory()->create(['birthday' => '2000-12-31']);

        $response = $this->search(['birthday' => '2000/12/31']);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_by_belongs_name_of_users(): void
    {
        $user1 = TUser::factory()->create();
        TUserBelongs::factory()->create([
            'user_id' => $user1->user_id,
            'belongs_name' => 'システム部',
        ]);

        $user2 = TUser::factory()->create();
        TUserBelongs::factory()->create([
            'user_id' => $user2->user_id,
            'belongs_name' => '営業部',
        ]);

        $response = $this->search(['belongs_name' => 'システム']);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_belongs.0.belongs_name', 'システム部');

        $response = $this->search(['belongs_name' => '営業']);
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_belongs.0.belongs_name', '営業部');

        $response = $this->search(['belongs_name' => 'ファイナンス']);
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * 性別で検索すると正しくフィルタリングされる
     */
    public function test_search_by_gender_of_users(): void
    {
        TUser::factory()->create(['gender' => 0]);
        TUser::factory()->create(['gender' => 1]);
        TUser::factory()->create(['gender' => 2]);

        foreach ([0, 1, 2] as $gender) {
            $response = $this->search(['gender' => $gender]);
            $response->assertOk()
                ->assertJsonCount(1, 'data');
        }
    }

    /**
     * 性別で検索する際、不正な値が入力された場合はバリデーションエラーとなる
     */
    public function test_invalid_gender_value_is_rejected(): void
    {
        $response = $this->search(['gender' => 9]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }
}
