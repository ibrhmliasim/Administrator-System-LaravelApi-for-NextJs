<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\TUser;
use App\Contracts\User\UserExportInterface;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // インターフェースを介してコンテナからサービスを受信します。
        $service = app(UserExportInterface::class);
        
        $response = $service->export([]);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringStartsWith('text/csv', $contentType);
        $this->assertStringContainsString('charset=SJIS-WIN', $contentType);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment; filename=', $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);

        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertNotEmpty($csvContent);
        
        $utf8Content = mb_convert_encoding($csvContent, 'UTF-8', 'SJIS-WIN');
        $lines = explode("\n", trim($utf8Content));
        
        $this->assertGreaterThan(1, count($lines)); 
    }
}
