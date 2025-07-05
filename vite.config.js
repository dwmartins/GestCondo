import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        })
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@assets': path.resolve(__dirname, 'resources/assets'),
            '@': path.resolve(__dirname, 'resources/js'),
            '@views': path.resolve(__dirname, 'resources/js/views'),
            '@layouts': path.resolve(__dirname, 'resources/js/layouts'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        assetsDir: '.',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                assetFileNames: '[name][extname]',
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
            },
        },
    },
});
