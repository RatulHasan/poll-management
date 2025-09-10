<?php

namespace Tests\Feature\Services;

use App\Models\Poll;
use App\Models\User;
use App\Services\PollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollServiceTest extends TestCase
{
    use RefreshDatabase;

    private PollService $pollService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pollService = app(PollService::class);
        $this->user = User::factory()->create();
    }

    public function test_can_create_poll_with_options(): void
    {
        $pollData = [
            'title' => 'Test Poll',
            'description' => 'Test Description',
            'is_active' => true,
            'expires_at' => now()->addDays(7),
            'options' => ['Option 1', 'Option 2', 'Option 3'],
        ];

        $poll = $this->pollService->createPoll($pollData, $this->user);

        $this->assertDatabaseHas('polls', [
            'id' => $poll->id,
            'title' => $pollData['title'],
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(3, $poll->options);
        foreach ($pollData['options'] as $option) {
            $this->assertDatabaseHas('poll_options', [
                'poll_id' => $poll->id,
                'text' => $option,
            ]);
        }
    }

    public function test_can_update_poll_and_options(): void
    {
        $poll = Poll::factory()
            ->has(\App\Models\PollOption::factory()->count(3), 'options')
            ->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'options' => ['New Option 1', 'New Option 2'],
        ];

        $updatedPoll = $this->pollService->updatePoll($poll, $updateData);

        $this->assertDatabaseHas('polls', [
            'id' => $updatedPoll->id,
            'title' => $updateData['title'],
            'description' => $updateData['description'],
        ]);

        $this->assertCount(2, $updatedPoll->options);
        foreach ($updateData['options'] as $option) {
            $this->assertDatabaseHas('poll_options', [
                'poll_id' => $updatedPoll->id,
                'text' => $option,
            ]);
        }
    }

    public function test_can_list_active_polls(): void
    {
        // Create some active and inactive polls
        Poll::factory()->count(3)->create(['is_active' => true]);
        Poll::factory()->count(2)->create(['is_active' => false]);

        $activePollsPage = $this->pollService->getActivePolls(10);

        $this->assertCount(3, $activePollsPage->items());
        foreach ($activePollsPage->items() as $poll) {
            $this->assertTrue($poll->is_active);
        }
    }

    public function test_can_toggle_poll_status(): void
    {
        $poll = Poll::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $updatedPoll = $this->pollService->togglePollStatus($poll);
        $this->assertFalse($updatedPoll->is_active);

        $updatedPoll = $this->pollService->togglePollStatus($updatedPoll);
        $this->assertTrue($updatedPoll->is_active);
    }

    public function test_expired_polls_are_not_listed_as_active(): void
    {
        // Create active but expired poll
        Poll::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        // Create active and not expired poll
        Poll::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $activePollsPage = $this->pollService->getActivePolls(10);

        $this->assertCount(1, $activePollsPage->items());
        $this->assertTrue($activePollsPage->items()[0]->expires_at->isFuture());
    }
}
