<?php

namespace App\Http\Controllers;

use App\Http\Requests\CastVoteRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Services\PollService;
use App\Services\VoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPollController extends Controller
{
    public function __construct(
        private readonly PollService $pollService,
        private readonly VoteService $voteService
    ) {}

    public function index(): Response
    {
        $polls = $this->pollService->getActivePolls();
        return Inertia::render('Welcome', [
            'polls' => $polls,
        ]);
    }

    public function show(Request $request, Poll $poll): Response
    {
        if (!$poll->is_active || ($poll->expires_at && $poll->isExpired())) {
            abort(404, 'Poll not found or has expired.');
        }

        // Single query to fetch user's vote; derive hasVoted to avoid duplicate exists() query
        $userVote = $this->voteService->getUserVote(
            $poll,
            $request->ip(),
            $request->user()
        );
        $hasVoted = (bool) $userVote;

        $poll->load(['options' => function($q){ $q->select('id','poll_id','text','order'); }]);

        return Inertia::render('Polls/Show', [
            'poll' => $poll,
            'hasVoted' => $hasVoted,
            'userVote' => $userVote,
            'statistics' => $this->voteService->getVoteStatistics($poll),
        ]);
    }

    public function vote(CastVoteRequest $request, Poll $poll)
    {
        if (!$poll->is_active || ($poll->expires_at && $poll->isExpired())) {
            return redirect()->back()->withErrors(['vote' => 'This poll is not active or has expired.']);
        }

        try {
            $option = PollOption::where('id', $request->option_id)
                ->where('poll_id', $poll->id)
                ->firstOrFail();

            $this->voteService->castVote(
                $poll,
                $option,
                $request->ip(),
                $request->user(),
                $request->userAgent()
            );

            $userVote = $this->voteService->getUserVote($poll, $request->ip(), $request->user());
            $poll->load(['options' => function($q){ $q->select('id','poll_id','text','order'); }]);

            return Inertia::render('Polls/Show', [
                'poll' => $poll,
                'hasVoted' => true,
                'userVote' => $userVote,
                'statistics' => $this->voteService->getVoteStatistics($poll),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['vote' => $e->getMessage()]);
        }
    }
}
