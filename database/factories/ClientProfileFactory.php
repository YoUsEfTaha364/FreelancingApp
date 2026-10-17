<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientProfile>
 */
class ClientProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'company_name' => fake()->optional()->company(),
            'phone'        => '010' . fake()->numerify('########'),
            'country'      => fake()->country(),
            'city'         => fake()->city(),
        ];
    }
}
