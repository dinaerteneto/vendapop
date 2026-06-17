import React from 'react';
import { Navigate } from 'react-router-dom';

const ProtectedSuperAdminRoute: React.FC<{ children: JSX.Element }> = ({ children }) => {
  const token = localStorage.getItem('admin_token');
  const isSuperAdmin = localStorage.getItem('is_super_admin') === 'true';

  if (!token || !isSuperAdmin) {
    return <Navigate to="/superadmin/login" replace />;
  }

  return children;
};

export default ProtectedSuperAdminRoute;
