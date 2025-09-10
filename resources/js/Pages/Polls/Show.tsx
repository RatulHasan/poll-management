import InputError from '@/Components/InputError';
import PollResults from '@/Components/PollResults';
import GuestLayout from '@/Layouts/GuestLayout';
import { Poll, User, VoteStatistics } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { FormEventHandler } from 'react';

interface Props {
    auth: { user: User };
    poll: Poll;
    hasVoted: boolean;
    userVote?: {
        id: number;
        poll_option_id: number;
        option: {
            text: string;
        };
    } | null;
    statistics: VoteStatistics;
}

export default function Show({ poll, hasVoted, userVote, statistics }: Props) {
    // Real-time updates are handled by PollResults via usePollUpdates to avoid duplicate listeners on this page.

    const { data, setData, post, processing, errors, reset } = useForm({
        option_id: '',
        vote: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('polls.vote', poll.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset('option_id');
            },
        });
    };

    const getExpiryMessage = () => {
        if (!poll.expires_at) return null;
        const expiryDate = new Date(poll.expires_at);
        const now = new Date();

        if (expiryDate < now) {
            return 'This poll has expired';
        }

        return `Expires on ${format(expiryDate, 'MMM d, yyyy')}`;
    };

    return (
        <GuestLayout>
            <Head title={poll.title} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6">
                            <h1 className="mb-2 text-2xl font-bold text-gray-800 dark:text-gray-200">
                                {poll.title}
                            </h1>

                            {poll.description && (
                                <p className="mb-6 text-gray-600 dark:text-gray-400">
                                    {poll.description}
                                </p>
                            )}

                            {getExpiryMessage() && (
                                <div className="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    {getExpiryMessage()}
                                </div>
                            )}

                            {!hasVoted ? (
                                <form onSubmit={submit} className="space-y-4">
                                    <div className="space-y-2">
                                        {poll.options.map((option) => (
                                            <label
                                                key={option.id}
                                                className="flex cursor-pointer items-center space-x-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                                            >
                                                <input
                                                    type="radio"
                                                    name="option_id"
                                                    value={option.id}
                                                    checked={
                                                        data.option_id ===
                                                        option.id.toString()
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'option_id',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="form-radio h-5 w-5 text-blue-600 dark:text-blue-400"
                                                />
                                                <span className="text-gray-700 dark:text-gray-300">
                                                    {option.text}
                                                </span>
                                            </label>
                                        ))}
                                    </div>

                                    <InputError
                                        message={errors.option_id}
                                        className="mt-2"
                                    />
                                    <InputError
                                        message={errors.vote}
                                        className="mt-2"
                                    />

                                    <div className="flex items-center justify-between">
                                        <button
                                            type="submit"
                                            disabled={
                                                processing || !data.option_id
                                            }
                                            className="rounded-md bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                        >
                                            {processing
                                                ? 'Submitting...'
                                                : 'Submit Vote'}
                                        </button>
                                    </div>
                                </form>
                            ) : (
                                <div className="mb-6">
                                    {userVote && (
                                        <div className="mb-4 rounded-lg bg-blue-50 p-4 dark:bg-blue-900">
                                            <p className="text-sm text-blue-800 dark:text-blue-200">
                                                You voted for:{' '}
                                                <strong>
                                                    {userVote.option.text}
                                                </strong>
                                            </p>
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className="mt-8">
                                <h2 className="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    Results
                                </h2>
                                <PollResults
                                    pollId={poll.id}
                                    initialStatistics={statistics}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
