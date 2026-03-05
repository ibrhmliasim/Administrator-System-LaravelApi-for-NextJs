<?php
// app\Services\User\UserExportCsvService.php

namespace App\Services\User;

use App\Contracts\User\UserExportInterface;
use App\Services\User\UserSearchService;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportCsvService implements UserExportInterface
{

    protected $searchService;

    public function __construct(UserSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * 検索結果に基づいたCSVエクスポート処理
     *
     * @param array $filters 検索フィルター
     * @return StreamedResponse CSVファイルのストリームレスポンス
     */
    public function export(array $filters): StreamedResponse
    {
        set_time_limit(300);
        $startMemory = memory_get_usage(true) / 1024 / 1024;

        $query = $this->buildExportQuery($filters);

        // ファイル名（日本時間）
        $filename = 'ユーザー情報_' . Carbon::now('Asia/Tokyo')->format('YmdHis') . '.csv';

        // StreamedResponse を返却
        $response = new StreamedResponse(function () use ($query, $startMemory) {
            // 出力ストリームをオープン
            $handle = fopen('php://output', 'w');

            // CSV ヘッダー定義
            $headers = [
                'ユーザーID',
                'ログインID',
                'パスワード',
                '姓',
                '名',
                '郵便番号',
                '都道府県',
                '市区町村',
                '番地',
                '建造物名等',
                '生年月日',
                '性別',
                'メールアドレス',
                '電話番号',
                '登録日時',
                '更新日時',
            ];

            // SJIS-WIN に変換してヘッダーを出力
            fputcsv($handle, array_map(fn($h) => mb_convert_encoding($h, 'SJIS-WIN', 'UTF-8'), $headers));

            // chunk()でデータを分割取得（メモリ節約）
            $query->chunk(1000, function ($users) use ($handle) {
                foreach ($users as $user) {
                    $row = [
                        $user->user_id,
                        $user->login_id,
                        '',
                        // $user->password,
                        $user->user_surname,
                        $user->user_name,
                        $user->postal_cd,
                        $user->prefectures,
                        $user->city,
                        $user->address,
                        $user->other_address,
                        $user->birthday?->format('Y-m-d') ?? '',
                        config('genders')[$user->gender] ?? '',
                        $user->email,
                        $user->tel,
                        $user->regist_date?->format('Y-m-d H:i:s') ?? '',
                        $user->update_date?->format('Y-m-d H:i:s') ?? '',
                    ];

                    fputcsv($handle, array_map(
                        fn($value) => mb_convert_encoding($value ?? '', 'SJIS-WIN', 'UTF-8'),
                        $row
                    ));
                }
            });

            fclose($handle);

            // 3.
            $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;

            Log::info("CSVエクスポートレポート:", [
                '開始時メモリ' => round($startMemory, 2) . ' MB',
                'ピーク時メモリ'  => round($peakMemory, 2) . ' MB',
                'メモリ消費量'  => round($peakMemory - $startMemory, 2) . ' MB',
            ]);
        });

        // ヘッダー設定
        $response->headers->set('Content-Type', 'text/csv; charset=SJIS-WIN');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * エクスポート用のクエリを構築
     * @param array $filters 検索フィルター
     * @return \Illuminate\Database\Eloquent\Builder 構築されたクエリビルダ
     */
    private function buildExportQuery(array $filters)
    {
        $query = \App\Models\TUser::query()
            ->leftJoin('t_user_delete', 't_user.user_id', '=', 't_user_delete.user_id')
            ->whereNull('t_user_delete.user_id')
            ->select('t_user.*');

        // フィルター適用
        if (!empty($filters['login_id'])) {
            $query->where('login_id', 'like', '%' . $filters['login_id'] . '%');
        }
        if (!empty($filters['full_name'])) {
            $query->whereFullName($filters['full_name']);
        }
        if (!empty($filters['birthday'])) {
            $query->whereDate('birthday', $filters['birthday']);
        }
        if (isset($filters['gender']) && $filters['gender'] !== '') {
            $query->where('gender', $filters['gender']);
        }
        if (!empty($filters['belongs_name'])) {
            $query->whereHas('userBelongs', function ($q) use ($filters) {
                $q->where('belongs_name', 'like', '%' . $filters['belongs_name'] . '%');
            });
        }

        return $query;
    }
}
