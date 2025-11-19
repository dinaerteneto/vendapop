import React, { useEffect, useState } from 'react';
import { Outlet, useParams, Link } from 'react-router-dom';
import api from '../services/api';
import Footer from '../components/common/Footer';

interface StoreInfo {
  name: string;
  whatsapp_number: string;
  email_contact?: string;
  address?: string;
  socials?: {
    name: string;
    url: string;
    icon?: string;
  }[];
}

// Layout para a Loja Pública
const PublicLayout: React.FC = () => {
  const { storeSlug } = useParams();
  const [storeInfo, setStoreInfo] = useState<StoreInfo | null>(null);

  useEffect(() => {
    if (storeSlug) {
        api.get(`/${storeSlug}`)
           .then(response => setStoreInfo(response.data))
           .catch(err => console.error("Error fetching store info", err));
    }
  }, [storeSlug]);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <header className="bg-white shadow-sm sticky top-0 z-30">
        <div className="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
           <Link to={`/${storeSlug}`} className="hover:opacity-80 transition-opacity">
               <h1 className="text-xl font-bold text-purple-600 capitalize">{storeInfo?.name || storeSlug?.replace('-', ' ')}</h1>
           </Link>
           {/* Carrinho Widget poderia vir aqui */}
        </div>
      </header>
      
      <main className="flex-grow max-w-6xl w-full mx-auto px-4 py-6">
        <Outlet />
      </main>

      <Footer storeInfo={storeInfo} />
      
      {/* Floating WhatsApp Button */}
      {storeInfo?.whatsapp_number && (
        <a 
          href={`https://wa.me/${storeInfo.whatsapp_number.replace(/[^0-9]/g, '')}`}
          target="_blank"
          rel="noopener noreferrer"
          className="fixed bottom-20 right-4 z-40 bg-green-500 text-white p-3 rounded-full shadow-lg hover:bg-green-600 transition-all hover:scale-110"
          aria-label="Chat on WhatsApp"
        >
           <svg viewBox="0 0 24 24" fill="currentColor" className="w-8 h-8"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </a>
      )}
    </div>
  );
};

export default PublicLayout;