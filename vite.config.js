import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
// import { VitePWA } from 'vite-plugin-pwa';
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Temporarily disabled PWA plugin
        /* Disabled PWA plugin to fix build issues
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'robots.txt', 'img/*'],
            manifest: {
                name: 'Church Management System',
                short_name: 'ChCMS',
                description: 'A modern church management system',
                theme_color: '#0ea5e9',
                background_color: '#ffffff',
                display: 'standalone',
                icons: [
                    {
                        src: 'img/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: 'img/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: 'img/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                runtimeCaching: [
                    // Cache configurations removed for brevity
                ],
            },
        }),
        */
        process.env.ANALYZE === 'true' && visualizer({
            open: true,
            gzipSize: true,
            brotliSize: true,
            filename: 'dist/stats.html',
        }),
    ].filter(Boolean),
    
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        // Enable chunk splitting for better caching
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Vendor chunks
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('vue-router') || id.includes('vuex')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('axios') || id.includes('vue-toastification')) {
                            return 'vendor-utils';
                        }
                        return 'vendor-other';
                    }
                    
                    // Feature-based chunks
                    if (id.includes('/resources/js/pages/Dashboard')) {
                        return 'feature-dashboard';
                    }
                    if (id.includes('/resources/js/pages/Members') || id.includes('/resources/js/pages/Families')) {
                        return 'feature-members';
                    }
                    if (id.includes('/resources/js/pages/Events') || id.includes('/resources/js/pages/Attendance')) {
                        return 'feature-events';
                    }
                    if (id.includes('/resources/js/pages/Finance') || 
                        id.includes('/resources/js/pages/Donations') || 
                        id.includes('/resources/js/pages/Expenses')) {
                        return 'feature-finances';
                    }
                    if (id.includes('/resources/js/pages/Reports') || id.includes('/resources/js/pages/Analytics')) {
                        return 'feature-reports';
                    }
                    
                    // UI components chunk
                    if (id.includes('/resources/js/components/ui/')) {
                        return 'ui-components';
                    }
                },
                // Optimize chunk naming
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/\.(css|scss|sass)$/.test(assetInfo.name)) {
                        return `css/[name]-[hash][extname]`;
                    }
                    if (/\.(png|jpe?g|gif|svg|webp|ico)$/.test(assetInfo.name)) {
                        return `img/[name]-[hash][extname]`;
                    }
                    if (/\.(woff2?|eot|ttf|otf)$/.test(assetInfo.name)) {
                        return `fonts/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
            },
        },
        // Enable source maps for production
        sourcemap: process.env.NODE_ENV === 'development',
        // Minify options
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: process.env.NODE_ENV === 'production',
                drop_debugger: process.env.NODE_ENV === 'production',
            },
        },
    },
    // Optimize dev server performance
    server: {
        hmr: {
            overlay: true,
        },
    },
});
