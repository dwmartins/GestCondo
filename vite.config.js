import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers';

export default defineConfig({
    plugins: [
        vue(),
        AutoImport({
            resolvers: [ElementPlusResolver()],
        }),
        Components({
            resolvers: [ElementPlusResolver()],
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        })
    ],
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: '',
            },
        },
    },
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
