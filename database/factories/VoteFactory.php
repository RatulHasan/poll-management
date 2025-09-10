<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $poll = Poll::factory()->create();
        $option = PollOption::factory()->create(['poll_id' => $poll->id]);

        return [
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
