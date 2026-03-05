<?php

//app\Providers\AppServiceProvider.php

namespace App\Providers;

use App\Contracts\User\UserExportInterface;
use App\Services\User\UserExportCsvService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserExportInterface::class, UserExportCsvService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
