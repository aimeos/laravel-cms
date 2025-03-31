import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vuetify()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    rollupOptions: {
      output: {
        entryFileNames: '[name].js',
        assetFileNames: (asset) => {
          return asset.names.includes('index.css') ? 'index.css' : 'assets/[name]-[hash][extname]'
        }
      }
    }
  },
  experimental: {
    renderBuiltUrl: (filename, { type, hostType }) => {
      if(type === 'asset' && ['css', 'html'].includes(hostType) === false) {
        return { runtime: `new URL(${JSON.stringify(filename)}, import.meta.url).href` }
      } else {
        return { relative: true }
      }
    },
  },
})
