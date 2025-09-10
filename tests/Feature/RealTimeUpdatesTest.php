<?php

namespace Tests\Feature;

use App\Events\NewVoteCast;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealTimeUpdatesTest extends TestCase
{
    use RefreshDatabase;

    private Poll $poll;
    private PollOption $option;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->poll = Poll::factory()->create(['is_active' => true]);
        $this->option = PollOption::factory()->create(['poll_id' => $this->poll->id]);
    }

    public function test_new_vote_triggers_broadcast_event(): void
    {
        Event::fake([NewVoteCast::class]);

        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        Event::assertDispatched(NewVoteCast::class, function ($event) {
            return $event->poll->id === $this->poll->id &&
                   isset($event->statistics['total_votes']) &&
                   isset($event->statistics['options']);
        });
    }

    public function test_broadcast_channel_authorization(): void
    {
        $channelName = "poll.{$this->poll->id}";

        $response = $this->actingAs($this->user)
            ->post("/broadcasting/auth", [
                'socket_id' => '123.456',
                'channel_name' => $channelName,
            ]);

        // Public channels don't require authentication, so they should get a successful response
        $response->assertStatus(200);
    }

    public function test_broadcast_with_correct_statistics(): void
    {
        Event::fake([NewVoteCast::class]);

        // Create some initial votes
        Vote::factory()->count(3)->create([
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
        ]);

        // Cast a new vote
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        Event::assertDispatched(NewVoteCast::class, function ($event) {
            return $event->statistics['total_votes'] === 4 &&
                   $event->statistics['options'][0]['votes'] === 4 &&
                   $event->statistics['options'][0]['percentage'] === 100.0;
        });
    }

    public function test_updates_are_broadcasted_immediately(): void
    {
        Event::fake([NewVoteCast::class]);

        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        Event::assertDispatched(NewVoteCast::class, function ($event) {
            return $event->shouldBroadcast() === true;
        });
    }
}
