import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Poll } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Props {
    poll: Poll;
}

export default function Edit({ poll }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        title: poll.title,
        description: poll.description || '',
        is_active: poll.is_active,
        expires_at: poll.expires_at?.slice(0, 16) || '', // Format for datetime-local input
        options: poll.options.map((option) => option.text),
    });

    const addOption = () => {
        setData('options', [...data.options, '']);
    };

    const removeOption = (index: number) => {
        if (data.options.length > 2) {
            const newOptions = [...data.options];
            newOptions.splice(index, 1);
            setData('options', newOptions);
        }
    };

    const updateOption = (index: number, value: string) => {
        const newOptions = [...data.options];
        newOptions[index] = value;
        setData('options', newOptions);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('admin.polls.update', poll.id));
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Edit Poll: ${poll.title}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6">
                            <h2 className="mb-6 text-xl font-semibold text-gray-800 dark:text-gray-200">
                                Edit Poll: {poll.title}
                            </h2>

                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <InputLabel
                                        htmlFor="title"
                                        value="Poll Title"
                                    />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        name="title"
                                        value={data.title}
                                        className="mt-1 block w-full"
                                        onChange={(e) =>
                                            setData('title', e.target.value)
                                        }
                                    />
                                    <InputError
                                        message={errors.title}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="description"
                                        value="Description (Optional)"
                                    />
                                    <textarea
                                        id="description"
                                        name="description"
                                        value={data.description}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                                        rows={3}
                                        onChange={(e) =>
                                            setData(
                                                'description',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={errors.description}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel value="Poll Options" />
                                    <div className="space-y-2">
                                        {data.options.map((option, index) => (
                                            <div
                                                key={index}
                                                className="flex gap-2"
                                            >
                                                <TextInput
                                                    type="text"
                                                    value={option}
                                                    className="mt-1 block w-full"
                                                    placeholder={`Option ${index + 1}`}
                                                    onChange={(e) =>
                                                        updateOption(
                                                            index,
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                {data.options.length > 2 && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removeOption(index)
                                                        }
                                                        className="mt-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        Remove
                                                    </button>
                                                )}
                                            </div>
                                        ))}
                                        <InputError
                                            message={errors.options}
                                            className="mt-2"
                                        />
                                    </div>
                                    <SecondaryButton
                                        type="button"
                                        onClick={addOption}
                                        className="mt-2"
                                    >
                                        Add Option
                                    </SecondaryButton>
                                </div>

                                <div className="flex items-center gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="expires_at"
                                            value="Expiration Date (Optional)"
                                        />
                                        <TextInput
                                            id="expires_at"
                                            type="datetime-local"
                                            name="expires_at"
                                            value={data.expires_at}
                                            className="mt-1 block w-full"
                                            onChange={(e) =>
                                                setData(
                                                    'expires_at',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.expires_at}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={data.is_active}
                                            onChange={(e) =>
                                                setData(
                                                    'is_active',
                                                    e.target.checked,
                                                )
                                            }
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600"
                                        />
                                        <span className="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                            Active
                                        </span>
                                    </label>
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>
                                        Update Poll
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
