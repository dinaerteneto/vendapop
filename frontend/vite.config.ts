import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from 'vite-plugin-pwa'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const gaMeasurementId = env.VITE_GA_MEASUREMENT_ID || process.env.VITE_GA_MEASUREMENT_ID || ''

  const gaScript = gaMeasurementId
    ? `<script async src="https://www.googletagmanager.com/gtag/js?id=${gaMeasurementId}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      if (!window.location.pathname.startsWith('/admin')) {
        gtag('js', new Date());
        gtag('config', '${gaMeasurementId}');
      }
    </script>`
    : ''

  return {
  plugins: [
    {
      name: 'inject-ga',
      transformIndexHtml(html) {
        if (!gaScript) return html
        return html.replace('</head>', `${gaScript}\n  </head>`)
      },
    },
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
  }
  }
})
