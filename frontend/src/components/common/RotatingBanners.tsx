import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../services/api';

interface Banner {
  id: number;
  image_url: string;
  link_url?: string | null;
  title?: string | null;
  description?: string | null;
}

const RotatingBanners: React.FC = () => {
  const { storeSlug } = useParams();
  const [banners, setBanners] = useState<Banner[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!storeSlug) return;

    api.get(`/${storeSlug}/banners`)
      .then(response => {
        setBanners(response.data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching banners:', err);
        setLoading(false);
      });
  }, [storeSlug]);

  useEffect(() => {
    if (banners.length <= 1) return;

    const interval = setInterval(() => {
      setCurrentIndex((prevIndex) => (prevIndex + 1) % banners.length);
    }, 5000); // Muda a cada 5 segundos

    return () => clearInterval(interval);
  }, [banners.length]);

  if (loading) {
    return null;
  }

  if (banners.length === 0) {
    return null;
  }

  const currentBanner = banners[currentIndex];

  const handleBannerClick = () => {
    if (currentBanner.link_url) {
      window.open(currentBanner.link_url, '_blank', 'noopener,noreferrer');
    }
  };

  const goToSlide = (index: number) => {
    setCurrentIndex(index);
  };

  return (
    <div className="relative w-full mb-6">
      <div 
        className="relative w-full overflow-hidden rounded-lg cursor-pointer"
        style={{ 
          aspectRatio: '16/9',
          maxHeight: '500px'
        }}
        onClick={handleBannerClick}
      >
        <img
          src={currentBanner.image_url}
          alt={currentBanner.title || 'Banner'}
          className="w-full h-full object-cover transition-opacity duration-500"
        />
        
        {/* Overlay com título e descrição se existirem */}
        {(currentBanner.title || currentBanner.description) && (
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex items-end">
            <div className="w-full p-6 text-white">
              {currentBanner.title && (
                <h3 className="text-2xl font-bold mb-2">{currentBanner.title}</h3>
              )}
              {currentBanner.description && (
                <p className="text-sm opacity-90">{currentBanner.description}</p>
              )}
            </div>
          </div>
        )}

        {/* Indicadores de slide */}
        {banners.length > 1 && (
          <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-10">
            {banners.map((_, index) => (
              <button
                key={index}
                onClick={(e) => {
                  e.stopPropagation();
                  goToSlide(index);
                }}
                className={`w-2 h-2 rounded-full transition-all ${
                  index === currentIndex 
                    ? 'bg-white w-8' 
                    : 'bg-white/50 hover:bg-white/75'
                }`}
                aria-label={`Ir para slide ${index + 1}`}
              />
            ))}
          </div>
        )}

        {/* Botões de navegação */}
        {banners.length > 1 && (
          <>
            <button
              onClick={(e) => {
                e.stopPropagation();
                setCurrentIndex((prevIndex) => 
                  prevIndex === 0 ? banners.length - 1 : prevIndex - 1
                );
              }}
              className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all z-10"
              aria-label="Banner anterior"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              onClick={(e) => {
                e.stopPropagation();
                setCurrentIndex((prevIndex) => (prevIndex + 1) % banners.length);
              }}
              className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all z-10"
              aria-label="Próximo banner"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </>
        )}
      </div>
    </div>
  );
};

export default RotatingBanners;

