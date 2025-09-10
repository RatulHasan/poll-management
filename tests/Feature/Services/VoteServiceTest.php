<?php

namespace Tests\Feature\Services;

use App\Events\NewVoteCast;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private VoteService $voteService;
    private User $user;
    private Poll $poll;
    private PollOption $option;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voteService = app(VoteService::class);
        $this->user = User::factory()->create();

        // Create a poll with options
        $this->poll = Poll::factory()->create([
            'is_active' => true,
        ]);
        $this->option = PollOption::factory()->create([
            'poll_id' => $this->poll->id,
        ]);
    }

    public function test_can_cast_vote(): void
    {
        Event::fake();

        $vote = $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user,
            'PHPUnit Test Agent'
        );

        $this->assertDatabaseHas('votes', [
            'id' => $vote->id,
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
        ]);

        Event::assertDispatched(NewVoteCast::class);
    }

    public function test_prevents_duplicate_votes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You have already voted in this poll.');

        // Cast first vote
        $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user
        );

        // Attempt to cast second vote
        $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user
        );
    }

    public function test_prevents_voting_on_inactive_polls(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This poll is not active or has expired.');

        $this->poll->update(['is_active' => false]);

        $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user
        );
    }

    public function test_prevents_voting_on_expired_polls(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This poll is not active or has expired.');

        $this->poll->update(['expires_at' => now()->subDay()]);

        $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user
        );
    }

    public function test_calculates_vote_statistics_correctly(): void
    {
        // Create multiple options and votes
        $option2 = PollOption::factory()->create(['poll_id' => $this->poll->id]);

        $this->voteService->castVote($this->poll, $this->option, '127.0.0.1', $this->user);
        $this->voteService->castVote($this->poll, $this->option, '127.0.0.2');
        $this->voteService->castVote($this->poll, $option2, '127.0.0.3');

        $stats = $this->voteService->getVoteStatistics($this->poll);

        $this->assertEquals(3, $stats['total_votes']);
        $this->assertCount(2, $stats['options']);

        // Find option1 stats
        $option1Stats = collect($stats['options'])->firstWhere('id', $this->option->id);
        $this->assertEquals(2, $option1Stats['votes']);
        $this->assertEquals(66.67, $option1Stats['percentage']);

        // Find option2 stats
        $option2Stats = collect($stats['options'])->firstWhere('id', $option2->id);
        $this->assertEquals(1, $option2Stats['votes']);
        $this->assertEquals(33.33, $option2Stats['percentage']);
    }

    public function test_tracks_user_votes_correctly(): void
    {
        $vote = $this->voteService->castVote(
            $this->poll,
            $this->option,
            '127.0.0.1',
            $this->user
        );

        $hasVoted = $this->voteService->hasUserVoted($this->poll, '127.0.0.1', $this->user);
        $this->assertTrue($hasVoted);

        $userVote = $this->voteService->getUserVote($this->poll, '127.0.0.1', $this->user);
        $this->assertNotNull($userVote);
        $this->assertEquals($vote->id, $userVote->id);
    }
    public function test_guest_and_logged_in_user_with_same_ip_can_both_vote(): void
    {
        $ip = '10.0.0.1';
        // Logged in user votes first
        $this->voteService->castVote($this->poll, $this->option, $ip, $this->user);

        // Guest from same IP should still be able to vote
        $guestVote = $this->voteService->castVote($this->poll, $this->option, $ip, null);

        $this->assertDatabaseHas('votes', [
            'id' => $guestVote->id,
            'poll_id' => $this->poll->id,
            'poll_option_id' => $this->option->id,
            'user_id' => null,
            'ip_address' => $ip,
        ]);
    }

    public function test_prevents_guest_double_vote_but_allows_logged_in_after_guest_with_same_ip(): void
    {
        $ip = '10.0.0.2';
        // Guest votes
        $this->voteService->castVote($this->poll, $this->option, $ip, null);

        // The same guest IP cannot vote again
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You have already voted in this poll.');
        try {
            $this->voteService->castVote($this->poll, $this->option, $ip, null);
        } finally {
            // But a logged in user with the same IP should be allowed
            $otherUser = User::factory()->create();
            $vote = $this->voteService->castVote($this->poll, $this->option, $ip, $otherUser);
            $this->assertDatabaseHas('votes', [
                'id' => $vote->id,
                'poll_id' => $this->poll->id,
                'poll_option_id' => $this->option->id,
                'user_id' => $otherUser->id,
                'ip_address' => $ip,
            ]);
        }
    }
}
