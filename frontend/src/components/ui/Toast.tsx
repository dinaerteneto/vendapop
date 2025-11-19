import React, { useEffect } from 'react';

interface ToastProps {
  message: string;
  isVisible: boolean;
  onClose: () => void;
  type?: 'success' | 'error' | 'warning';
  duration?: number;
}

const Toast: React.FC<ToastProps> = ({ message, isVisible, onClose, type = 'warning', duration = 3000 }) => {
  useEffect(() => {
    if (isVisible) {
      const timer = setTimeout(() => {
        onClose();
      }, duration);
      return () => clearTimeout(timer);
    }
  }, [isVisible, duration, onClose]);

  if (!isVisible) return null;

  const bgColors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-amber-500',
  };

  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
  };

  return (
    <div className={`fixed top-20 left-1/2 transform -translate-x-1/2 z-50 flex items-center ${bgColors[type]} text-white px-6 py-3 rounded-full shadow-lg transition-all animate-fade-in-down`}>
      <span className="mr-2 text-lg">{icons[type]}</span>
      <span className="font-medium">{message}</span>
      <button onClick={onClose} className="ml-4 text-white/80 hover:text-white">
        ✕
      </button>
    </div>
  );
};

export default Toast;
