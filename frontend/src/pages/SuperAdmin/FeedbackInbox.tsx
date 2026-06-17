import React, { useState, useEffect } from 'react';
import api from '../../services/api';

interface Feedback {
  id: number;
  tenant: { name: string; slug: string } | null;
  subject: string;
  message: string;
  status: string;
  created_at: string;
}

const FeedbackInbox: React.FC = () => {
  const [feedbacks, setFeedbacks] = useState<Feedback[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState('');
  const [expanded, setExpanded] = useState<number | null>(null);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  useEffect(() => {
    loadFeedbacks();
  }, [page, statusFilter]);

  const loadFeedbacks = async () => {
    setLoading(true);
    try {
      const params: any = { page, per_page: 20 };
      if (statusFilter) params.status = statusFilter;
      const res = await api.get('/superadmin/feedbacks', { params });
      setFeedbacks(res.data.data);
      setLastPage(res.data.last_page);
    } catch (err) {
      console.error('Erro ao carregar feedbacks', err);
    } finally {
      setLoading(false);
    }
  };

  const updateStatus = async (id: number, status: string) => {
    try {
      await api.put(`/superadmin/feedbacks/${id}`, { status });
      loadFeedbacks();
    } catch (err) {
      console.error('Erro ao atualizar', err);
    }
  };

  const statusColors: Record<string, string> = {
    unread: 'bg-yellow-100 text-yellow-800',
    read: 'bg-blue-100 text-blue-800',
    resolved: 'bg-green-100 text-green-800',
  };

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-4">Feedback</h1>

      <div className="bg-white rounded-lg shadow p-4 mb-4">
        <select value={statusFilter} onChange={e => { setStatusFilter(e.target.value); setPage(1); }}
          className="px-3 py-2 border rounded">
          <option value="">Todos</option>
          <option value="unread">Não lidos</option>
          <option value="read">Lidos</option>
          <option value="resolved">Resolvidos</option>
        </select>
      </div>

      {loading ? (
        <div className="text-center py-8 text-gray-500">Carregando...</div>
      ) : (
        <div className="space-y-2">
          {feedbacks.map(fb => (
            <div key={fb.id} className="bg-white rounded-lg shadow">
              <div className="p-4 cursor-pointer" onClick={() => setExpanded(expanded === fb.id ? null : fb.id)}>
                <div className="flex items-center justify-between">
                  <div>
                    <span className="font-medium">{fb.subject}</span>
                    <span className="text-gray-400 mx-2">—</span>
                    <span className="text-gray-500 text-sm">{fb.tenant?.name || 'Desconhecido'}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[fb.status] || ''}`}>
                      {fb.status}
                    </span>
                    <span className="text-xs text-gray-400">{new Date(fb.created_at).toLocaleDateString('pt-BR')}</span>
                  </div>
                </div>
              </div>
              {expanded === fb.id && (
                <div className="px-4 pb-4 border-t pt-3">
                  <p className="text-gray-700 whitespace-pre-wrap">{fb.message}</p>
                  <div className="flex gap-2 mt-3">
                    {fb.status === 'unread' && (
                      <button onClick={() => updateStatus(fb.id, 'read')}
                        className="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                        Marcar como lido
                      </button>
                    )}
                    {(fb.status === 'unread' || fb.status === 'read') && (
                      <button onClick={() => updateStatus(fb.id, 'resolved')}
                        className="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                        Marcar como resolvido
                      </button>
                    )}
                  </div>
                </div>
              )}
            </div>
          ))}
          {feedbacks.length === 0 && (
            <div className="text-center py-8 text-gray-500 bg-white rounded-lg shadow">Nenhum feedback</div>
          )}

          {lastPage > 1 && (
            <div className="flex justify-center gap-2 p-4">
              <button disabled={page <= 1} onClick={() => setPage(p => p - 1)} className="px-3 py-1 border rounded bg-white disabled:opacity-50">Anterior</button>
              <span className="px-3 py-1">{page} de {lastPage}</span>
              <button disabled={page >= lastPage} onClick={() => setPage(p => p + 1)} className="px-3 py-1 border rounded bg-white disabled:opacity-50">Próximo</button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default FeedbackInbox;
