import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/calendar.css',
                'resources/js/app.js',
                'resources/js/calendar.js',
                'resources/js/analytics-charts.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        include: [
            'bootstrap',
            '@fullcalendar/core',
            '@fullcalendar/daygrid',
            '@fullcalendar/timegrid',
            '@fullcalendar/list',
            '@fullcalendar/interaction'
        ],
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules/bootstrap')) {
                        return 'bootstrap';
                    }
                    if (id.includes('node_modules/@fullcalendar')) {
                        return 'fullcalendar';
                    }
                },
            },
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: false,
        hmr: {
            host: 'localhost',
        },
    },
});
