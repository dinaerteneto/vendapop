import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

// Layouts
import PublicLayout from './layout/PublicLayout';
import AppLayout from './layout/AppLayout';

// Pages Admin
import SignIn from './pages/AuthPages/SignIn';
import Register from './pages/AuthPages/Register';
import ForgotPassword from './pages/AuthPages/ForgotPassword';
import ResetPassword from './pages/AuthPages/ResetPassword';
import VerifyEmail from './pages/AuthPages/VerifyEmail';
import ECommerce from './pages/Dashboard/ECommerce';
import AdminProductList from './pages/Dashboard/Products/ProductList';
import ProductForm from './pages/Dashboard/Products/ProductForm';
import CategoryList from './pages/Dashboard/Categories/CategoryList';
import CategoryForm from './pages/Dashboard/Categories/CategoryForm';
import OrderList from './pages/Dashboard/Orders/OrderList';
import OrderDetail from './pages/Dashboard/Orders/OrderDetail';
import CustomerList from './pages/Dashboard/Customers/CustomerList';
import CustomerForm from './pages/Dashboard/Customers/CustomerForm';
import StoreSettings from './pages/Dashboard/StoreSettings/StoreSettings';
import ChangePassword from './pages/Dashboard/ChangePassword/ChangePassword';
import BannerList from './pages/Dashboard/Banners/BannerList';
import BannerForm from './pages/Dashboard/Banners/BannerForm';
import AttributeList from './pages/Dashboard/Attributes/AttributeList';

// Pages Shop
import ProductList from './pages/Shop/ProductList';
import ProductDetail from './pages/Shop/ProductDetail';
import Cart from './pages/Shop/Cart';
import Checkout from './pages/Shop/Checkout';
import OrderTracking from './pages/Shop/OrderTracking';

// Landing Page
import Landing from './pages/Landing';

// Proteção de Rota Admin
const ProtectedRoute = ({ children }: { children: JSX.Element }) => {
  const token = localStorage.getItem('admin_token');
  if (!token) return <Navigate to="/admin/login" replace />;
  return children;
};

function App() {
  return (
    <Router>
      <ToastContainer position="top-right" autoClose={3000} />
      <Routes>
        {/* Landing Page */}
        <Route path="/" element={<Landing />} />

        {/* Rotas Admin */}
        <Route path="/admin/login" element={<SignIn />} />
        <Route path="/admin/register" element={<Register />} />
        <Route path="/admin/forgot-password" element={<ForgotPassword />} />
        <Route path="/admin/reset-password" element={<ResetPassword />} />
        <Route path="/admin/verify-email" element={<VerifyEmail />} />
        
        <Route path="/admin" element={
            <ProtectedRoute>
                <AppLayout />
            </ProtectedRoute>
        }>
            <Route index element={<ECommerce />} />
            <Route path="products" element={<AdminProductList />} />
            <Route path="products/new" element={<ProductForm />} />
            <Route path="products/:id" element={<ProductForm />} />
            
            <Route path="categories" element={<CategoryList />} />
            <Route path="categories/new" element={<CategoryForm />} />
            <Route path="categories/:id" element={<CategoryForm />} />

            <Route path="attributes" element={<AttributeList />} />

            <Route path="orders" element={<OrderList />} />
            <Route path="orders/:id" element={<OrderDetail />} />
            
            <Route path="customers" element={<CustomerList />} />
            <Route path="customers/:id" element={<CustomerForm />} />
            
            <Route path="store-settings" element={<StoreSettings />} />
            <Route path="change-password" element={<ChangePassword />} />
            
            <Route path="banners" element={<BannerList />} />
            <Route path="banners/new" element={<BannerForm />} />
            <Route path="banners/:id" element={<BannerForm />} />
        </Route>

        {/* Rotas Públicas (Loja) */}
        <Route path="/:storeSlug" element={<PublicLayout />}>
            <Route index element={<ProductList />} />
            <Route path="product/:productSlug" element={<ProductDetail />} />
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
