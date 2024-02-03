import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Exclude the public/games directory from being watched
        watch: {
            exclude: ['storage'],
        },
    },
    server: {
        // Exclude the public/games directory from being watched
        watch: {
            exclude: ['storage'],
        },
    },
});
