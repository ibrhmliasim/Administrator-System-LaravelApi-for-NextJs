<?php
// database\seeders\DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\TUser;
use App\Models\TUserBelongs;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        TUser::factory(1000)
            ->has(TUserBelongs::factory()->count(0), 'userBelongs')
            ->create();

        Cache::flush();
    }
}