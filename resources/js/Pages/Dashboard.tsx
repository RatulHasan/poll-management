import LiveResults from '@/Components/LiveResults';
import { useUserNotifications } from '@/hooks/usePollUpdates';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { type PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { useCallback, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

// Configure Chart.js defaults for dark mode support
ChartJS.defaults.color = 'rgb(156, 163, 175)';
ChartJS.defaults.borderColor = 'rgb(75, 85, 99)';

interface PollStat {
    id: number;
    title: string;
    votes: number;
}

interface Stats {
    total_polls: number;
    total_votes: number;
    active_polls: number;
    votes_per_poll: PollStat[];
    votes_over_time: Record<string, number>;
}

interface DashboardProps extends PageProps {
    stats: Stats;
}

export default function Dashboard({ auth, stats }: DashboardProps) {
    const defaultStats: Stats = {
        total_polls: 0,
        total_votes: 0,
        active_polls: 0,
        votes_per_poll: [],
        votes_over_time: {},
    };

    const safeStats = stats || defaultStats;

    // State for real-time updates
    const [totalVotes, setTotalVotes] = useState(safeStats.total_votes);
    const [votesOverTime, setVotesOverTime] = useState(
        safeStats.votes_over_time,
    );
    const [votesPerPoll, setVotesPerPoll] = useState(safeStats.votes_per_poll);

    const updateChartsForNewVote = useCallback(
        (pollId?: number, pollTitle?: string) => {
            // Update votes over time for today
            const today = new Date().toISOString().split('T')[0];
            setVotesOverTime((prev) => ({
                ...prev,
                [today]: (prev[today] || 0) + 1,
            }));

            // Increment total votes
            setTotalVotes((prev) => prev + 1);

            // Update votes per poll: increment if exists; else add with votes=1 (using title if available), then sort desc and keep top 5
            setVotesPerPoll((prev) => {
                const idx =
                    pollId != null
                        ? prev.findIndex((p) => p.id === pollId)
                        : -1;
                const next = [...prev];
                if (idx >= 0) {
                    next[idx] = { ...next[idx], votes: next[idx].votes + 1 };
                } else if (pollId != null && pollTitle) {
                    next.push({ id: pollId, title: pollTitle, votes: 1 });
                }
                next.sort((a, b) => b.votes - a.votes);
                return next.slice(0, 5);
            });
        },
        [],
    );

    const { isConnected } = useUserNotifications(auth.user?.id, {
        onNotification: (notification) => {
            if (notification.status === 'success' && notification.poll_id) {
                const pollTitle = notification.poll_title || notification.message?.replace(
                    'New vote received on poll: ',
                    '',
                );
                updateChartsForNewVote(notification.poll_id, pollTitle);
            }
        },
    });

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    color: 'rgb(156, 163, 175)',
                },
                grid: {
                    color: 'rgb(75, 85, 99)',
                },
            },
            x: {
                ticks: {
                    color: 'rgb(156, 163, 175)',
                },
                grid: {
                    color: 'rgb(75, 85, 99)',
                },
            },
        },
        plugins: {
            legend: {
                labels: {
                    color: 'rgb(156, 163, 175)',
                },
            },
        },
    };

    const votesOverTimeData = {
        labels: Object.keys(votesOverTime),
        datasets: [
            {
                label: 'Votes',
                data: Object.values(votesOverTime),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.1,
                fill: true,
            },
        ],
    };

    const votesPerPollData = {
        labels: votesPerPoll.map((poll: PollStat) => poll.title),
        datasets: [
            {
                label: 'Votes per Poll',
                data: votesPerPoll.map((poll: PollStat) => poll.votes),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
            },
        ],
    };

    console.log('votesPerPoll: ', votesPerPoll);
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Stats Overview Cards */}
                    <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                        {/* Total Polls Card */}
                        <div className="overflow-hidden rounded-lg bg-white shadow-sm transition-colors duration-200 dark:bg-gray-800">
                            <div className="p-6">
                                <h3 className="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    Total Polls
                                </h3>
                                <p className="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {safeStats.total_polls}
                                </p>
                            </div>
                        </div>

                        {/* Total Votes Card */}
                        <div className="overflow-hidden rounded-lg bg-white shadow-sm transition-colors duration-200 dark:bg-gray-800">
                            <div className="p-6">
                                <div className="flex items-center justify-between">
                                    <h3 className="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                                        Total Votes
                                    </h3>
                                    {isConnected && <LiveResults />}
                                </div>
                                <p className="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {totalVotes}
                                </p>
                            </div>
                        </div>

                        {/* Active Polls Card */}
                        <div className="overflow-hidden rounded-lg bg-white shadow-sm transition-colors duration-200 dark:bg-gray-800">
                            <div className="p-6">
                                <h3 className="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    Active Polls
                                </h3>
                                <p className="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    {safeStats.active_polls}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Charts */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {/* Votes Over Time Chart */}
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow-sm transition-colors duration-200 dark:bg-gray-800">
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    Votes Over Time (Last 7 Days)
                                </h3>
                                {isConnected && <LiveResults />}
                            </div>
                            <div className="h-[300px]">
                                <Line
                                    data={votesOverTimeData}
                                    options={chartOptions}
                                />
                            </div>
                        </div>

                        {/* Top Polls Chart */}
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow-sm transition-colors duration-200 dark:bg-gray-800">
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                    Top Polls by Votes
                                </h3>
                                {isConnected && <LiveResults />}
                            </div>
                            <div className="h-[300px]">
                                <Bar
                                    data={votesPerPollData}
                                    options={chartOptions}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
