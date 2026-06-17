import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';

interface Tenant {
  id: number;
  name: string;
  slug: string;
  business_sector: string | null;
  subscriptions: Array<{
    plan_type: string;
    plan_status: string;
  }>;
  users: Array<{
    last_login_at: string | null;
  }>;
  created_at: string;
}

const TenantList: React.FC = () => {
  const navigate = useNavigate();
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [planType, setPlanType] = useState('');
  const [planStatus, setPlanStatus] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  useEffect(() => {
    loadTenants();
  }, [page, planType, planStatus]);

  const loadTenants = async () => {
    setLoading(true);
    try {
      const params: any = { page, per_page: 20 };
      if (search) params.search = search;
      if (planType) params.plan_type = planType;
      if (planStatus) params.plan_status = planStatus;

      const res = await api.get('/superadmin/tenants', { params });
      setTenants(res.data.data);
      setLastPage(res.data.last_page);
    } catch (err) {
      console.error('Erro ao carregar tenants', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    loadTenants();
  };

  const getActiveSubscription = (tenant: Tenant) => {
    return tenant.subscriptions?.[0] || null;
  };

  const getLastLogin = (tenant: Tenant) => {
    const logins = tenant.users?.map(u => u.last_login_at).filter(Boolean).sort().reverse();
    return logins?.[0] || null;
  };

  const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('pt-BR');
  };

  const planTypeLabel: Record<string, string> = {
    free: 'Gratuito', basic: 'Básico', professional: 'Profissional', premium: 'Premium',
  };

  const planStatusColors: Record<string, string> = {
    active: 'bg-green-100 text-green-800', trial: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800', expired: 'bg-gray-100 text-gray-800',
  };

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-4">Tenants</h1>

      <div className="bg-white rounded-lg shadow p-4 mb-4">
        <form onSubmit={handleSearch} className="flex flex-wrap gap-3">
          <input
            type="text" value={search} onChange={e => setSearch(e.target.value)}
            placeholder="Buscar por nome ou slug..." className="px-3 py-2 border rounded flex-1 min-w-[200px]"
          />
          <select value={planType} onChange={e => { setPlanType(e.target.value); setPage(1); }}
            className="px-3 py-2 border rounded">
            <option value="">Todos os planos</option>
            <option value="free">Gratuito</option>
            <option value="basic">Básico</option>
            <option value="professional">Profissional</option>
            <option value="premium">Premium</option>
          </select>
          <select value={planStatus} onChange={e => { setPlanStatus(e.target.value); setPage(1); }}
            className="px-3 py-2 border rounded">
            <option value="">Todos os status</option>
            <option value="active">Ativo</option>
            <option value="trial">Trial</option>
            <option value="cancelled">Cancelado</option>
            <option value="expired">Expirado</option>
          </select>
          <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Buscar
          </button>
        </form>
      </div>

      {loading ? (
        <div className="text-center py-8 text-gray-500">Carregando...</div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="text-left px-4 py-3">Loja</th>
                <th className="text-left px-4 py-3">Slug</th>
                <th className="text-left px-4 py-3">Plano</th>
                <th className="text-left px-4 py-3">Status</th>
                <th className="text-left px-4 py-3">Ramo</th>
                <th className="text-left px-4 py-3">Último Login</th>
                <th className="text-left px-4 py-3">Registrado</th>
              </tr>
            </thead>
            <tbody>
              {tenants.map(tenant => {
                const sub = getActiveSubscription(tenant);
                return (
                  <tr key={tenant.id} onClick={() => navigate(`/superadmin/tenants/${tenant.id}`)}
                    className="border-b hover:bg-gray-50 cursor-pointer">
                    <td className="px-4 py-3 font-medium">{tenant.name}</td>
                    <td className="px-4 py-3 text-gray-500">{tenant.slug}</td>
                    <td className="px-4 py-3">{sub ? planTypeLabel[sub.plan_type] || sub.plan_type : '—'}</td>
                    <td className="px-4 py-3">
                      {sub && <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${planStatusColors[sub.plan_status] || ''}`}>
                        {sub.plan_status}
                      </span>}
                    </td>
                    <td className="px-4 py-3 text-gray-500">{tenant.business_sector || '—'}</td>
                    <td className="px-4 py-3 text-gray-500">{formatDate(getLastLogin(tenant))}</td>
                    <td className="px-4 py-3 text-gray-500">{formatDate(tenant.created_at)}</td>
                  </tr>
                );
              })}
              {tenants.length === 0 && (
                <tr><td colSpan={7} className="text-center py-8 text-gray-500">Nenhum tenant encontrado</td></tr>
              )}
            </tbody>
          </table>

          {lastPage > 1 && (
            <div className="flex justify-center gap-2 p-4 border-t">
              <button disabled={page <= 1} onClick={() => setPage(p => p - 1)}
                className="px-3 py-1 border rounded disabled:opacity-50">Anterior</button>
              <span className="px-3 py-1">{page} de {lastPage}</span>
              <button disabled={page >= lastPage} onClick={() => setPage(p => p + 1)}
                className="px-3 py-1 border rounded disabled:opacity-50">Próximo</button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default TenantList;
