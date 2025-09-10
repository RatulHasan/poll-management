<?php

namespace App\Notifications;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewVoteNotification extends Notification implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        public Poll $poll,
        public Vote $vote
    ) {}

    public function via($notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'info',
            'status' => 'success',
            'message' => "New vote received on poll: {$this->poll->title}",
            'poll_id' => $this->poll->id,
            'poll_title' => $this->poll->title,
            'vote_id' => $this->vote->id,
            'created_at' => now()->toISOString(),
        ]);
    }
}
