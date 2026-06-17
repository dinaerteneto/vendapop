import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';

const TenantDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [tenant, setTenant] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadTenant();
  }, [id]);

  const loadTenant = async () => {
    try {
      const res = await api.get(`/superadmin/tenants/${id}`);
      setTenant(res.data);
    } catch (err) {
      console.error('Erro ao carregar tenant', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="text-center py-8 text-gray-500">Carregando...</div>;
  if (!tenant) return <div className="text-center py-8 text-red-500">Tenant não encontrado</div>;

  return (
    <div>
      <button onClick={() => navigate('/superadmin')} className="text-indigo-600 hover:underline mb-4 inline-block">
        &larr; Voltar para lista
      </button>

      <div className="bg-white rounded-lg shadow p-6 mb-4">
        <h1 className="text-2xl font-bold text-gray-800 mb-4">{tenant.name}</h1>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div><span className="font-medium text-gray-600">Slug:</span> {tenant.slug}</div>
          <div><span className="font-medium text-gray-600">WhatsApp:</span> {tenant.whatsapp_number}</div>
          <div><span className="font-medium text-gray-600">Email:</span> {tenant.email_contact || '—'}</div>
          <div><span className="font-medium text-gray-600">Ramo:</span> {tenant.business_sector || '—'}</div>
          <div><span className="font-medium text-gray-600">Produtos:</span> {tenant.products_count}</div>
          <div><span className="font-medium text-gray-600">Pedidos:</span> {tenant.orders_count}</div>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6 mb-4">
        <h2 className="text-lg font-semibold text-gray-800 mb-3">Usuários</h2>
        {tenant.users?.length > 0 ? (
          <table className="w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">Nome</th>
                <th className="text-left px-3 py-2">Email</th>
                <th className="text-left px-3 py-2">Owner</th>
                <th className="text-left px-3 py-2">Último Login</th>
              </tr>
            </thead>
            <tbody>
              {tenant.users.map((u: any) => (
                <tr key={u.id} className="border-b">
                  <td className="px-3 py-2">{u.name}</td>
                  <td className="px-3 py-2 text-gray-500">{u.email}</td>
                  <td className="px-3 py-2">{u.is_owner ? 'Sim' : 'Não'}</td>
                  <td className="px-3 py-2 text-gray-500">
                    {u.last_login_at ? new Date(u.last_login_at).toLocaleDateString('pt-BR') : '—'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : <p className="text-gray-500">Nenhum usuário</p>}
      </div>

      {tenant.subscriptions?.length > 0 && (
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-3">Histórico de Assinaturas</h2>
          <table className="w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2">Plano</th>
                <th className="text-left px-3 py-2">Status</th>
                <th className="text-left px-3 py-2">Início</th>
                <th className="text-left px-3 py-2">Término</th>
              </tr>
            </thead>
            <tbody>
              {tenant.subscriptions.map((s: any) => (
                <tr key={s.id} className="border-b">
                  <td className="px-3 py-2 capitalize">{s.plan_type}</td>
                  <td className="px-3 py-2 capitalize">{s.plan_status}</td>
                  <td className="px-3 py-2">{new Date(s.started_at).toLocaleDateString('pt-BR')}</td>
                  <td className="px-3 py-2">{s.ends_at ? new Date(s.ends_at).toLocaleDateString('pt-BR') : '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default TenantDetail;
