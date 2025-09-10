<?php

namespace Tests\Feature;

use App\Events\NewVoteCast;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Poll $poll;
    private PollOption $option;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->poll = Poll::factory()->create(['is_active' => true]);
        $this->option = PollOption::factory()->create(['poll_id' => $this->poll->id]);
    }

    public function test_poll_broadcast_event_is_fired(): void
    {
        Event::fake([NewVoteCast::class]);

        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        Event::assertDispatched(NewVoteCast::class, function ($event) {
            return $event->poll->id === $this->poll->id
                && isset($event->statistics['total_votes'])
                && isset($event->statistics['options']);
        });
    }
}
