import React from 'react';

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

interface FooterProps {
  storeInfo: StoreInfo | null;
}

const Footer: React.FC<FooterProps> = ({ storeInfo }) => {
  if (!storeInfo) return null;

  const whatsappLink = `https://wa.me/${storeInfo.whatsapp_number?.replace(/[^0-9]/g, '')}`;

  return (
    <footer className="bg-gray-50 pt-10 pb-6 px-4 mt-10 border-t">
      <div className="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        
        {/* Contact Section */}
        <div>
          <div className="flex items-center mb-4">
             <a href={whatsappLink} target="_blank" rel="noopener noreferrer" className="flex items-center text-gray-700 hover:text-green-600">
                <div className="w-8 h-8 mr-3 text-green-500">
                    {/* WhatsApp Icon SVG */}
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
                <span className="font-medium">{storeInfo.name}</span>
             </a>
          </div>

          <h3 className="text-gray-500 font-semibold mb-4 uppercase text-sm mt-6">Atendimento ao cliente</h3>
          <div className="space-y-3 text-gray-600 text-sm">
             {storeInfo.whatsapp_number && (
                 <div className="flex items-center">
                    <span className="mr-2">📞</span>
                    {storeInfo.whatsapp_number}
                 </div>
             )}
             {storeInfo.email_contact && (
                 <div className="flex items-center">
                    <span className="mr-2">✉️</span>
                    {storeInfo.email_contact}
                 </div>
             )}
             {storeInfo.address && (
                 <div className="flex items-center">
                    <span className="mr-2">📍</span>
                    {storeInfo.address}
                 </div>
             )}
          </div>
        </div>

        {/* Socials & Links */}
        <div>
            <h3 className="text-gray-500 font-semibold mb-4 uppercase text-sm">Nos siga nas redes sociais</h3>
            <div className="flex space-x-4 mb-6">
                {storeInfo.socials?.map((social, idx) => (
                    <a key={idx} href={social.url} target="_blank" rel="noopener noreferrer" className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center hover:bg-purple-100 hover:text-purple-600 transition-colors text-2xl">
                        {/* Basic mapping based on name or just show generic if no icon */}
                        {social.icon ? <img src={social.icon} alt={social.name} className="w-6 h-6" /> : <span>🌐</span>}
                    </a>
                ))}
                {/* Fallback if no socials */}
                {(!storeInfo.socials || storeInfo.socials.length === 0) && (
                    <span className="text-gray-400 italic text-sm">Sem redes sociais cadastradas.</span>
                )}
            </div>

            <div className="mt-8 flex items-center text-green-600 font-bold">
                <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <span className="text-2xl">🛡️</span>
                </div>
                <div>
                    <p className="uppercase text-xs text-gray-500">Site 100%</p>
                    <p className="text-lg leading-none">SEGURO</p>
                </div>
            </div>
        </div>
      </div>
      <div className="text-center text-xs text-gray-400 mt-8 pt-4 border-t border-gray-200">
        VesteZap © 2025 - Todos os direitos reservados.
      </div>
    </footer>
  );
};

export default Footer;
