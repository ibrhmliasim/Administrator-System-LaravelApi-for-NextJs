<?php

namespace Database\Factories;

use App\Models\TUser;
use App\Models\TUserDelete;
use Illuminate\Database\Eloquent\Factories\Factory;

class TUserDeleteFactory extends Factory
{
    protected $model = TUserDelete::class;

    public function definition(): array
    {
        return [
            'user_id'     => TUser::factory(),
            'regist_date' => now(),
        ];
    }
}
