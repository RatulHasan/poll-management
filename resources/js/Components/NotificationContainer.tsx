import { useUserNotifications } from '@/hooks/usePollUpdates';
import { Notification } from '@/types/index';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

interface User {
    id: number;
    name: string;
    email: string;
}

export default function NotificationContainer() {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const auth = usePage().props.auth as { user: User | null };

    useUserNotifications(auth.user?.id, {
        onNotification: (notification: Notification) => {
            const newNotification = {
                id: notification.id || Date.now().toString(),
                message: notification.message,
                type: notification.type || 'info',
            };

            setNotifications((prev) => [...prev, newNotification]);

            // Remove notification after 5 seconds
            setTimeout(() => {
                setNotifications((prev) =>
                    prev.filter((n) => n.id !== newNotification.id),
                );
            }, 5000);
        },
    });

    if (notifications.length === 0) return null;

    return (
        <div className="fixed bottom-4 right-4 z-50 space-y-2">
            {notifications.map((notification, index) => (
                <div
                    key={notification.id + index}
                    className={`transform rounded-lg p-4 shadow-lg transition-all duration-300 ease-in-out ${
                        notification.type === 'success'
                            ? 'bg-green-500'
                            : notification.type === 'error'
                              ? 'bg-red-500'
                              : 'bg-blue-500'
                    } text-white`}
                >
                    {notification.message}
                </div>
            ))}
        </div>
    );
}
