<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PollService
{
    public function createPoll(array $data, User $user): Poll
    {
        $poll = Poll::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        // Create options
        collect($data['options'])->each(function ($optionText, $index) use ($poll) {
            $poll->options()->create([
                'text' => $optionText,
                'order' => $index,
            ]);
        });

        return $poll->load('options');
    }

    public function updatePoll(Poll $poll, array $data): Poll
    {
        $poll->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? $poll->description,
            'is_active' => $data['is_active'] ?? $poll->is_active,
            'expires_at' => $data['expires_at'] ?? $poll->expires_at,
        ]);

        if (isset($data['options'])) {
            // Delete existing options not in the new set
            $poll->options()->whereNotIn('text', $data['options'])->delete();

            // Update or create options
            collect($data['options'])->each(function ($optionText, $index) use ($poll) {
                $poll->options()->updateOrCreate(
                    ['text' => $optionText],
                    ['order' => $index]
                );
            });
        }

        return $poll->load('options');
    }

    public function listPolls(?User $user = null, int $perPage = 10): CursorPaginator {
        $query = Poll::query()
            ->select(['id','user_id','title','description','is_active','expires_at','created_at','updated_at'])
            ->with(['options' => function($q){ $q->select('id','poll_id','text','order'); }])
            ->withCount('votes')
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest('id');

        return $query->cursorPaginate($perPage);
    }

    public function getActivePolls(int $perPage = 18): CursorPaginator {
        return Poll::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['options' => function($q){ $q->select('id','poll_id','text','order'); }])
            ->withCount('votes')
            ->latest('id')
            ->cursorPaginate($perPage);
    }

    public function getPoll(int $id): ?Poll
    {
        return Poll::with(['options' => function($q){ $q->select('id','poll_id','text','order'); }])
            ->withCount('votes')
            ->findOrFail($id);
    }

    public function deletePoll(Poll $poll): bool
    {
        return $poll->delete();
    }

    public function togglePollStatus(Poll $poll): Poll
    {
        $poll->update(['is_active' => !$poll->is_active]);
        return $poll;
    }
}
