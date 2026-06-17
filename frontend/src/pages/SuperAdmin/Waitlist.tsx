import React, { useState, useEffect } from 'react';
import api from '../../services/api';

interface WaitlistEntry {
  id: number;
  email: string;
  status: string;
  rejection_reason: string | null;
  created_at: string;
}

const Waitlist: React.FC = () => {
  const [entries, setEntries] = useState<WaitlistEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState('');
  const [selected, setSelected] = useState<number[]>([]);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [inviteCodes, setInviteCodes] = useState<Record<number, string>>({});
  const [emailSent, setEmailSent] = useState<Record<number, boolean>>({});

  useEffect(() => {
    loadEntries();
  }, [page, statusFilter]);

  const loadEntries = async () => {
    setLoading(true);
    try {
      const params: any = { page, per_page: 20 };
      if (statusFilter) params.status = statusFilter;
      const res = await api.get('/superadmin/waitlist', { params });
      setEntries(res.data.data);
      setLastPage(res.data.last_page);
    } catch (err) {
      console.error('Erro ao carregar waitlist', err);
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (id: number) => {
    try {
      const res = await api.put(`/superadmin/waitlist/${id}`, { status: 'approved' });
      setInviteCodes(prev => ({ ...prev, [id]: res.data.invite_code }));
      if (res.data.email_sent) {
        setEmailSent(prev => ({ ...prev, [id]: true }));
      }
      loadEntries();
    } catch (err) {
      console.error('Erro ao aprovar', err);
    }
  };

  const handleBatchApprove = async () => {
    try {
      const res = await api.post('/superadmin/waitlist/batch', { ids: selected });
      res.data.forEach((r: any) => {
        setInviteCodes(prev => ({ ...prev, [r.entry.id]: r.invite_code }));
      });
      setSelected([]);
      loadEntries();
    } catch (err) {
      console.error('Erro ao aprovar em lote', err);
    }
  };

  const handleReject = async (id: number) => {
    const reason = prompt('Motivo da rejeição (opcional):');
    try {
      await api.put(`/superadmin/waitlist/${id}`, { status: 'rejected', rejection_reason: reason || undefined });
      loadEntries();
    } catch (err) {
      console.error('Erro ao rejeitar', err);
    }
  };

  const toggleSelect = (id: number) => {
    setSelected(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-4">Lista de Espera</h1>

      <div className="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-center">
        <select value={statusFilter} onChange={e => { setStatusFilter(e.target.value); setPage(1); }}
          className="px-3 py-2 border rounded">
          <option value="">Todos os status</option>
          <option value="pending">Pendente</option>
          <option value="approved">Aprovado</option>
          <option value="rejected">Rejeitado</option>
        </select>
        {selected.length > 0 && (
          <button onClick={handleBatchApprove}
            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Aprovar {selected.length} selecionados
          </button>
        )}
      </div>

      {Object.keys(inviteCodes).length > 0 && (
        <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
          <h3 className="font-medium text-green-800 mb-2">Códigos gerados:</h3>
          {Object.entries(inviteCodes).map(([id, code]) => (
            <div key={id} className="text-sm text-green-700">
              Entry #{id}: <code className="bg-green-100 px-2 py-0.5 rounded font-mono">{code}</code>
            </div>
          ))}
        </div>
      )}

      {loading ? (
        <div className="text-center py-8 text-gray-500">Carregando...</div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-3 py-3"><input type="checkbox" onChange={e => {
                  setSelected(e.target.checked ? entries.filter(e => e.status === 'pending').map(e => e.id) : []);
                }} /></th>
                <th className="text-left px-3 py-3">Email</th>
                <th className="text-left px-3 py-3">Status</th>
                <th className="text-left px-3 py-3">Data</th>
                <th className="text-left px-3 py-3">Ações</th>
              </tr>
            </thead>
            <tbody>
              {entries.map(entry => (
                <tr key={entry.id} className="border-b hover:bg-gray-50">
                  <td className="px-3 py-2">
                    {entry.status === 'pending' && (
                      <input type="checkbox" checked={selected.includes(entry.id)} onChange={() => toggleSelect(entry.id)} />
                    )}
                  </td>
                  <td className="px-3 py-2">{entry.email}</td>
                  <td className="px-3 py-2">
                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[entry.status] || ''}`}>
                      {entry.status}
                    </span>
                    {entry.rejection_reason && <div className="text-xs text-gray-400 mt-1">{entry.rejection_reason}</div>}
                  </td>
                  <td className="px-3 py-2 text-gray-500">{new Date(entry.created_at).toLocaleDateString('pt-BR')}</td>
                  <td className="px-3 py-2">
                    {entry.status === 'pending' && (
                      <div className="flex gap-2">
                        <button onClick={() => handleApprove(entry.id)}
                          className="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Aprovar</button>
                        <button onClick={() => handleReject(entry.id)}
                          className="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Rejeitar</button>
                      </div>
                    )}
                    {inviteCodes[entry.id] && (
                      <div className="mt-1">
                        <code className="text-xs bg-gray-100 px-2 py-0.5 rounded inline-block">{inviteCodes[entry.id]}</code>
                        {emailSent[entry.id] && (
                          <span className="text-xs text-green-600 ml-1">✓ Email enviado</span>
                        )}
                      </div>
                    )}
                  </td>
                </tr>
              ))}
              {entries.length === 0 && (
                <tr><td colSpan={5} className="text-center py-8 text-gray-500">Nenhuma entrada na lista de espera</td></tr>
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

export default Waitlist;
