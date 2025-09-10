<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPollTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_poll(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.polls.store'), [
                'title' => 'Test Poll',
                'description' => 'Test Description',
                'is_active' => true,
                'options' => ['Option 1', 'Option 2', 'Option 3'],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('polls', [
            'title' => 'Test Poll',
            'description' => 'Test Description',
            'is_active' => true,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_poll_expiration_works(): void
    {
        // Create an expired poll
        $poll = $this->actingAs($this->admin)
            ->post(route('admin.polls.store'), [
                'title' => 'Expired Poll',
                'is_active' => true,
                'expires_at' => now()->subDay(),
                'options' => ['Option 1', 'Option 2'],
            ]);

        $response = $this->get(route('polls.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('polls.data', 0)
        );
    }

    public function test_poll_statistics_are_accurate(): void
    {
        // Create a poll with options
        $response = $this->actingAs($this->admin)
            ->post(route('admin.polls.store'), [
                'title' => 'Stats Poll',
                'is_active' => true,
                'options' => ['Option 1', 'Option 2'],
            ]);

        $poll = \App\Models\Poll::latest()->first();
        $option1 = $poll->options()->first();

        // Create some votes
        \App\Models\Vote::factory()->count(3)->create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option1->id,
        ]);

        $response = $this->get(route('polls.show', $poll));

        $response->assertInertia(fn ($page) => $page
            ->has('statistics')
            ->where('statistics.total_votes', 3)
            ->where('statistics.options.0.votes', 3)
            ->where('statistics.options.0.percentage', fn ($value) =>
                abs($value - 100.00) < 0.01
            )
        );
    }
}
