import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  css: {
    postcss: './frontend/postcss.config.js'
  },
  root: './frontend',
  build: {
    outDir: '../dist',
    emptyOutDir: true
  },
  server: {
    port: 3000,
    host: true,
    hmr: true,  // Réactiver le hot module reload
    watch: {
      ignored: [
        '**/backend/**',
        '**/public/**',
        '**/vendor/**',
        '**/node_modules/**',
        '**/.git/**',
        '**/tests/**',
        '**/*.php',
        '**/composer.json',
        '**/composer.lock',
        '**/package-lock.json'
      ]
    }
  },
  esbuild: {
    loader: 'jsx',
    include: /src\/.*\.[jt]sx?$/,
    exclude: []
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './frontend/src'),
      '@/components': path.resolve(__dirname, './frontend/src/components'),
      '@/lib': path.resolve(__dirname, './frontend/src/lib'),
      '@/utils': path.resolve(__dirname, './frontend/src/lib/utils')
    }
  }
})