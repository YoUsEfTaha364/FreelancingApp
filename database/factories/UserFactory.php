<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\ClientProfile;
use App\Models\FreelancerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'role'              => UserRole::Client->value,
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Create a client user (default role).
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Client->value,
        ])->afterCreating(function (User $user) {
            ClientProfile::factory()->for($user)->create();
        });
    }

    /**
     * Create a freelancer user.
     */
    public function freelancer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Freelancer->value,
        ])->afterCreating(function (User $user) {
            FreelancerProfile::factory()->for($user)->create();
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
