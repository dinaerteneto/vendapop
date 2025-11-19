import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';

// Layouts
import PublicLayout from './layout/PublicLayout';
import AppLayout from './layout/AppLayout';

// Pages Admin
import SignIn from './pages/AuthPages/SignIn';
import ECommerce from './pages/Dashboard/ECommerce';
// import ProductAdmin from './pages/Dashboard/Products'; // TODO

// Pages Shop
import ProductList from './pages/Shop/ProductList';
import ProductDetail from './pages/Shop/ProductDetail';
import Cart from './pages/Shop/Cart';
import Checkout from './pages/Shop/Checkout';
import OrderTracking from './pages/Shop/OrderTracking';

// Proteção de Rota Admin
const ProtectedRoute = ({ children }: { children: JSX.Element }) => {
  const token = localStorage.getItem('admin_token');
  if (!token) return <Navigate to="/admin/login" replace />;
  return children;
};

function App() {
  return (
    <Router>
      <Routes>
        {/* Rotas Admin */}
        <Route path="/admin/login" element={<SignIn />} />
        
        <Route path="/admin" element={
            <ProtectedRoute>
                <AppLayout />
            </ProtectedRoute>
        }>
            <Route index element={<ECommerce />} />
            <Route path="products" element={<div>Gestão de Produtos (Em Breve)</div>} />
            <Route path="categories" element={<div>Gestão de Categorias (Em Breve)</div>} />
            <Route path="orders" element={<div>Gestão de Pedidos (Em Breve)</div>} />
            <Route path="store-settings" element={<div>Configurações da Loja (Em Breve)</div>} />
        </Route>

        {/* Rotas Públicas (Loja) */}
        <Route path="/:storeSlug" element={<PublicLayout />}>
            <Route index element={<ProductList />} />
            <Route path="product/:productId" element={<ProductDetail />} />
            <Route path="cart" element={<Cart />} />
            <Route path="checkout" element={<Checkout />} />
            <Route path="order/:orderUuid" element={<OrderTracking />} />
        </Route>

        {/* Rota Default */}
        <Route path="*" element={<div className="p-10 text-center">Página não encontrada. <br/> Tente acessar /nomedaloja</div>} />
      </Routes>
    </Router>
  );
}

export default App;
