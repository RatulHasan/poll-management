import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Poll, VoteStatistics } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { useCallback, useState } from 'react';

interface Props {
    poll: Poll;
    statistics: VoteStatistics;
}

export default function Show({ poll, statistics }: Props) {
    const [copied, setCopied] = useState(false);
    const handleCopyLink = useCallback(() => {
        const link = `${window.location.origin}/polls/${poll.id}`;
        navigator.clipboard.writeText(link).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    }, [poll.id]);
    return (
        <AuthenticatedLayout>
            <Head title={`Poll: ${poll.title}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6">
                            <div className="mb-6 grid grid-cols-12 items-start justify-between">
                                <div className="col-span-9">
                                    <h2 className="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                        {poll.title}
                                    </h2>
                                    {poll.description && (
                                        <p className="mt-2 text-gray-600 dark:text-gray-400">
                                            {poll.description}
                                        </p>
                                    )}
                                </div>
                                <div className="col-span-3 flex justify-end gap-2">
                                    <Link
                                        href={route(
                                            'admin.polls.edit',
                                            poll.id,
                                        )}
                                        className="rounded-md bg-indigo-600 px-4 py-2 text-white transition-colors hover:bg-indigo-700"
                                    >
                                        Edit Poll
                                    </Link>
                                    <Link
                                        href={route(
                                            'admin.polls.toggle-status',
                                            poll.id,
                                        )}
                                        method="patch"
                                        as="button"
                                        className={`px-4 py-2 ${
                                            poll.is_active
                                                ? 'bg-red-600 hover:bg-red-700'
                                                : 'bg-green-600 hover:bg-green-700'
                                        } rounded-md text-white transition-colors`}
                                    >
                                        {poll.is_active
                                            ? 'Deactivate'
                                            : 'Activate'}
                                    </Link>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {/* Poll Information */}
                                <div className="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <h3 className="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        Poll Information
                                    </h3>
                                    <dl className="space-y-2">
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600 dark:text-gray-400">
                                                Status:
                                            </dt>
                                            <dd>
                                                <span
                                                    className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${
                                                        poll.is_active
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                    }`}
                                                >
                                                    {poll.is_active
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </span>
                                            </dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600 dark:text-gray-400">
                                                Created:
                                            </dt>
                                            <dd className="text-gray-900 dark:text-gray-100">
                                                {format(
                                                    new Date(poll.created_at),
                                                    'MMM d, yyyy',
                                                )}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600 dark:text-gray-400">
                                                Expires:
                                            </dt>
                                            <dd className="text-gray-900 dark:text-gray-100">
                                                {poll.expires_at
                                                    ? format(
                                                          new Date(
                                                              poll.expires_at,
                                                          ),
                                                          'MMM d, yyyy',
                                                      )
                                                    : 'Never'}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600 dark:text-gray-400">
                                                Multiple Votes:
                                            </dt>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt className="text-gray-600 dark:text-gray-400">
                                                Total Votes:
                                            </dt>
                                            <dd className="text-gray-900 dark:text-gray-100">
                                                {statistics.total_votes}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                {/* Results Visualization */}
                                <div className="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <h3 className="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        Results
                                    </h3>
                                    <div className="space-y-4">
                                        {statistics.options.map((option) => (
                                            <div
                                                key={option.id}
                                                className="space-y-2"
                                            >
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-700 dark:text-gray-300">
                                                        {option.text}
                                                    </span>
                                                    <span className="text-gray-600 dark:text-gray-400">
                                                        {option.votes} votes (
                                                        {option.percentage}%)
                                                    </span>
                                                </div>
                                                <div className="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                                    <div
                                                        className="h-2.5 rounded-full bg-blue-600"
                                                        style={{
                                                            width: `${option.percentage}%`,
                                                        }}
                                                    ></div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Share Section */}
                            <div className="mt-6">
                                <h3 className="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    Share Poll
                                </h3>
                                <div className="flex items-center gap-4">
                                    <input
                                        type="text"
                                        readOnly
                                        value={`${window.location.origin}/polls/${poll.id}`}
                                        className="flex-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    />
                                    <SecondaryButton onClick={handleCopyLink}>
                                        {copied ? 'Copied!' : 'Copy Link'}
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
