<?php
//tests\Feature\UserSearchTest.php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;
use App\Models\TUserBelongs;
use App\Models\TUserDelete;

use App\Services\User\UserSearchService;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class UserSearchTest extends TestCase
{
    /**
     * テスト前のセットアップ
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('t_user_delete')->delete();
        DB::table('t_user_belongs')->delete();
        DB::table('t_user')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * テスト後のクリーンアップ
     * 
     * @return void
     */
    public function test_limit_parameter_works(): void
    {
        TUser::factory()->count(125)->create();

        $service = new UserSearchService();
        $result = $service->searchService(['limit' => 15]);

        $this->assertCount(15, $result->items());
        $this->assertEquals(125, $result->total());
    }

    public function test_returns_paginated_users_without_deleted_ones(): void
    {
        TUser::factory()->create(['login_id' => 'active_user']);

        $deletedUser = TUser::factory()->create(['login_id' => 'deleted_user']);
        TUserDelete::factory()->create(['user_id' => $deletedUser->user_id]);

        $service = new UserSearchService();

        $result = $service->searchService([]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(1, $result->items());
        $this->assertEquals('active_user', $result->items()[0]->login_id);
    }

    public function test_empty_conditions_returns_all_users(): void
    {
        TUser::factory()->count(3)->create();

        $service = new UserSearchService();
        $result = $service->searchService([]);

        $this->assertCount(3, $result->items());
    }

    public function test_combined_search(): void
    {
        $user1 = TUser::factory()->create([
            'login_id' => 'target_user',
            'gender' => 1,
            'birthday' => '1995-05-05'
        ]);
        TUserBelongs::factory()->create([
            'user_id' => $user1->user_id,
            'belongs_name' => 'IT Department'
        ]);

        $user2 = TUser::factory()->create([
            'login_id' => 'other_user',
            'gender' => 2,
            'birthday' => '1995-05-05'
        ]);

        $service = new UserSearchService();

        $conditions = [
            'login_id' => 'target',
            'gender' => 1,
            'belongs_name' => 'IT'
        ];

        $result = $service->searchService($conditions);

        $this->assertCount(1, $result->items());
        $this->assertEquals('target_user', $result->items()[0]->login_id);
    }

    public function test_search_by_login_id_of_users(): void
    {
        TUser::factory()->create(['login_id' => 'asimib']);
        TUser::factory()->create(['login_id' => 'alpayib']);
        TUser::factory()->create(['login_id' => 'musamm']);

        $service = new UserSearchService();

        $result = $service->searchService(['login_id' => 'asim']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(1, $result->items());
        $this->assertEquals('asimib', $result->items()[0]->login_id);

        $result = $service->searchService(['login_id' => 'musa']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('musamm', $result->items()[0]->login_id);

        $result = $service->searchService(['login_id' => 'moriyama']);
        $this->assertCount(0, $result->items());

        $result = $service->searchService(['login_id' => 'nonexistence']);
        $this->assertCount(0, $result->items());
    }

    public function test_search_by_full_name_of_users(): void
    {
        TUser::factory()->create(['user_surname' => 'ibrahimli', 'user_name' => 'asim']);
        TUser::factory()->create(['user_surname' => 'ipkaev', 'user_name' => 'farid']);

        $service = new UserSearchService();

        $result = $service->searchService(['full_name' => 'ibrahimli']);
        $this->assertCount(1, $result->items());

        $result = $service->searchService(['full_name' => 'alpay']);
        $this->assertCount(0, $result->items());
    }

    public function test_search_by_birthday_of_users(): void
    {
        TUser::factory()->create(['birthday' => '1990-01-01']);
        TUser::factory()->create(['birthday' => '2000-12-31']);
        TUser::factory()->create(['birthday' => '1995-05-05']);

        $service = new UserSearchService();

        $result = $service->searchService(['birthday' => '2000-12-31']);


        $this->assertCount(1, $result->items());
        $this->assertEquals('2000-12-31', date('Y-m-d', strtotime($result->items()[0]->birthday)));

        $result = $service->searchService(['birthday' => '2026-01-01']);
        $this->assertCount(0, $result->items());
    }

    public function test_search_by_belongs_name_of_users(): void
    {
        $user1 = TUser::factory()->create();
        TUserBelongs::factory()->create([
            'user_id' => $user1->user_id,
            'belongs_name' => 'システム部'
        ]);

        $user2 = TUser::factory()->create();
        TUserBelongs::factory()->create([
            'user_id' => $user2->user_id,
            'belongs_name' => '営業部'
        ]);

        $service = new UserSearchService();

        $result = $service->searchService(['belongs_name' => 'システム']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('システム部', $result->items()[0]->userBelongs->first()->belongs_name);

        $result = $service->searchService(['belongs_name' => '営業']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('営業部', $result->items()[0]->userBelongs->first()->belongs_name);

        $result = $service->searchService(['belongs_name' => 'ファイナンス']);
        $this->assertCount(0, $result->items());
    }

    public function test_search_by_gender_of_users(): void
    {
        TUser::factory()->create(['gender' => 0]);
        TUser::factory()->create(['gender' => 1]);
        TUser::factory()->create(['gender' => 2]);

        $service = new UserSearchService();

        $result = $service->searchService(['gender' => 0]);
        $this->assertCount(1, $result->items());
        $this->assertEquals(0, $result->items()[0]->gender);

        $result = $service->searchService(['gender' => 1]);
        $this->assertCount(1, $result->items());
        $this->assertEquals(1, $result->items()[0]->gender);

        $result = $service->searchService(['gender' => 2]);
        $this->assertCount(1, $result->items());
        $this->assertEquals(2, $result->items()[0]->gender);
    }
}
