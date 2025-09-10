<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create some regular users
        $users = User::factory(5)->create();

        // Create 10 polls with options and votes
        Poll::factory(10)
            ->create(['user_id' => $admin->id])
            ->each(function ($poll) use ($users) {
                // Create 2-5 options for each poll
                PollOption::factory(fake()->numberBetween(2, 5))
                    ->sequence(fn ($sequence) => ['order' => $sequence->index])
                    ->create(['poll_id' => $poll->id]);

                // Create some votes for each poll
                $users->each(function ($user) use ($poll) {
                    if (fake()->boolean(70)) { // 70% chance user voted
                        Vote::factory()->create([
                            'poll_id' => $poll->id,
                            'poll_option_id' => $poll->options->random()->id,
                            'user_id' => $user->id,
                        ]);
                    }
                });

                // Create some anonymous votes
                Vote::factory(fake()->numberBetween(5, 15))
                    ->anonymous()
                    ->create([
                        'poll_id' => $poll->id,
                        'poll_option_id' => $poll->options->random()->id,
                    ]);
            });
    }
}
