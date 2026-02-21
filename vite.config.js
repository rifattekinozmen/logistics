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
    optimizeDeps: {
        include: ['bootstrap'],
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules/bootstrap')) {
                        return 'bootstrap';
                    }
                },
            },
        },
    },
    server: {
        host: '127.0.0.1',
        hmr: {
            host: 'localhost',
        },
    },
});
