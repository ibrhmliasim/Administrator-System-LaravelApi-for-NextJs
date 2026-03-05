<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserRegisterTest extends TestCase
{
    use DatabaseTransactions;

    /** --- セクション: 登録テスト --- **/

    /**
     * テスト: ユーザー登録成功とprepareForValidation()
     */
    public function test_user_regstration_and_prepare_for_validation(): void
    {
        // 最も汚いデータを送信して、prepareForValidationをテストします
        $dirtyData = [
            'login_id'              => ' nicoleBalaton009 ',  // スペース
            'password'              => 'jschbhbewcbe12345',
            'user_surname'          => ' Balisheva ',         // スペース
            'user_name'             => ' Nicole ',            // スペース
            'postal_cd'             => '234-2122',            // ハイフン（削除する必要があります）
            'prefectures'           => '東京都 ',
            'city'                  => '世田谷区',
            'address'               => ' 井節枝2-1-6 ',
            'other_address'         => ' チアノーゼ108号 ',
            'birthday'              => ' 2001.01/21 ',       // 例：ドットとスラッシュ（ハイフンにする必要があります）
            'gender'                => 1,
            'email'                 => ' nicole@example.com ',
            'tel'                   => '090-2321-3211',      // ハイフン（削除する必要があります）
        ];

        $response = $this->post(route('users.register'), $dirtyData);

        // バリデーションエラーがないことを確認する
        $response->assertSessionHasNoErrors();
        $response->assertStatus(201);

        // データベースに入力されたデータがクリーンであることを確認する
        $this->assertDatabaseHas('t_user', [
            'login_id'      => 'nicoleBalaton009',
            'user_surname'  => 'Balisheva',
            'user_name'     => 'Nicole',
            'postal_cd'     => '2342122',            // クリーン
            'prefectures'   => '東京都',
            'city'          => '世田谷区',
            'address'       => '井節枝2-1-6',
            'other_address' => 'チアノーゼ108号',
            'birthday'      => '2001-01-21',         // クリーン （ハイフン付き）
            'email'         => 'nicole@example.com',
            'tel'           => '09023213211',        // クリーン
        ]);
    }

    /** --- セクション: バリデーションテスト --- **/

    /**
     * テスト: login_id は一意である必要があります。
     */
    public function test_registration_fails_if_login_id_is_not_unique(): void
    {
        TUser::factory()->create(['login_id' => 'taken_id']);

        $response = $this->post(route('users.register'), [
            'login_id' => 'taken_id',
            'password' => 'password1234',
            'email'    => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['login_id']);
        $this->assertEquals(
            '登録済みのログインIDです',
            session('errors')->get('login_id')[0]
        );
    }

    /**
     * テスト: 郵便番号にハイフンが含まれていても、バリデーション前に削除され登録に成功すること。
     */
    public function test_registration_fails_if_postal_cd_has_wrong_length(): void
    {
        $response = $this->post(route('users.register'), ['postal_cd' => '123456789']);
        $response->assertSessionHasErrors(['postal_cd']);
    }

    /**
     * テスト: パスワードは 8 文字以上である必要があります。
     */
    public function test_registration_fails_if_password_is_too_short(): void
    {
        $response = $this->post(route('users.register'), [
            'password' => '12345',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * テスト: t_user_belongs が存在する場合、ネストされた属名が必要です。
     */
    public function test_registration_fails_if_belongs_name_is_missing_in_array(): void
    {
        $validData = [
            'login_id'      => 'valid_user',
            'password'      => 'password123',
            'user_surname'  => 'Test',
            'user_name'     => 'User',
            'postal_cd'     => '1234567',
            'prefectures'   => 'Tokyo',
            'city'          => 'City',
            'address'       => 'Address',
            'other_address' => '101',
            'birthday'      => '2000-01-01',
            'email'         => 'valid@example.com',
            't_user_belongs'  => [
                ['belongs_name' => '']
            ]
        ];

        $response = $this->post(route('users.register'), $validData);

        $response->assertSessionHasErrors(['t_user_belongs.0.belongs_name']);
    }
}
