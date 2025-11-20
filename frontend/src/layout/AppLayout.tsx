import React, { useState, useEffect } from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';

// Layout simples para Admin (Sidebar + Header + Content)
const AppLayout: React.FC = () => {
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [user, setUser] = useState<{ name: string; email: string } | null>(null);

  useEffect(() => {
      const storedUser = localStorage.getItem('user');
      if (storedUser) {
          setUser(JSON.parse(storedUser));
      }
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('user');
    navigate('/admin/login');
  };

  return (
    <div className="flex h-screen overflow-hidden bg-gray-100">
      {/* Sidebar */}
      <aside className={`absolute left-0 top-0 z-50 flex h-screen w-64 flex-col overflow-y-hidden bg-black duration-300 ease-linear lg:static lg:translate-x-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
          <Link to="/admin" className="text-2xl font-bold text-white">VesteZap Admin</Link>
        </div>

        <div className="flex flex-col overflow-y-auto duration-300 ease-linear">
          <nav className="mt-5 px-4 py-4 lg:mt-9 lg:px-6">
            <ul className="mb-6 flex flex-col gap-1.5">
              <li><Link to="/admin" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Dashboard</Link></li>
              <li><Link to="/admin/products" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Produtos</Link></li>
              <li><Link to="/admin/categories" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Categorias</Link></li>
              <li><Link to="/admin/orders" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Pedidos</Link></li>
              <li><Link to="/admin/customers" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Clientes</Link></li>
              <li><Link to="/admin/store-settings" className="block px-4 py-2 text-gray-200 hover:bg-gray-700 rounded">Minha Loja</Link></li>
            </ul>
          </nav>
        </div>
      </aside>

      {/* Content Area */}
      <div className="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
        <header className="sticky top-0 z-40 flex w-full bg-white drop-shadow-1">
            <div className="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
                <button onClick={() => setSidebarOpen(!sidebarOpen)} className="lg:hidden">Menu</button>
                <div className="flex items-center gap-3 2xl:gap-7">
                   {user && (
                       <div className="text-right hidden sm:block">
                           <span className="block text-sm font-medium text-black">{user.name}</span>
                           <span className="block text-xs text-gray-500">{user.email}</span>
                       </div>
                   )}
                   <button onClick={handleLogout} className="text-sm text-red-600 hover:underline">Sair</button>
                </div>
            </div>
        </header>

        <main>
          <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default AppLayout;
