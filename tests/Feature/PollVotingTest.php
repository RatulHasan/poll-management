<?php

namespace Tests\Feature;

use App\Events\NewVoteCast;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PollVotingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Poll $poll;
    private PollOption $option;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->poll = Poll::factory()->create([
            'is_active' => true,
        ]);
        $this->option = PollOption::factory()->create([
            'poll_id' => $this->poll->id,
        ]);
    }

    public function test_can_view_active_polls(): void
    {
        $response = $this->get(route('polls.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Welcome')
                ->has('polls.data')
            );
    }

    public function test_can_view_single_poll(): void
    {
        $response = $this->get(route('polls.show', $this->poll));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Polls/Show')
                ->has('poll')
                ->has('statistics')
                ->has('hasVoted')
            );
    }

    public function test_can_cast_vote(): void
    {
        Event::fake();

        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Polls/Show')
                ->where('hasVoted', true)
                ->has('userVote')
            );

        $this->assertDatabaseHas('votes', [
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
            'user_id' => $this->user->id,
        ]);

        Event::assertDispatched(NewVoteCast::class);
    }

    public function test_cannot_vote_on_inactive_poll(): void
    {
        $this->poll->update(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('vote');

        $this->assertDatabaseMissing('votes', [
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
        ]);
    }

    public function test_cannot_vote_twice_on_same_poll(): void
    {
        // Cast first vote
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        // Attempt second vote
        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('vote');

        $this->assertDatabaseCount('votes', 1);
    }

    public function test_anonymous_users_can_vote(): void
    {
        $response = $this->post(route('polls.vote', $this->poll), [
            'option_id' => $this->option->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('votes', [
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
            'user_id' => null,
        ]);
    }

    public function test_invalid_option_returns_error(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => 999999, // Invalid option ID
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('votes', 0);
    }
    public function test_cannot_vote_with_option_from_another_poll(): void
    {
        // Create a different poll with its own option
        $otherPoll = \App\Models\Poll::factory()->create(['is_active' => true]);
        $otherOption = \App\Models\PollOption::factory()->create(['poll_id' => $otherPoll->id]);

        // Try to vote on the first poll using an option from the other poll
        $response = $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $otherOption->id,
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('option_id');

        // Ensure no vote was created for the first poll
        $this->assertDatabaseMissing('votes', [
            'poll_id' => $this->poll->id,
            'poll_option_id' => $otherOption->id,
        ]);
    }

    public function test_anonymous_cannot_vote_twice_by_ip(): void
    {
        // First anonymous vote from an IP
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ])
            ->assertStatus(200);

        // Second anonymous vote from the same IP should fail
        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->post(route('polls.vote', $this->poll), [
                'option_id' => $this->option->id,
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('vote');

        // Ensure only one vote exists in total
        $this->assertDatabaseCount('votes', 1);
    }
}
