<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AnalyticsService {

    public function getDashboardStats(): array {
        try {
            $user = auth()->user();

            if ( ! $user ) {
                return $this->getDefaultStats();
            }

            // Get user's poll IDs once
            $userPollIds = Poll::where( 'user_id', $user->id )->pluck( 'id' );

            // Get aggregate counts in a single query
            $pollStats = Poll::where( 'user_id', $user->id )
                ->selectRaw( '
                    COUNT(*) as total_polls,
                    SUM(CASE WHEN is_active = 1 AND (expires_at IS NULL OR expires_at > ?) THEN 1 ELSE 0 END) as active_polls
                ', [ now() ] )
                ->first();

            // Get total votes in a single query
            $totalVotes = Vote::whereIn( 'poll_id', $userPollIds )->count();

            // Get votes per poll efficiently
            $votesPerPoll = Poll::where( 'user_id', $user->id )
                ->select( 'id', 'title' )
                ->withCount( 'votes' )
                ->orderByDesc( 'votes_count' )
                ->limit( 5 )
                ->get()
                ->map( fn( $poll ) => [
                    'id'    => $poll->id,
                    'title' => $poll->title,
                    'votes' => (int) $poll->votes_count,
                ] );

            // Get votes over time with a single efficient query
            $votesOverTime = Vote::whereIn( 'poll_id', $userPollIds )
                ->where( 'created_at', '>=', Carbon::now()->subDays( 7 ) )
                ->selectRaw( 'DATE(created_at) as date, COUNT(*) as count' )
                ->groupBy( 'date' )
                ->orderBy( 'date' )
                ->get()
                ->pluck( 'count', 'date' );

            // Fill in missing days efficiently
            $dateRange = collect( range( 0, 6 ) )
                ->mapWithKeys( function ( $days ) use ( $votesOverTime ) {
                    $date = Carbon::now()->subDays( $days )->format( 'Y-m-d' );

                    return [ $date => $votesOverTime[$date] ?? 0 ];
                } )
                ->sortKeys();

            return [
                'total_polls'     => $pollStats->total_polls,
                'total_votes'     => $totalVotes,
                'active_polls'    => $pollStats->active_polls,
                'votes_per_poll'  => $votesPerPoll->toArray(),
                'votes_over_time' => $dateRange->toArray(),
            ];
        } catch ( Exception $e ) {
            report( $e ); // Log the error

            return $this->getDefaultStats();
        }
    }

    private function getDefaultStats(): array {
        return [
            'total_polls'     => 0,
            'total_votes'     => 0,
            'active_polls'    => 0,
            'votes_per_poll'  => [],
            'votes_over_time' => array_fill_keys(
                array_map(
                    fn( $days ) => Carbon::now()->subDays( $days )->format( 'Y-m-d' ),
                    range( 0, 6 )
                ),
                0
            ),
        ];
    }
}
