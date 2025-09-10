import { Notification, VoteStatistics } from '@/types';
import { useEffect, useRef, useState } from 'react';

interface UsePollUpdatesOptions {
    onVoteCast?: (statistics: VoteStatistics) => void;
}

interface UseUserNotificationsOptions {
    onNotification?: (notification: Notification) => void;
}

export function usePollUpdates(
    pollId: number,
    options?: UsePollUpdatesOptions,
) {
    const [isConnected, setIsConnected] = useState(false);
    // Keep the latest callback without causing the effect to re-subscribe
    const onVoteCastRef = useRef<
        ((statistics: VoteStatistics) => void) | undefined
    >(options?.onVoteCast);

    useEffect(() => {
        onVoteCastRef.current = options?.onVoteCast;
    }, [options?.onVoteCast]);

    useEffect(() => {
        if (!window.Echo) {
            setIsConnected(false);
            return;
        }

        const channelName = `poll.${pollId}`;
        const channel = window.Echo.channel(channelName);

        // Ensure we don't have a stale listener lingering
        channel.stopListening('.vote.cast');

        const handler = (event: { statistics?: VoteStatistics }) => {
            console.log('Vote cast event received:', event);
            const cb = onVoteCastRef.current;
            if (event.statistics && cb) {
                cb(event.statistics);
            }
        };

        channel.listen('.vote.cast', handler);
        setIsConnected(true);

        return () => {
            channel.stopListening('.vote.cast');
            window.Echo.leave(channelName);
            setIsConnected(false);
        };
    }, [pollId]);

    return { isConnected };
}

export function useUserNotifications(
    userId?: number,
    options?: UseUserNotificationsOptions,
) {
    const [isConnected, setIsConnected] = useState(false);
    const cbRef = useRef<((n: Notification) => void) | undefined>(
        options?.onNotification,
    );
    useEffect(() => {
        cbRef.current = options?.onNotification;
    }, [options?.onNotification]);

    useEffect(() => {
        if (!window.Echo || !userId) {
            setIsConnected(false);
            return;
        }

        const channelName = `App.Models.User.${userId}`;
        const channel = window.Echo.private(channelName);

        channel.notification((notification: Notification) => {
            const cb = cbRef.current;
            if (cb) cb(notification);
        });

        setIsConnected(true);

        return () => {
            window.Echo.leave(channelName);
            setIsConnected(false);
        };
    }, [userId]);

    return { isConnected };
}
