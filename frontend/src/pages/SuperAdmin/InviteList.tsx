import React, { useState, useEffect } from 'react';
import api from '../../services/api';

interface Invite {
  id: number;
  code: string;
  type: string;
  max_uses: number;
  current_uses: number;
  expires_at: string;
  is_active: boolean;
  created_at: string;
}

const InviteList: React.FC = () => {
  const [invites, setInvites] = useState<Invite[]>([]);
  const [loading, setLoading] = useState(true);
  const [typeFilter, setTypeFilter] = useState('');
  const [count, setCount] = useState(1);
  const [generated, setGenerated] = useState<{ code: string; link: string }[]>([]);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  useEffect(() => {
    loadInvites();
  }, [page, typeFilter]);

  const loadInvites = async () => {
    setLoading(true);
    try {
      const params: any = { page, per_page: 20 };
      if (typeFilter) params.type = typeFilter;
      const res = await api.get('/superadmin/invites', { params });
      setInvites(res.data.data);
      setLastPage(res.data.last_page);
    } catch (err) {
      console.error('Erro ao carregar invites', err);
    } finally {
      setLoading(false);
    }
  };

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const res = await api.post('/superadmin/invites', { count });
      const baseUrl = window.location.origin;
      setGenerated(res.data.map((i: any) => ({
        code: i.code,
        link: `${baseUrl}/convite/${i.code}`,
      })));
      loadInvites();
    } catch (err) {
      console.error('Erro ao gerar invites', err);
    }
  };

  const handleToggle = async (id: number) => {
    try {
      await api.put(`/superadmin/invites/${id}/toggle`);
      loadInvites();
    } catch (err) {
      console.error('Erro ao alternar status', err);
    }
  };

  const copyToClipboard = async (text: string) => {
    try {
      await navigator.clipboard.writeText(text);
    } catch {
      const el = document.createElement('textarea');
      el.value = text;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
    }
  };

  const isExpired = (date: string) => new Date(date) < new Date();

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-2">Códigos de Convite</h1>

      <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-800">
        <span className="font-medium">Manual:</span> convite individual (1 uso, expira em 7 dias) — ideal para enviar pra uma pessoa específica.<br />
        <span className="font-medium">Público:</span> link com múltiplos usos (expira em horas) — ideal para campanhas e vagas limitadas.
      </div>

      <div className="bg-white rounded-lg shadow p-4 mb-4">
        <form onSubmit={handleGenerate} className="flex flex-wrap gap-3 items-end">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
            <input type="number" min={1} max={20} value={count} onChange={e => setCount(parseInt(e.target.value) || 1)}
              className="w-20 px-3 py-2 border rounded" />
          </div>
          <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Gerar Convites
          </button>
          <select value={typeFilter} onChange={e => { setTypeFilter(e.target.value); setPage(1); }}
            className="px-3 py-2 border rounded ml-auto">
            <option value="">Todos os tipos</option>
            <option value="manual">Manual</option>
            <option value="public">Público</option>
          </select>
        </form>

        {generated.length > 0 && (
          <div className="mt-4 p-3 bg-green-50 border border-green-200 rounded">
            <h4 className="font-medium text-green-800 mb-1">Convites gerados — clique para copiar o link:</h4>
            <div className="space-y-1">
              {generated.map((item, i) => (
                <button key={i} onClick={() => copyToClipboard(item.link)}
                  className="block w-full text-left px-3 py-1.5 bg-green-100 text-green-800 font-mono text-sm rounded hover:bg-green-200 transition-colors"
                  title="Clique para copiar o link">
                  {item.link}
                </button>
              ))}
            </div>
          </div>
        )}
      </div>

      {loading ? (
        <div className="text-center py-8 text-gray-500">Carregando...</div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="text-left px-3 py-3">Código</th>
                <th className="text-left px-3 py-3">Link</th>
                <th className="text-left px-3 py-3">Tipo</th>
                <th className="text-left px-3 py-3">Usos</th>
                <th className="text-left px-3 py-3">Expira em</th>
                <th className="text-left px-3 py-3">Status</th>
                <th className="text-left px-3 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {invites.map(inv => {
                const link = `${window.location.origin}/convite/${inv.code}`;
                return (
                  <tr key={inv.id} className="border-b">
                    <td className="px-3 py-2 font-mono">{inv.code}</td>
                    <td className="px-3 py-2">
                      <button onClick={() => copyToClipboard(link)}
                        className="text-indigo-600 hover:text-indigo-800 text-xs font-mono truncate max-w-[200px] inline-block"
                        title="Clique para copiar">
                        {link}
                      </button>
                    </td>
                    <td className="px-3 py-2 capitalize">{inv.type}</td>
                    <td className="px-3 py-2">{inv.current_uses} / {inv.max_uses}</td>
                    <td className="px-3 py-2 text-gray-500">{new Date(inv.expires_at).toLocaleDateString('pt-BR')}</td>
                    <td className="px-3 py-2">
                      {!inv.is_active ? (
                        <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Inativo</span>
                      ) : isExpired(inv.expires_at) ? (
                        <span className="px-2 py-0.5 bg-red-100 text-red-800 text-xs rounded-full">Expirado</span>
                      ) : inv.current_uses >= inv.max_uses ? (
                        <span className="px-2 py-0.5 bg-orange-100 text-orange-800 text-xs rounded-full">Esgotado</span>
                      ) : (
                        <span className="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Ativo</span>
                      )}
                    </td>
                    <td className="px-3 py-2">
                      <button onClick={() => handleToggle(inv.id)}
                        className={`px-2 py-1 text-xs rounded ${inv.is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'}`}>
                        {inv.is_active ? 'Desativar' : 'Ativar'}
                      </button>
                    </td>
                  </tr>
                );
              })}
              {invites.length === 0 && (
                <tr><td colSpan={7} className="text-center py-8 text-gray-500">Nenhum convite encontrado</td></tr>
              )}
            </tbody>
          </table>

          {lastPage > 1 && (
            <div className="flex justify-center gap-2 p-4 border-t">
              <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="px-3 py-1 border rounded disabled:opacity-50">Anterior</button>
              <span className="px-3 py-1">{page} de {lastPage}</span>
              <button disabled={page >= lastPage} onClick={() => setPage(p => p + 1)} className="px-3 py-1 border rounded disabled:opacity-50">Próximo</button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default InviteList;
