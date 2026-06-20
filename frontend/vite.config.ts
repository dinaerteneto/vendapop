import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from 'vite-plugin-pwa'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: 'autoUpdate',
      // Manifest será gerado dinamicamente pelo backend por tenant
      manifest: false,
      // Service worker customizado para suportar push notifications
      strategies: 'injectManifest',
      srcDir: 'src',
      filename: 'sw.js',
      injectManifest: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}']
      },
      workbox: {
        // Configurações para cache
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
        // Não cachear requisições de API
        runtimeCaching: [
          {
            urlPattern: ({ url }) => {
              // Não cachear API ou manifest
              return !url.pathname.startsWith('/api/') && 
                     !url.pathname.includes('manifest.json');
            },
            handler: 'NetworkFirst',
            options: {
              cacheName: 'app-cache',
              expiration: {
                maxEntries: 50,
                maxAgeSeconds: 60 * 60 * 24 // 24 hours
              },
              networkTimeoutSeconds: 10
            }
          }
        ]
      }
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    allowedHosts: true,
  }
})
