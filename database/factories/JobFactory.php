<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $budgetMin = fake()->randomFloat(2, 100, 2000);

        return [
            'user_id'          => User::factory()->client(),
            'category_id'      => Category::factory(),
            'title'            => fake()->sentence(6),
            'description'      => fake()->paragraphs(3, true),
            'budget_min'       => $budgetMin,
            'budget_max'       => $budgetMin + fake()->randomFloat(2, 100, 2000),
            'experience_level' => fake()->randomElement(['entry', 'intermediate', 'expert']),
            'deadline'         => fake()->optional()->dateTimeBetween('+1 week', '+6 months')?->format('Y-m-d'),
            'status'           => 'open',
        ];
    }

    /**
     * Create a closed job.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    /**
     * Create a job owned by a specific user (client).
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
