import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../services/api';
import Pagination from '../../../components/ui/Pagination';
import SortableHeader from '../../../components/ui/SortableHeader';

interface Order {
  id: number;
  order_number: string;
  status: string;
  total_amount: string;
  created_at: string;
  customer: {
    name: string;
    email: string;
    phone: string;
  };
}

interface PaginationData {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
  data: Order[];
}

const OrderList: React.FC = () => {
  const navigate = useNavigate();
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [pagination, setPagination] = useState<PaginationData | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(20);
  const [sortBy, setSortBy] = useState('id');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc');

  useEffect(() => {
    fetchOrders(currentPage, perPage, sortBy, sortDirection);
  }, [currentPage, perPage, sortBy, sortDirection]);

  const fetchOrders = async (page: number = 1, perPageValue: number = 20, sortByValue: string = 'id', sortDir: 'asc' | 'desc' = 'desc') => {
    try {
      setLoading(true);
      const response = await api.get(`/admin/orders?page=${page}&per_page=${perPageValue}&sort_by=${sortByValue}&sort_direction=${sortDir}`);
      const data = response.data;
      
      if (data.data) {
        // Resposta paginada
        setOrders(data.data);
        setPagination({
          current_page: data.current_page,
          last_page: data.last_page,
          total: data.total,
          per_page: data.per_page,
          data: data.data,
        });
      } else {
        // Fallback para resposta não paginada
        setOrders(Array.isArray(data) ? data : []);
        setPagination(null);
      }
      setError('');
    } catch (err: any) {
      console.error(err);
      setError('Erro ao carregar pedidos.');
    } finally {
      setLoading(false);
    }
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleSort = (key: string, direction: 'asc' | 'desc') => {
    setSortBy(key);
    setSortDirection(direction);
    setCurrentPage(1);
  };

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newPerPage = parseInt(e.target.value);
    setPerPage(newPerPage);
    setCurrentPage(1);
  };

  const getStatusColor = (status: string) => {
    const statusUpper = status.toUpperCase();
    if (statusUpper === 'NEW') return 'bg-yellow-100 text-yellow-800';
    if (statusUpper === 'DONE') return 'bg-green-100 text-green-800';
    if (statusUpper === 'CANCELED') return 'bg-red-100 text-red-800';
    return 'bg-gray-100 text-gray-800';
  };

  const getStatusLabel = (status: string) => {
    const statusUpper = status.toUpperCase();
    if (statusUpper === 'NEW') return 'Novo';
    if (statusUpper === 'DONE') return 'Concluído';
    if (statusUpper === 'CANCELED') return 'Cancelado';
    return status;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Carregando pedidos...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {error}
      </div>
    );
  }

  return (
    <div>
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold text-gray-800">Pedidos</h1>
            <p className="text-gray-600 mt-1">Gerencie todos os pedidos da sua loja</p>
          </div>
          <div className="flex items-center gap-2">
            <label htmlFor="perPage" className="text-sm text-gray-600">Itens por página:</label>
            <select
              id="perPage"
              value={perPage}
              onChange={handlePerPageChange}
              className="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>
      </div>

      {orders.length === 0 ? (
        <div className="bg-white rounded-lg shadow p-8 text-center">
          <p className="text-gray-500">Nenhum pedido encontrado.</p>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-hidden">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <SortableHeader
                    label="Pedido"
                    sortKey="order_number"
                    currentSort={sortBy}
                    currentDirection={sortDirection}
                    onSort={handleSort}
                  />
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente
                  </th>
                  <SortableHeader
                    label="Data"
                    sortKey="created_at"
                    currentSort={sortBy}
                    currentDirection={sortDirection}
                    onSort={handleSort}
                  />
                  <SortableHeader
                    label="Status"
                    sortKey="status"
                    currentSort={sortBy}
                    currentDirection={sortDirection}
                    onSort={handleSort}
                  />
                  <SortableHeader
                    label="Total"
                    sortKey="total_amount"
                    currentSort={sortBy}
                    currentDirection={sortDirection}
                    onSort={handleSort}
                  />
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {orders.map((order) => (
                  <tr key={order.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        #{order.order_number}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{order.customer.name}</div>
                      <div className="text-sm text-gray-500">{order.customer.email}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{formatDate(order.created_at)}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(order.status)}`}>
                        {getStatusLabel(order.status)}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        R$ {parseFloat(order.total_amount).toFixed(2).replace('.', ',')}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        onClick={() => navigate(`/admin/orders/${order.id}`)}
                        className="text-indigo-600 hover:text-indigo-900 mr-4"
                      >
                        Ver Detalhes
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {pagination && (
            <Pagination
              currentPage={pagination.current_page}
              lastPage={pagination.last_page}
              total={pagination.total}
              perPage={pagination.per_page}
              onPageChange={handlePageChange}
            />
          )}
        </div>
      )}
    </div>
  );
};

export default OrderList;

