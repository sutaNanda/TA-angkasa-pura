import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: true,
        port: 5173,
        hmr: {
            host: 'cfaf-103-19-231-203.ngrok-free.app',
            protocol: 'wss',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
