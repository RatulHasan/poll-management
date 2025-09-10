import NotificationContainer from '@/Components/NotificationContainer';
import { useTheme } from '@/hooks/useTheme';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    const { isDark, toggle } = useTheme();
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
            {children}
            <NotificationContainer />
            {/*Create a floating button at bottom right to toggle dark mode*/}
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
