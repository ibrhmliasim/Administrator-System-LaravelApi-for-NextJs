<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Http\Requests\User\SearchUserRequest.php
// リクエスト（国境警備隊）：データをチェックします。問題がある場合はブレードに送り返します。問題がない場合は通過させます。

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ユーザー検索リクエストバリデーション
 * 
 */
class SearchUserRequest extends FormRequest
{
    /**
     * リクエストの実行権限を確認
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // 認証済みユーザーなら誰でも検索可能とする
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
            'login_id'     => filled($this->login_id) ? trim($this->login_id) : null,
            'full_name'    => filled($this->full_name) ? trim($this->full_name) : null,
            'birthday'     => $normalizedBirthday ?? null,
            'belongs_name' => filled($this->belongs_name) ? trim($this->belongs_name) : null,
            'gender'       => filled($this->gender) ? trim($this->gender) : null,
            'limit'        => filled($this->limit) ? trim($this->limit) : null,
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
            'login_id'     => ['nullable', 'string', 'max:20'],
            'full_name'    => ['nullable', 'string', 'max:20'],
            'birthday'     => ['nullable', 'date'],
            'gender'       => ['nullable', 'in:0,1,2'],
            'belongs_name' => ['nullable', 'string', 'max:100'],
            'limit'        => ['nullable', 'integer', 'in:10,15,20,50,100'],
        ];
    }

    /**
     * 検索条件をバリデーション済みデータとして取得
     *
     * @return array 検索条件配列
     */
    public function searchData(): array
    {
        // $searchData 相当: バリデーション済みの安全なデータのみを返却
        return $this->validated();
    }
}
