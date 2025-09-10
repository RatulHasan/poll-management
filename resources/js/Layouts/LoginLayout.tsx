import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { useTheme } from '@/hooks/useTheme';

export default function LoginLayout({ children }: PropsWithChildren) {
    const { isDark, toggle } = useTheme();
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
            <div>
                <Link href="/">
                    <ApplicationLogo className="h-20 w-20 fill-current text-gray-500" />
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800">
                {children}
            </div>
            <button
                onClick={toggle}
                title="Toggle Dark Mode"
                className="fixed bottom-4 right-4 rounded-full bg-slate-600 p-4 text-white shadow-lg hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            >
                {isDark ? (
                    <div className="h-5 w-5 text-yellow-400">&#9728;</div>
                ) : (
                    <div className="h-5 w-5 text-gray-200">&#9790;</div>
                )}
            </button>
        </div>
    );
}
