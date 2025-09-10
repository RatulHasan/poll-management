<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Models\Poll;
use App\Services\PollService;
use App\Services\VoteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PollController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollService $pollService,
        private readonly VoteService $voteService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Poll::class);
        $polls = $this->pollService->listPolls(
            $request->user(),
            $request->input('per_page', 10)
        );

        return Inertia::render('Admin/Polls/Index', [
            'polls' => $polls,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Poll::class);
        return Inertia::render('Admin/Polls/Create');
    }

    public function store(CreatePollRequest $request): RedirectResponse
    {
        $this->authorize('create', Poll::class);
        $poll = $this->pollService->createPoll(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('admin.polls.show', $poll)
            ->with('success', 'Poll created successfully');
    }

    public function show(Poll $poll): Response
    {
        $this->authorize('view', $poll);

        return Inertia::render('Admin/Polls/Show', [
            'poll' => $poll->load('options'),
            'statistics' => $this->voteService->getVoteStatistics($poll),
        ]);
    }

    public function edit(Poll $poll): Response
    {
        $this->authorize('update', $poll);

        return Inertia::render('Admin/Polls/Edit', [
            'poll' => $poll->load('options'),
        ]);
    }

    public function update(UpdatePollRequest $request, Poll $poll): Response
    {
        $this->authorize('update', $poll);
        $poll = $this->pollService->updatePoll($poll, $request->validated());

        return Inertia::render('Admin/Polls/Show', [
            'poll' => $poll->load('options'),
            'statistics' => $this->voteService->getVoteStatistics($poll),
        ]);
    }

    public function destroy(Poll $poll)
    {
        $this->authorize('delete', $poll);

        $this->pollService->deletePoll($poll);

        return redirect()->route('admin.polls.index')
            ->with('success', 'Poll deleted successfully.');
    }

    public function toggleStatus(Poll $poll)
    {
        $this->authorize('update', $poll);

        $poll = $this->pollService->togglePollStatus($poll);

        return back()->with('success',
            $poll->is_active ? 'Poll activated successfully.' : 'Poll deactivated successfully.'
        );
    }
}
