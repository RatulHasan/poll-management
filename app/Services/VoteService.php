<?php

namespace App\Services;

use App\Events\NewVoteCast;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use App\Notifications\NewVoteNotification;
use Illuminate\Support\Facades\DB;

class VoteService {

    public function castVote( Poll $poll, PollOption $option, string $ipAddress, ?User $user = null, ?string $userAgent = null ): Vote {
        if ( ! $poll->is_active || ( $poll->expires_at && $poll->isExpired() ) ) {
            throw new \RuntimeException( 'This poll is not active or has expired.' );
        }

        if ( $option->poll_id !== $poll->id ) {
            throw new \RuntimeException( 'Invalid option for this poll.' );
        }

        return DB::transaction( function () use ( $poll, $option, $ipAddress, $user, $userAgent ) {
            // Check for existing votes if multiple votes are not allowed
            $existingVote = Vote::where( 'poll_id', $poll->id )
                                ->when( $user, function ( $query ) use ( $user ) {
                                    $query->where( 'user_id', $user->id );
                                } )
                                ->when( ! $user, function ( $query ) use ( $ipAddress ) {
                                    $query->whereNull( 'user_id' )
                                          ->where( 'ip_address', $ipAddress );
                                } )
                                ->exists();

            if ( $existingVote ) {
                throw new \RuntimeException( 'You have already voted in this poll.' );
            }

            // Create the vote
            $vote = Vote::create( [
                'poll_id'        => $poll->id,
                'poll_option_id' => $option->id,
                'user_id'        => $user?->id,
                'ip_address'     => $ipAddress,
                'user_agent'     => $userAgent,
            ] );

            $statistics = $this->getVoteStatistics( $poll );

            // Broadcast the event with statistics
            event( new NewVoteCast( $poll, $vote, $statistics ) );

            // Send notification to poll creator.
            $poll->loadMissing( 'user' );
            if ( $poll->user ) {
                $poll->user->notify( new NewVoteNotification( $poll, $vote ) );
            }

            return $vote;
        } );
    }

    public function getVoteStatistics( Poll $poll ): array {
        $options    = $poll->options()->select( 'id', 'text' )->withCount( 'votes' )->get();
        $totalVotes = (int) $options->sum( 'votes_count' );

        $optionStats = $options
            ->map( function ( $option ) use ( $totalVotes ) {
                $votes = (int) $option->votes_count;

                return [
                    'id'         => $option->id,
                    'text'       => $option->text,
                    'votes'      => $votes,
                    'percentage' => $totalVotes > 0
                        ? round( ( $votes / $totalVotes ) * 100, 2 )
                        : 0,
                ];
            } )
            ->values()
            ->toArray();

        return [
            'total_votes' => $totalVotes,
            'options'     => $optionStats,
        ];
    }

    public function hasUserVoted( Poll $poll, string $ipAddress, ?User $user = null ): bool {
        return Vote::where( 'poll_id', $poll->id )
                   ->when( $user, function ( $query ) use ( $user ) {
                       $query->where( 'user_id', $user->id );
                   } )
                   ->when( ! $user, function ( $query ) use ( $ipAddress ) {
                       $query->whereNull( 'user_id' )
                             ->where( 'ip_address', $ipAddress );
                   } )
                   ->exists();
    }

    public function getUserVote( Poll $poll, string $ipAddress, ?User $user = null ): ?Vote {
        return Vote::where( 'poll_id', $poll->id )
                   ->when( $user, function ( $query ) use ( $user ) {
                       $query->where( 'user_id', $user->id );
                   } )
                   ->when( ! $user, function ( $query ) use ( $ipAddress ) {
                       $query->whereNull( 'user_id' )
                             ->where( 'ip_address', $ipAddress );
                   } )
                   ->with( 'option' )
                   ->first();
    }
}
