<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PollService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreatePollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poll:create {user? : User ID who creates the poll}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new poll interactively';

    /**
     * Execute the console command.
     */
    public function handle(PollService $pollService): int
    {
        $user = $this->getUser();
        if (!$user) {
            $this->error('No valid user found.');
            return 1;
        }

        $title = $this->ask('What is the poll title?');
        $description = $this->ask('Enter poll description (optional)');

        $options = [];
        $this->info('Enter poll options (minimum 2, enter empty line to finish):');
        do {
            $optionNumber = count( $options ) + 1;
            $prompt       = "Enter option {$optionNumber}";
            if ( count( $options ) >= 2 ) {
                $prompt .= ' (or press enter to finish)';
            }

            $option = $this->ask( $prompt );

            if ( $option ) {
                $options[] = $option;
            } elseif ( count( $options ) < 2 ) {
                $this->error( 'You must enter at least 2 options.' );
            }
        } while ($option !== null || count($options) < 2);

        $isActive = $this->confirm('Should the poll be active immediately?', true);
        $hasExpiry = $this->confirm('Should the poll have an expiry date?', false);
        $expiresAt = null;
        if ($hasExpiry) {
            $days = $this->ask('How many days should the poll be active for?', 7);
            $expiresAt = Carbon::now()->addDays($days);
        }

        try {
            $poll = $pollService->createPoll([
                'title' => $title,
                'description' => $description,
                'is_active' => $isActive,
                'expires_at' => $expiresAt,
                'options' => $options,
            ], $user);

            $this->info('Poll created successfully!');
            $this->table(
                ['ID', 'User', 'Title', 'Options', 'Expires'],
                [[
                    $poll->id,
                    $user->name,
                    $poll->title,
                    count($poll->options) . ' options',
                    $poll->expires_at ? $poll->expires_at->format('Y-m-d H:i') : 'Never',
                ]]
            );
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create poll: ' . $e->getMessage());
            return 1;
        }
    }

    protected function getUser(): ?User
    {
        $userId = $this->argument('user');
        if ($userId) {
            return User::find($userId);
        }

        // If no user provided, get the first admin user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in the system. Please create a user first.');
            return null;
        }
        return $user;
    }
}
