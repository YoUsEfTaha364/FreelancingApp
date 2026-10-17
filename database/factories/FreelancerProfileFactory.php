<?php

namespace Database\Factories;

use App\Models\FreelancerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FreelancerProfile>
 */
class FreelancerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'gender'      => fake()->randomElement(['male', 'female']),
            'title'       => fake()->jobTitle(),
            'biography'   => fake()->paragraph(),
            'phone'       => '010' . fake()->numerify('########'),
            'country'     => fake()->country(),
            'city'        => fake()->city(),
            'hourly_rate' => fake()->randomFloat(2, 5, 150),
        ];
    }
}
