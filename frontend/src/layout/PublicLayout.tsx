import React from 'react';
import { Outlet, useParams } from 'react-router-dom';

// Layout para a Loja Pública
const PublicLayout: React.FC = () => {
  const { storeSlug } = useParams();

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white shadow-sm sticky top-0 z-30">
        <div className="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
           <h1 className="text-xl font-bold text-purple-600 capitalize">{storeSlug?.replace('-', ' ')}</h1>
           {/* Carrinho Widget poderia vir aqui */}
        </div>
      </header>
      <main className="max-w-4xl mx-auto px-4 py-6 pb-20">
        <Outlet />
      </main>
      {/* Footer mobile com navegação se necessário */}
    </div>
  );
};

export default PublicLayout;