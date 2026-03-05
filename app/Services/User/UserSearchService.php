<?php
// C:\Users\Asim\sekkeiShoLrvl\app\Services\User\UserSearchService.php
// サービス (ワーカー): データベース (モデル) と通信し、結果をコントローラーに返します。

namespace App\Services\User;

use App\Models\TUser;
use Illuminate\Database\Eloquent\Builder; // where, orderBy, join, etc.

use Illuminate\Support\Facades\Cache;

class UserSearchService
{

    /**
     * ユーザー一覧を検索・取得 (ペジネーション)
     *
     * @param array $conditions 検索条件
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator 検索結果のパジネーター
     */
    public function searchService(array $conditions)
    {
        // 共通のクエリビルダを取得
        $query = $this->buildSearchQuery($conditions);

        // 1ページあたりの件数
        $limit = min($conditions['limit'] ?? 10, 100);
        $page = $conditions['page'] ?? request()->get('page', 1);

        // $totalはフィルターのセット全体に対して 1 回計算されます。
        $cacheConditions = $conditions;
        unset($cacheConditions['page'], $cacheConditions['limit']);
        ksort($cacheConditions);

        $cacheKey = 'users_total_' . md5(json_encode($cacheConditions));
        $total = Cache::remember($cacheKey, 5, function () use ($query) {
            return (clone $query)->count();
        });

        // ソート順を指定してペジネーションを返す
        return $query
            ->orderBy('t_user.user_id')
            ->paginate($limit, ['*'], 'page', $page, $total)
            ->withQueryString(); // クエリパラメータを保持
    }

    /**
     * 検索クエリの構築 (検索とCSV出力で共通)
     *
     * @param array $conditions 検索条件
     * @return \Illuminate\Database\Eloquent\Builder 構築されたクエリビルダ
     */
    public function buildSearchQuery(array $conditions): Builder
    {
        $query = TUser::query()
            ->leftJoin('t_user_delete', 't_user.user_id', '=', 't_user_delete.user_id') // 削除済みユーザーを除外
            ->whereNull('t_user_delete.user_id')
            ->select(['t_user.user_id', 't_user.login_id', 't_user.user_surname', 't_user.user_name', 't_user.birthday', 't_user.gender', 't_user.email', 't_user.tel'])
            ->with('userBelongs');  // 所属情報を Eager Load

        // ログインIDで検索
        if (!empty($conditions['login_id'])) {
            $query->where('login_id', 'like', '%' . $conditions['login_id'] . '%');
        }

        // full_nameで検索(TUser.php - scopeWhereFullNameを使用)
        if (!empty($conditions['full_name'])) {
            $query->whereFullName($conditions['full_name']);
        }

        // 生年月日で検索
        if (!empty($conditions['birthday'])) {
            $query->whereDate('birthday', $conditions['birthday']);
        }

        // 性別で検索
        if (isset($conditions['gender']) && $conditions['gender'] !== '') {
            $query->where('gender', $conditions['gender']);
        }

        // 所属名で検索（リレーション先のカラムを検索）
        if (!empty($conditions['belongs_name'])) {
            $query->whereHas('userBelongs', function ($q) use ($conditions) {
                $q->where('belongs_name', 'like', '%' . $conditions['belongs_name'] . '%');
            });
        }

        return $query;
    }
}
