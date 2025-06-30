import { fileURLToPath, URL } from 'node:url'
import path from 'node:path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  base: '/Norciv_Base/',
  plugins: [vue(), vueJsx(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  build: {
    target: 'esnext',
    outDir: path.resolve(__dirname, 'C:/xampp/htdocs/Norciv_Base'),
    //outDir: 'dist',
    emptyOutDir: false,
    cssCodeSplit: true,
    assetsInlineLimit: 0,
    rollupOptions: {
      // input: {
      //   main: path.resolve(__dirname, 'index.html'), // key entry
      //   // add other HTML or JS entry points if needed
      // },
      output: {
        inlineDynamicImports: true,
        entryFileNames: 'static/[name].js',
        chunkFileNames: 'static/[name].js',
        assetFileNames: 'static/[name].[ext]',
      },
    },
  },
})
