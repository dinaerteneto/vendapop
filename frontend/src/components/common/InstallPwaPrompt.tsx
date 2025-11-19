import React, { useEffect, useState } from 'react';

const InstallPwaPrompt: React.FC<{ primaryColor?: string }> = ({ primaryColor = '#7c3aed' }) => {
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const handler = (e: Event) => {
      // Prevent the mini-infobar from appearing on mobile
      e.preventDefault();
      // Stash the event so it can be triggered later.
      setDeferredPrompt(e);
      // Update UI notify the user they can install the PWA
      setIsVisible(true);
    };

    window.addEventListener('beforeinstallprompt', handler);

    return () => {
      window.removeEventListener('beforeinstallprompt', handler);
    };
  }, []);

  const handleInstallClick = () => {
    setIsVisible(false);
    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then((choiceResult: any) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('User accepted the install prompt');
        } else {
          console.log('User dismissed the install prompt');
        }
        setDeferredPrompt(null);
      });
    }
  };

  if (!isVisible) return null;

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 p-4 bg-white shadow-lg border-t border-gray-200 animate-slide-up">
      <div className="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="flex items-center gap-3">
            <div className="bg-gray-100 p-2 rounded-lg">
                <span className="text-2xl">📱</span>
            </div>
            <div>
                <h3 className="font-bold text-gray-900">Instalar App</h3>
                <p className="text-sm text-gray-600">Instale nosso app para uma melhor experiência!</p>
            </div>
        </div>
        <div className="flex gap-3 w-full sm:w-auto">
            <button 
                onClick={() => setIsVisible(false)}
                className="flex-1 sm:flex-none py-2 px-4 rounded-lg font-medium text-gray-500 hover:bg-gray-100"
            >
                Agora não
            </button>
            <button 
                onClick={handleInstallClick}
                className="flex-1 sm:flex-none py-2 px-6 rounded-lg font-bold text-white shadow-md transition-transform hover:scale-105"
                style={{ backgroundColor: primaryColor }}
            >
                Instalar
            </button>
        </div>
      </div>
    </div>
  );
};

export default InstallPwaPrompt;

