/// <reference types="vite/client" />
/// <reference types="vite-plugin-pwa/client" />

interface Window {
  gtag: (event: string, action: string, payload?: Record<string, unknown>) => void;
  dataLayer: unknown[];
}
