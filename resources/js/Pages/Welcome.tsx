import GuestLayout from '@/Layouts/GuestLayout';
import { Poll } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';

interface Props {
    polls: {
        data: Poll[];
        next_page_url?: string | null;
        prev_page_url?: string | null;
        per_page?: number;
        path?: string;
    };
    auth: {
        user: unknown;
    };
}

export default function Welcome({ auth, polls }: Props) {
    return (
        <GuestLayout>
            <Head title="Polls" />
            <div className="min-h-screen bg-gray-100 py-12 dark:bg-gray-900">
                <div className="relative min-h-screen sm:flex sm:items-center sm:justify-center">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        {/* Header Section */}
                        <div className="mb-8 flex items-center justify-between">
                            <div>
                                <h1 className="text-4xl font-bold text-gray-900 dark:text-white">
                                    Active Polls
                                </h1>
                                <p className="mt-2 text-gray-600 dark:text-gray-400">
                                    Cast your vote and see results in real-time
                                </p>
                            </div>
                            <div className="space-x-4">
                                {auth.user ? (
                                    <>
                                        <Link
                                            href={route('dashboard')}
                                            className="font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-indigo-500 dark:text-gray-400 dark:hover:text-white"
                                        >
                                            Dashboard
                                        </Link>
                                        <Link
                                            href={route('admin.polls.create')}
                                            className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900"
                                        >
                                            Create Poll
                                        </Link>
                                    </>
                                ) : (
                                    <>
                                        <Link
                                            href={route('login')}
                                            className="font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-indigo-500 dark:text-gray-400 dark:hover:text-white"
                                        >
                                            Log in
                                        </Link>
                                        <Link
                                            href={route('register')}
                                            className="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-indigo-500 dark:text-gray-400 dark:hover:text-white"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Poll Grid */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {polls.data.map((poll) => (
                                <Link
                                    key={poll.id}
                                    href={route('polls.show', poll.id)}
                                    className="group block"
                                >
                                    <div className="overflow-hidden rounded-lg bg-white shadow-xl transition-all duration-200 group-hover:-translate-y-1 group-hover:shadow-2xl dark:bg-gray-800">
                                        <div className="p-6">
                                            <h2 className="mb-2 text-xl font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                                                {poll.title}
                                            </h2>
                                            {poll.description && (
                                                <p className="mb-4 line-clamp-2 text-gray-600 dark:text-gray-400">
                                                    {poll.description}
                                                </p>
                                            )}
                                            <div className="flex flex-col space-y-2 text-sm">
                                                <div className="flex justify-between text-gray-500 dark:text-gray-400">
                                                    <span>Options:</span>
                                                    <span>
                                                        {poll.options.length}
                                                    </span>
                                                </div>
                                                <div className="flex justify-between text-gray-500 dark:text-gray-400">
                                                    <span>Total Votes:</span>
                                                    <span>
                                                        {poll.votes_count ??
                                                            (poll.votes
                                                                ? poll.votes
                                                                      .length
                                                                : 0)}
                                                    </span>
                                                </div>
                                                {poll.expires_at && (
                                                    <div className="flex justify-between text-gray-500 dark:text-gray-400">
                                                        <span>Expires:</span>
                                                        <span>
                                                            {format(
                                                                new Date(
                                                                    poll.expires_at,
                                                                ),
                                                                'MMM d, yyyy',
                                                            )}
                                                        </span>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="mt-4">
                                                <span className="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900">
                                                    Cast Your Vote
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>

                        {/* Pagination (cursor-based) */}
                        {(polls.prev_page_url || polls.next_page_url) && (
                            <div className="mt-4 flex justify-between">
                                {polls.prev_page_url ? (
                                    <Link
                                        href={polls.prev_page_url}
                                        className="rounded-md bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                                    >
                                        Previous
                                    </Link>
                                ) : (
                                    <div></div>
                                )}
                                {polls.next_page_url ? (
                                    <Link
                                        href={polls.next_page_url}
                                        className="rounded-md bg-indigo-600 px-4 py-2 text-white transition-colors hover:bg-indigo-700"
                                    >
                                        Next
                                    </Link>
                                ) : (
                                    <div></div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
