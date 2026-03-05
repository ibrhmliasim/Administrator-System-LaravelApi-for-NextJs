<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Http\Requests\User\RegistUserRequest.php
// リクエスト: フォーム/URL から受信データを取得し、
// それをチェックし (null 可能、最大値など)、「すべてが正常であるため、作業できます。」と言います。

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ユーザー登録リクエストバリデーション
 * 
 */
class RegistUserRequest extends FormRequest
{
    /**
     * リクエストの実行権限を確認
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前のデータ準備
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $rawBirthday = filled($this->birthday) ? trim($this->birthday) : null;
        if ($rawBirthday) {
            $normalizedBirthday = preg_replace('/[^0-9]/', '-', $rawBirthday);
            $normalizedBirthday = preg_replace('/-+/', '-', $normalizedBirthday);
        }

        $this->merge([
            'login_id'       => filled($this->login_id) ? trim($this->login_id) : null,
            'user_surname'   => filled($this->user_surname) ? trim($this->user_surname) : null,
            'user_name'      => filled($this->user_name) ? trim($this->user_name) : null,
            'postal_cd'      => filled($this->postal_cd) ? str_replace(['-', '　'], '', $this->postal_cd) : null,
            'prefectures'    => filled($this->prefectures) ? trim($this->prefectures) : null,
            'city'           => filled($this->city) ? trim($this->city) : null,
            'address'        => filled($this->address) ? trim($this->address) : null,
            'other_address'  => filled($this->other_address) ? trim($this->other_address) : null,
            'birthday'       => $normalizedBirthday ?? null,
            'gender'         => filled($this->gender) ? (int)$this->gender : null,
            'email'          => filled($this->email) ? trim($this->email) : null,
            'tel'            => filled($this->tel) ? str_replace(['-', '　'], '', $this->tel) : null,
            't_user_belongs' => is_array($this->t_user_belongs)
                ? collect($this->t_user_belongs)->map(function ($item) {
                    if (isset($item['belongs_name'])) {
                        $item['belongs_name'] = trim($item['belongs_name']);
                    }
                    return $item;
                })->all()
                : [],
        ]);
    }

    /**
     * バリデーションルールを定義
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // --- ログインID ---
            'login_id' => [
                'required',
                'string',
                'max:20',
                'unique:t_user,login_id'
            ],

            // --- パスワード ---
            'password' => [
                'min:8',
                'required',
                'string',
            ],

            // --- 氏名 ---
            'user_surname' => ['required', 'string',],
            'user_name'    => ['required', 'string',],

            // --- 郵便番号 ---
            'postal_cd' => [
                'required',
                'string',
                'size:7',
                'regex:/^\d+$/'
            ],

            // --- 住所 ---
            'prefectures'   => ['required', 'string'],
            'city'          => ['required', 'string'],
            'address'       => ['required', 'string'],
            'other_address' => ['required', 'string'],

            // --- 生年月日 ---
            'birthday' => ['required', 'date'],

            // --- 性別 ---
            'gender' => ['nullable', 'in:0,1,2'],

            // --- メールアドレス ---
            'email' => ['required', 'email', 'max:255'],

            // --- 電話番号 ---
            'tel' => ['nullable', 'max:255'],

            // --- 所属情報 ---
            't_user_belongs' => ['nullable', 'array'],
            't_user_belongs.*.belongs_name' => ['required_with:t_user_belongs', 'string', 'max:100'],
        ];
    }

    /**
     * バリデーションエラーメッセージのカスタマイズ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login_id.required'  => '必須項目です',
            'login_id.unique'    => '登録済みのログインIDです',

            'password.min'       => 'パスワードは８桁以上入力してください',
            'password.required'  => '必須項目です',

            'user_surname.required' => '必須項目です',
            'user_name.required'    => '必須項目です',

            'postal_cd.required' => '必須項目です',
            'postal_cd.regex'    => '半角数字のみ入力してください',
            'postal_cd.size'     => '７桁入力してください',

            'prefectures.required' => '必須項目です',
            'city.required'          => '必須項目です',
            'address.required'       => '必須項目です',
            'other_address.required' => '必須項目です',

            'birthday.required'  => '誕生日は必須です',

            'email.required' => '必須項目です',
            'email.email' => 'メールアドレスの形式で入力してください',

            't_user_belongs.*.belongs_name.required_with' => '必須項目です',
        ];
    }

    /**
     * バリデーション済みデータを取得
     *
     * @return array 登録用データ配列
     */
    public function registerData(): array
    {
        return $this->validated();
    }
}
