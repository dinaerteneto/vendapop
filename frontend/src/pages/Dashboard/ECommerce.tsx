import React from 'react';

const ECommerce: React.FC = () => {
  return (
    <div>
      <h2 className="mb-4 text-2xl font-bold text-black">Dashboard</h2>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div className="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default">
          <h4 className="text-title-md font-bold text-black">R$ 0,00</h4>
          <span className="text-sm text-gray-500">Vendas hoje</span>
        </div>
        <div className="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default">
            <h4 className="text-title-md font-bold text-black">0</h4>
            <span className="text-sm text-gray-500">Pedidos Novos</span>
        </div>
        <div className="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default">
            <h4 className="text-title-md font-bold text-black">0</h4>
            <span className="text-sm text-gray-500">Produtos Ativos</span>
        </div>
      </div>
    </div>
  );
};

export default ECommerce;
