import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Poll } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';

interface Props {
    auth: { user: unknown };
    polls: {
        data: Poll[];
        next_page_url?: string | null;
        prev_page_url?: string | null;
        per_page?: number;
        path?: string;
    };
}

export default function Index({ polls }: Props) {
    return (
        <AuthenticatedLayout>
            <Head title="Manage Polls" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    Manage Polls
                                </h2>
                                <Link
                                    href={route('admin.polls.create')}
                                    className="rounded-md bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                                >
                                    Create New Poll
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead className="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Title
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Votes
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Expires
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        {polls.data.map((poll) => (
                                            <tr key={poll.id}>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {poll.title}
                                                    </div>
                                                    <div className="text-sm text-gray-500 dark:text-gray-400">
                                                        {poll.options.length}{' '}
                                                        options
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
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
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    {poll.votes_count ??
                                                        (poll.votes
                                                            ? poll.votes.length
                                                            : 0)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    {poll.expires_at
                                                        ? format(
                                                              new Date(
                                                                  poll.expires_at,
                                                              ),
                                                              'MMM d, yyyy',
                                                          )
                                                        : 'Never'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                                    <Link
                                                        href={route(
                                                            'admin.polls.show',
                                                            poll.id,
                                                        )}
                                                        className="mr-4 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                    >
                                                        View
                                                    </Link>
                                                    <Link
                                                        href={route(
                                                            'admin.polls.edit',
                                                            poll.id,
                                                        )}
                                                        className="mr-4 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <Link
                                                        href={route(
                                                            'admin.polls.destroy',
                                                            poll.id,
                                                        )}
                                                        method="delete"
                                                        as="button"
                                                        className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        Delete
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

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
                                            className="rounded-md bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
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
            </div>
        </AuthenticatedLayout>
    );
}
