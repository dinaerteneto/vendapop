import React from 'react';

interface ShopPreviewProps {
  tenantSlug: string;
  refreshKey: number;
}

const ShopPreview: React.FC<ShopPreviewProps> = ({ tenantSlug, refreshKey }) => {
  const shopUrl = `${import.meta.env.VITE_APP_URL || 'http://localhost:8000'}/${tenantSlug}?t=${refreshKey}`;

  return (
    <div className="flex flex-col items-center gap-3">
      <p className="text-xs text-gray-400 uppercase tracking-wide">Prévia da sua loja</p>
      <div
        className="border-[6px] border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl bg-white"
        style={{ width: 375, height: 667 }}
      >
        <iframe src={shopUrl} width="375" height="667" title="Prévia" className="border-none" />
      </div>
    </div>
  );
};

export default ShopPreview;
