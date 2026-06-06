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
        // Hapus host: true karena di Windows hal ini akan membuat Vite 
        // menyuntikkan URL http://0.0.0.0 yang tidak bisa dibaca browser.
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
