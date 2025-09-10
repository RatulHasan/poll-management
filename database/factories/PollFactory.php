<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\Poll>
 */
class PollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'expires_at' => fake()->boolean(70) ? fake()->dateTimeBetween('+1 day', '+30 days') : null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'expires_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }
}
