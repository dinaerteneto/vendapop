import React from 'react';

interface PromotionalBannerProps {
  message: string;
  textColor1: string;
  textColor2: string;
  backgroundColor: string;
}

const PromotionalBanner: React.FC<PromotionalBannerProps> = ({
  message,
  textColor1,
  textColor2,
  backgroundColor,
}) => {
  if (!message) return null;

  return (
    <div 
      className="w-full py-2 px-4 text-center font-bold text-sm uppercase tracking-wider shadow-md sticky top-[72px] z-20"
      style={{ 
        backgroundColor: backgroundColor,
      }}
    >
      <style>
        {`
          @keyframes colorPulse {
            0% { color: ${textColor1}; }
            50% { color: ${textColor2}; }
            100% { color: ${textColor1}; }
          }
        `}
      </style>
      <span style={{ animation: 'colorPulse 2s infinite' }}>
        {message}
      </span>
    </div>
  );
};

export default PromotionalBanner;

