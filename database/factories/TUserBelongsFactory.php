<?php
// database\factories\TUserBelongsFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TUserBelongs>
 */
class TUserBelongsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $order = 1;
        $currentOrder = $order++;
        return [
            'seq_no'       => $currentOrder, 
            'belongs_name' => fake()->randomElement(['営業部', '開発部', '総務部', '人事部', '企画部', '経理部', '広報部', '法務部', 'IT部', 'マーケティング部', 'システム部']),
            'regist_date'  => now(),
        ];
    }
}
