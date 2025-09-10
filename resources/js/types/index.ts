export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
}

export interface PollOption {
    id: number;
    poll_id: number;
    text: string;
    created_at: string;
    updated_at: string;
}

export interface Poll {
    id: number;
    user_id: number;
    title: string;
    description: string | null;
    is_active: boolean;
    expires_at: string | null;
    end_date: string | null;
    created_at: string;
    updated_at: string;
    options: PollOption[];
    votes_count?: number;
    votes?: Array<{
        id: number;
        poll_id: number;
        poll_option_id: number;
        user_id: number | null;
        created_at: string;
        updated_at: string;
    }>;
}

export interface VoteStatistics {
    total_votes: number;
    options: Array<{
        id: number;
        text: string;
        votes: number;
        percentage: number;
    }>;
}

export type PageProps<T = Record<string, unknown>> = {
    auth: {
        user: User;
    };
    errors: Record<string, string>;
} & T;

export interface Notification {
    id: string;
    message: string;
    type: 'success' | 'error' | 'info';
    status?: 'success' | 'error' | 'info';
    poll_id?: number;
    poll_title?: string;
}
