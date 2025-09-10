<?php

namespace App\Events;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewVoteCast implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Poll $poll,
        public Vote $vote,
        public array $statistics
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("poll.{$this->poll->id}"),
        ];
    }

    public function broadcastWith(): array {
        return [
            'statistics' => $this->statistics,
            'poll_id' => $this->poll->id,
            'vote_id' => $this->vote->id,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string {
        return 'vote.cast';
    }

    // Ensure immediate broadcasting
    public function shouldBroadcast(): bool {
        return true;
    }

}
