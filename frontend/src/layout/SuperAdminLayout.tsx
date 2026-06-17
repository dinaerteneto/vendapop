import React, { useState, useEffect } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';

const SuperAdminLayout: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [user, setUser] = useState<{ name: string; email: string } | null>(null);

  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
  }, []);

  useEffect(() => {
    if (window.innerWidth < 1024) {
      setSidebarOpen(false);
    }
  }, [location]);

  const handleLogout = () => {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('user');
    localStorage.removeItem('is_super_admin');
    window.dispatchEvent(new Event('localStorageChange'));
    navigate('/superadmin/login');
  };

  const menuItems = [
    { path: '/superadmin', label: 'Tenants', icon: '🏢' },
    { path: '/superadmin/waitlist', label: 'Waitlist', icon: '📋' },
    { path: '/superadmin/feedback', label: 'Feedback', icon: '💬' },
    { path: '/superadmin/invites', label: 'Invites', icon: '🎫' },
  ];

  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  };

  const isActive = (path: string) => {
    if (path === '/superadmin') {
      return location.pathname === '/superadmin';
    }
    return location.pathname.startsWith(path);
  };

  return (
    <div className="flex h-screen overflow-hidden bg-gray-100">
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside className={`fixed left-0 top-0 z-50 flex h-screen w-64 flex-col overflow-y-hidden bg-gray-900 duration-300 ease-linear lg:static lg:translate-x-0 ${
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      }`}>
        <div className="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
          <Link to="/superadmin" className="text-xl font-bold text-white">
            SuperAdmin
          </Link>
          <button
            onClick={() => setSidebarOpen(false)}
            className="lg:hidden text-white hover:text-gray-300"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div className="flex flex-col overflow-y-auto duration-300 ease-linear">
          <nav className="mt-5 px-4 py-4 lg:mt-9 lg:px-6">
            <ul className="mb-6 flex flex-col gap-1.5">
              {menuItems.map((item) => (
                <li key={item.path}>
                  <Link
                    to={item.path}
                    className={`block px-4 py-2 rounded transition-colors ${
                      isActive(item.path)
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-300 hover:bg-gray-800'
                    }`}
                  >
                    <span className="mr-2">{item.icon}</span>
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
        </div>
      </aside>

      <div className="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
        <header className="sticky top-0 z-30 flex w-full bg-white border-b border-gray-200 drop-shadow-sm">
          <div className="flex flex-grow items-center justify-between px-4 py-4 md:px-6 2xl:px-11">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100"
              aria-label="Toggle menu"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <div className="flex items-center gap-3 ml-auto">
              {user && (
                <div className="flex items-center gap-3">
                  <div className="text-right hidden sm:block">
                    <span className="block text-sm font-medium text-gray-900">{user.name}</span>
                    <span className="block text-xs text-gray-500">{user.email}</span>
                  </div>
                  <div className="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold">
                    {user ? getInitials(user.name) : '?'}
                  </div>
                  <button
                    onClick={handleLogout}
                    className="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded transition-colors"
                  >
                    Sair
                  </button>
                </div>
              )}
            </div>
          </div>
        </header>

        <main className="bg-gray-100">
          <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default SuperAdminLayout;
