<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserExportTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ユーザーCSVファイル出力のテスト
     *
     * @return void
     */
    public function test_user_csv_file_export(): void
    {
        TUser::factory()->count(5)->create();

        $response = $this->get(route('users.export'));

        $response->assertOk();

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringStartsWith('text/csv', $contentType);
        $this->assertStringContainsString('charset=SJIS-WIN', $contentType);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment; filename=', $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);

        $csvContent = $response->streamedContent();
        $this->assertNotEmpty($csvContent);

        $utf8Content = mb_convert_encoding($csvContent, 'UTF-8', 'SJIS-WIN');
        $lines = explode("\n", trim($utf8Content));

        $this->assertGreaterThan(1, count($lines));
    }

    /**
     * CSVファイルが検索と同じ条件で作成されていることをテスト
     *
     * @return void
     */
    public function test_csv_export_respects_search_conditions(): void
    {
        TUser::factory()->create(['login_id' => 'export_target']);
        TUser::factory()->create(['login_id' => 'export_other']);

        $response = $this->get(route('users.export', ['login_id' => 'target']));

        $response->assertOk();

        $utf8Content = mb_convert_encoding($response->streamedContent(), 'UTF-8', 'SJIS-WIN');

        $this->assertStringContainsString('export_target', $utf8Content);
        $this->assertStringNotContainsString('export_other', $utf8Content);
    }
}
