const CaseSection: React.FC = () => {
  return (
    <section className="bg-gray-50 py-16">
      <div className="container mx-auto px-4 text-center">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Lojas que já estão vendendo mais</h2>
        <p className="text-gray-500 mb-8">Os primeiros lojistas estão entrando. Seja um deles.</p>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="bg-white rounded-xl border border-gray-200 p-4 opacity-60">
              <div className="bg-gray-100 rounded-lg h-40 mb-3 flex items-center justify-center">
                <span className="text-gray-400 text-sm">Em breve</span>
              </div>
              <div className="h-3 bg-gray-200 rounded w-20 mx-auto"></div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CaseSection;
