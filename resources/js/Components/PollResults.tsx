import LiveResults from '@/Components/LiveResults';
import { usePollUpdates } from '@/hooks/usePollUpdates';
import { VoteStatistics } from '@/types';
import { useEffect, useState } from 'react';

interface Props {
    pollId: number;
    initialStatistics: VoteStatistics;
}

export default function PollResults({ pollId, initialStatistics }: Props) {
    const [statistics, setStatistics] =
        useState<VoteStatistics>(initialStatistics);

    // Use our custom hook for real-time updates
    const { isConnected } = usePollUpdates(pollId, {
        onVoteCast: (newStats) => {
            console.log('Received new vote stats:', newStats);
            setStatistics(newStats);
        },
    });

    // Update statistics when initialStatistics changes
    useEffect(() => {
        setStatistics(initialStatistics);
    }, [initialStatistics]);

    const getProgressBarColor = (percentage: number) => {
        if (percentage > 66) return 'bg-green-600 dark:bg-green-500';
        if (percentage > 33) return 'bg-blue-600 dark:bg-blue-500';
        return 'bg-indigo-600 dark:bg-indigo-500';
    };

    return (
        <div className="space-y-6">
            {isConnected && <LiveResults />}

            <div className="space-y-4">
                {statistics.options.map((option) => (
                    <div key={option.id} className="space-y-2">
                        <div className="flex justify-between text-sm">
                            <span className="font-medium text-gray-700 dark:text-gray-300">
                                {option.text}
                            </span>
                            <span className="text-gray-600 dark:text-gray-400">
                                {option.votes} vote
                                {option.votes !== 1 ? 's' : ''} (
                                {option.percentage}%)
                            </span>
                        </div>
                        <div className="h-2.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                className={`h-2.5 rounded-full transition-all duration-500 ease-out ${getProgressBarColor(option.percentage)}`}
                                style={{ width: `${option.percentage}%` }}
                            ></div>
                        </div>
                    </div>
                ))}

                <div className="mt-6 text-sm text-gray-600 dark:text-gray-400">
                    Total votes: {statistics.total_votes}
                </div>
            </div>
        </div>
    );
}
