import React from 'react';
import { useNavigate } from 'react-router-dom';

export interface LegalSection {
  id: string;
  title: string;
  content: React.ReactNode;
}

export interface LegalPageProps {
  title: string;
  lastUpdated: string;
  sections: LegalSection[];
}

export default function LegalPage({ title, lastUpdated, sections }: LegalPageProps) {
  const navigate = useNavigate();

  const handlePrint = () => {
    window.print();
  };

  const handleClose = () => {
    navigate(-1);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-4xl mx-auto px-4 py-8">
        <header className="mb-8">
          <div className="flex items-center justify-between mb-4">
            <button
              onClick={handleClose}
              className="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-200 transition"
              aria-label="Voltar"
            >
              <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <button
              onClick={handlePrint}
              className="hidden print:hidden md:flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
              aria-label="Imprimir página"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Imprimir
            </button>
          </div>
          <h1 className="text-3xl font-bold text-gray-900">{title}</h1>
          <p className="text-sm text-gray-500 mt-2">Última atualização: {lastUpdated}</p>
        </header>

        <nav className="mb-8 p-4 bg-white rounded-lg shadow-sm print:hidden">
          <h2 className="text-lg font-semibold text-gray-900 mb-3">Índice</h2>
          <ul className="space-y-2">
            {sections.map((section) => (
              <li key={section.id}>
                <a
                  href={`#${section.id}`}
                  className="text-sm text-blue-600 hover:text-blue-800 hover:underline transition"
                >
                  {section.title}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        <article className="space-y-8">
          {sections.map((section) => (
            <section key={section.id} id={section.id} className="scroll-mt-4">
              <h2 className="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                {section.title}
              </h2>
              <div className="prose prose-sm max-w-none text-gray-700">
                {section.content}
              </div>
            </section>
          ))}
        </article>

        <footer className="mt-12 pt-8 border-t border-gray-200 text-center text-sm text-gray-500 print:hidden">
          <p>© 2026 PopVenda. Todos os direitos reservados.</p>
        </footer>
      </div>
    </div>
  );
}
