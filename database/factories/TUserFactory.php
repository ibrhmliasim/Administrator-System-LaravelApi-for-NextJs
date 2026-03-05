<?php
// database\factories\TUserFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TUser>
 */
class TUserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $login = 'user_' . fake()->unique()->numberBetween(1, 1000000);
        $allGenders = array_keys(config('genders'));
        $genderKeys = array_filter($allGenders, fn($key) => $key !== '');
        $prefectures = config('prefectures');
        
        return [
            'login_id' => $login,
            'password' => static::$password ??= Hash::make('password'),
            'user_surname' => fake()->lastName(),
            'user_name' => fake()->firstName(),
            'postal_cd' => fake()->postcode(),
            'prefectures' => fake()->randomElement($prefectures),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'other_address' => fake()->secondaryAddress(),
            'birthday' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement($genderKeys),
            'email' => $login . '@gmail.com',
            'tel' => fake()->regexify('0[789]0\d{4}\d{4}'),
            'regist_date' => now(),
            'update_date' => now(),
        ];
    }
}
