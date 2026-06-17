import React, { useState, useEffect } from 'react';
import api from '../../services/api';

interface Invite {
  code: string;
  url: string;
  type: string;
  max_uses: number;
  current_uses: number;
  expires_at: string;
  created_at: string;
}

const InvitePanel: React.FC = () => {
  const [remaining, setRemaining] = useState<number>(0);
  const [invites, setInvites] = useState<Invite[]>([]);
  const [lastCode, setLastCode] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [remRes, invRes] = await Promise.all([
        api.get('/admin/invites/remaining'),
        api.get('/admin/invites'),
      ]);
      setRemaining(remRes.data.remaining);
      setInvites(invRes.data);
    } catch {
      console.error('Erro ao carregar convites');
    } finally {
      setLoading(false);
    }
  };

  const handleGenerate = async () => {
    try {
      const res = await api.post('/admin/invites');
      setLastCode(res.data.code);
      setRemaining(res.data.remaining);
      loadData();
    } catch {
      alert('Erro ao gerar convite');
    }
  };

  const handleCopy = (url: string) => {
    const fullUrl = `${window.location.origin}/convite/${url}`;
    navigator.clipboard.writeText(fullUrl).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    });
  };

  const statusLabel = (invite: Invite) => {
    if (invite.current_uses >= invite.max_uses) return 'Usado';
    if (new Date(invite.expires_at) < new Date()) return 'Expirado';
    return 'Ativo';
  };

  const statusColor = (invite: Invite) => {
    if (invite.current_uses >= invite.max_uses) return 'text-gray-400';
    if (new Date(invite.expires_at) < new Date()) return 'text-red-500';
    return 'text-green-500';
  };

  if (loading) return null;

  return (
    <div className="bg-white border border-purple-200 rounded-lg shadow-sm">
      <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div className="flex items-center gap-2">
          <span className="text-lg">📨</span>
          <h3 className="text-sm font-semibold text-gray-800">Seus Convites</h3>
        </div>
        <span className="text-xs font-medium px-2 py-1 bg-purple-100 text-purple-700 rounded-full">
          {remaining} restante{remaining !== 1 ? 's' : ''}
        </span>
      </div>

      <div className="px-6 py-4">
        {lastCode && (
          <div className="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p className="text-xs text-green-700 mb-2">Convite gerado!</p>
            <div className="flex gap-2">
              <input
                type="text"
                readOnly
                value={`${window.location.origin}/convite/${lastCode}`}
                className="flex-1 text-xs bg-white border border-green-300 rounded px-2 py-1 text-gray-700"
              />
              <button
                onClick={() => handleCopy(lastCode)}
                className="text-xs px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition"
              >
                {copied ? 'Copiado!' : 'Copiar'}
              </button>
            </div>
          </div>
        )}

        <button
          onClick={handleGenerate}
          disabled={remaining <= 0}
          className="w-full py-2 text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed bg-purple-600 text-white hover:bg-purple-700"
        >
          {remaining > 0 ? `Gerar Convite (${remaining})` : 'Sem convites disponíveis'}
        </button>

        {invites.length > 0 && (
          <div className="mt-4">
            <p className="text-xs text-gray-500 mb-2">Histórico</p>
            <div className="space-y-2 max-h-48 overflow-y-auto">
              {invites.map((invite) => (
                <div key={invite.code} className="flex items-center justify-between text-xs py-1 border-b border-gray-100 last:border-0">
                  <div className="flex items-center gap-2">
                    <code className="bg-gray-100 px-1.5 py-0.5 rounded text-gray-700 font-mono">{invite.code}</code>
                    <span className="text-gray-400">
                      {new Date(invite.created_at).toLocaleDateString('pt-BR')}
                    </span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className={statusColor(invite)}>{statusLabel(invite)}</span>
                    {invite.type === 'public' && <span className="text-gray-400">Público</span>}
                    <button
                      onClick={() => handleCopy(invite.code)}
                      className="text-gray-400 hover:text-purple-600"
                      title="Copiar link"
                    >
                      📋
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default InvitePanel;
