<?php

namespace Database\Factories;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vehicle_type' => fake()->randomElement(['motorcycle', 'car', 'bicycle']),
            'vehicle_plate' => strtoupper(fake()->bothLetters(2) . fake()->numerify('####')),
            'phone' => fake()->phoneNumber(),
            'latitude' => 11.5564 + fake()->randomFloat(6, -0.05, 0.05),
            'longitude' => 104.9282 + fake()->randomFloat(6, -0.05, 0.05),
        ];
    }
}
