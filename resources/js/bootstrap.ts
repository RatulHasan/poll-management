import axios from 'axios';
import Echo, { Broadcaster } from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Echo: Echo<keyof Broadcaster>;
        Pusher: typeof Pusher;
        axios: typeof axios;
    }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
