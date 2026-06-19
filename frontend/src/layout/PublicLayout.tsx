import React, { useEffect, useState } from 'react';
import { Outlet, useParams, useLocation } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import api from '../services/api';
import Footer from '../components/common/Footer';
import { CartProvider } from '../context/CartContext';
import Header from '../components/common/Header';
import PromotionalBanner from '../components/common/PromotionalBanner';
import RotatingBanners from '../components/common/RotatingBanners';
import InstallPwaPrompt from '../components/common/InstallPwaPrompt';
import { formatWhatsAppNumber } from '../utils/whatsapp';

interface StoreInfo {
  name: string;
  whatsapp_number: string;
  whatsapp_message?: string;
  logo_url?: string | null;
  email_contact?: string;
  address?: string;
  primary_color?: string;
  secondary_color?: string;
  banner_message?: string;
  banner_text_color_1?: string;
  banner_text_color_2?: string;
  banner_background_color?: string;
  socials?: {
    name: string;
    url: string;
    icon?: string;
  }[];
}

// Wrapper component to provide context and layout
const PublicLayout: React.FC = () => {
  const { storeSlug } = useParams();
  const location = useLocation();
  const [storeInfo, setStoreInfo] = useState<StoreInfo | null>(null);
  
  // Verificar se estamos na página inicial (apenas /storeSlug, sem subcaminhos)
  const isHomePage = location.pathname === `/${storeSlug}` || location.pathname === `/${storeSlug}/`;

  useEffect(() => {
    if (storeSlug) {
        api.get(`/${storeSlug}`)
           .then(response => {
               console.log('PublicLayout: Store info loaded:', response.data);
               console.log('PublicLayout: Logo URL from API:', response.data?.logo_url);
               setStoreInfo(response.data);
               
                const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
                const rawLogoUrl = response.data?.logo_url;
                const logoUrl = rawLogoUrl ? rawLogoUrl.replace(/^http:\/\//i, 'https://') : null;
                const fallbackIcon = `${apiBaseUrl}/${storeSlug}/icon.png`;
                const storeIcon = logoUrl || fallbackIcon;

                // Atualiza os elementos de favicon — ambos precisam ser trocados
                const faviconSvg = document.getElementById('favicon-svg') as HTMLLinkElement | null;
                if (faviconSvg) {
                    faviconSvg.type = 'image/png';
                    faviconSvg.href = storeIcon;
                }

                const faviconIco = document.getElementById('favicon-ico') as HTMLLinkElement | null;
                if (faviconIco) {
                    faviconIco.type = 'image/png';
                    faviconIco.href = storeIcon;
                }

                // Apple touch icon
                const appleTouch = document.getElementById('apple-touch-icon') as HTMLLinkElement;
                if (appleTouch) {
                    appleTouch.href = logoUrl ? storeIcon : `${fallbackIcon}?size=180`;
                }

                // Manifest dinâmico por loja
                const manifestLink = document.querySelector("link[rel='manifest']") as HTMLLinkElement;
                if (manifestLink) {
                    manifestLink.href = `${apiBaseUrl}/${storeSlug}/manifest.json`;
                }
               
               // Service Worker será registrado automaticamente pelo vite-plugin-pwa
               // Mas podemos verificar se está ativo
               if ('serviceWorker' in navigator) {
                   navigator.serviceWorker.ready.then((registration) => {
                       console.log('Service Worker ready:', registration);
                   });
               }
            })
            .catch(err => console.error("Error fetching store info", err));

        // Load tracking scripts for this store
        const cleanScripts = () => {
          document.querySelectorAll('script[data-tracking]').forEach(el => el.remove());
        };

        api.get(`/${storeSlug}/trackings`)
          .then(response => {
            cleanScripts();
            response.data.forEach((t: { provider: string; tracking_code: string }) => {
              const script = document.createElement('script');
              script.setAttribute('data-tracking', t.provider);
              if (t.provider === 'google_analytics') {
                script.src = `https://www.googletagmanager.com/gtag/js?id=${t.tracking_code}`;
                script.async = true;
                document.head.appendChild(script);
                const gtagScript = document.createElement('script');
                gtagScript.setAttribute('data-tracking', 'google_analytics');
                gtagScript.text = `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${t.tracking_code}');`;
                document.head.appendChild(gtagScript);
              } else if (t.provider === 'facebook_pixel') {
                script.text = `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','${t.tracking_code}');fbq('track','PageView');`;
                document.head.appendChild(script);
              }
            });
          })
          .catch(() => {}); // tracking is optional
     }
   }, [storeSlug]);

  // Colors for dynamic theming
  const primaryColor = storeInfo?.primary_color || '#7c3aed';
  const secondaryColor = storeInfo?.secondary_color || '#f3e8ff';

  return (
    <CartProvider storeSlug={storeSlug}>
        {storeInfo && (
          <Helmet>
            <meta name="theme-color" content={primaryColor} />
            <meta name="apple-mobile-web-app-title" content={storeInfo.name} />
          </Helmet>
        )}
        <div
            className="min-h-screen bg-gray-50 flex flex-col"
            style={{
                '--theme-primary': primaryColor,
                '--theme-secondary': secondaryColor
            } as React.CSSProperties}
        >
        <Header
          storeName={storeInfo?.name} 
          storeSlug={storeSlug} 
          primaryColor={primaryColor}
          logoUrl={storeInfo?.logo_url}
        />
        
        {/* Banner Promocional - Apenas na página inicial */}
        {storeInfo?.banner_message && isHomePage && (
            <PromotionalBanner
                message={storeInfo.banner_message}
                textColor1={storeInfo.banner_text_color_1 || '#ffffff'}
                textColor2={storeInfo.banner_text_color_2 || '#ffff00'}
                backgroundColor={storeInfo.banner_background_color || '#000000'}
            />
        )}

        {/* Banners Rotativos - Apenas na página inicial */}
        {isHomePage && (
          <div className="w-full max-w-6xl mx-auto px-4 pt-6">
              <RotatingBanners />
          </div>
        )}
        
        <main className="flex-grow max-w-6xl w-full mx-auto px-4 py-6">
            <Outlet context={{ storeInfo }} />
        </main>

        <Footer storeInfo={storeInfo} />
        
        {/* Floating WhatsApp Button */}
        {storeInfo?.whatsapp_number && (
            <a 
            href={`https://wa.me/${formatWhatsAppNumber(storeInfo.whatsapp_number)}${storeInfo.whatsapp_message ? '?text=' + encodeURIComponent(storeInfo.whatsapp_message) : ''}`}
            target="_blank"
            rel="noopener noreferrer"
            className="fixed bottom-20 right-4 z-40 text-white p-3 rounded-full shadow-lg hover:opacity-90 transition-all hover:scale-110"
            style={{ backgroundColor: '#25D366' }} 
            aria-label="Chat on WhatsApp"
            >
            <svg viewBox="0 0 24 24" fill="currentColor" className="w-8 h-8"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </a>
        )}

        <InstallPwaPrompt primaryColor={primaryColor} />
        </div>
    </CartProvider>
  );
};

export default PublicLayout;