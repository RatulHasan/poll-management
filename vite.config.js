import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
    ],
    define: {
        // Make env variables available to client-side code
        'process.env': {
            VITE_PUSHER_APP_KEY: process.env.VITE_PUSHER_APP_KEY,
            VITE_PUSHER_HOST: process.env.VITE_PUSHER_HOST,
            VITE_PUSHER_PORT: process.env.VITE_PUSHER_PORT,
            VITE_PUSHER_SCHEME: process.env.VITE_PUSHER_SCHEME,
            VITE_PUSHER_APP_CLUSTER: process.env.VITE_PUSHER_APP_CLUSTER,
        },
    },
});
