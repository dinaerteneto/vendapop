import { BrowserRouter as Router, Routes, Route, Navigate, useParams } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

// Layouts
import PublicLayout from './layout/PublicLayout';
import AppLayout from './layout/AppLayout';
import SuperAdminLayout from './layout/SuperAdminLayout';

// Auth
import ProtectedSuperAdminRoute from './components/auth/ProtectedSuperAdminRoute';

// Pages Admin
import SignIn from './pages/AuthPages/SignIn';
import Register from './pages/AuthPages/Register';
import ForgotPassword from './pages/AuthPages/ForgotPassword';
import ResetPassword from './pages/AuthPages/ResetPassword';
import VerifyEmail from './pages/AuthPages/VerifyEmail';
import GoogleCallback from './pages/AuthPages/GoogleCallback';
import GoogleOnboarding from './pages/AuthPages/GoogleOnboarding';
import MagicLogin from './pages/AuthPages/MagicLogin';
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
import OnboardingSetup from './pages/AuthPages/OnboardingSetup';
import PlanosPage from './pages/Dashboard/Plans/PlanosPage';
import CheckoutSuccess from './pages/Dashboard/Plans/CheckoutSuccess';
import CheckoutError from './pages/Dashboard/Plans/CheckoutError';

// Pages SuperAdmin
import SuperAdminLogin from './pages/SuperAdmin/SuperAdminLogin';
import TenantList from './pages/SuperAdmin/TenantList';
import TenantDetail from './pages/SuperAdmin/TenantDetail';
import Waitlist from './pages/SuperAdmin/Waitlist';
import FeedbackInbox from './pages/SuperAdmin/FeedbackInbox';
import InviteList from './pages/SuperAdmin/InviteList';

// Pages Shop
import ProductList from './pages/Shop/ProductList';
import ProductDetail from './pages/Shop/ProductDetail';
import Cart from './pages/Shop/Cart';
import Checkout from './pages/Shop/Checkout';
import OrderTracking from './pages/Shop/OrderTracking';

// Legal Pages
import PrivacyPolicyPage from './pages/legal/PrivacyPolicyPage';
import TermsOfServicePage from './pages/legal/TermsOfServicePage';
import CookiePolicyPage from './pages/legal/CookiePolicyPage';
import LgpdRightsPage from './pages/legal/LgpdRightsPage';

// Landing Page
import Landing from './pages/Landing';

// Proteção de Rota Admin
const ProtectedRoute = ({ children }: { children: JSX.Element }) => {
  const token = localStorage.getItem('admin_token');
  if (!token) return <Navigate to="/admin/login" replace />;
  return children;
};

const InviteRedirect: React.FC = () => {
  const { code } = useParams<{ code: string }>();
  return <Navigate to={`/admin/register?invite=${code}`} replace />;
};

function App() {
  return (
    <Router>
      <ToastContainer position="top-right" autoClose={3000} />
      <Routes>
        {/* Legal Pages */}
        <Route path="/privacidade" element={<PrivacyPolicyPage />} />
        <Route path="/termos" element={<TermsOfServicePage />} />
        <Route path="/cookies" element={<CookiePolicyPage />} />
        <Route path="/direitos-lgpd" element={<LgpdRightsPage />} />
        <Route path="/convite/:code" element={<InviteRedirect />} />
        <Route path="/convite/:code" element={<Navigate to={window.location.pathname.replace('/convite/', '/admin/register?invite=')} replace />} />

        {/* Landing Page */}
        <Route path="/" element={<Landing />} />

        {/* Rotas Admin */}
        <Route path="/admin/login" element={<SignIn />} />
        <Route path="/admin/register" element={<Register />} />
        <Route path="/admin/forgot-password" element={<ForgotPassword />} />
        <Route path="/admin/reset-password" element={<ResetPassword />} />
        <Route path="/admin/verify-email" element={<VerifyEmail />} />
        <Route path="/admin/auth/google/callback" element={<GoogleCallback />} />
        <Route path="/admin/onboarding" element={<GoogleOnboarding />} />
        <Route path="/admin/magic-login" element={<MagicLogin />} />
        
        <Route path="/admin" element={
            <ProtectedRoute>
                <AppLayout />
            </ProtectedRoute>
        }>
            <Route index element={<ECommerce />} />
            <Route path="setup" element={<OnboardingSetup />} />
            <Route path="products" element={<AdminProductList />} />
            <Route path="products/new" element={<ProductForm />} />
            <Route path="products/:id" element={<ProductForm />} />
            
            <Route path="categories" element={<CategoryList />} />
            <Route path="categories/new" element={<CategoryForm />} />
            <Route path="categories/:id" element={<CategoryForm />} />

            <Route path="orders" element={<OrderList />} />
            <Route path="orders/:id" element={<OrderDetail />} />
            
            <Route path="customers" element={<CustomerList />} />
            <Route path="customers/:id" element={<CustomerForm />} />
            
            <Route path="store-settings" element={<StoreSettings />} />
            <Route path="change-password" element={<ChangePassword />} />
            
            <Route path="banners" element={<BannerList />} />
            <Route path="banners/new" element={<BannerForm />} />
            <Route path="banners/:id" element={<BannerForm />} />

            <Route path="planos" element={<PlanosPage />} />
            <Route path="planos/sucesso" element={<CheckoutSuccess />} />
            <Route path="planos/erro" element={<CheckoutError />} />
        </Route>

        {/* Rotas SuperAdmin */}
        <Route path="/superadmin/login" element={<SuperAdminLogin />} />
        <Route path="/superadmin" element={
            <ProtectedSuperAdminRoute>
                <SuperAdminLayout />
            </ProtectedSuperAdminRoute>
        }>
            <Route index element={<TenantList />} />
            <Route path="tenants/:id" element={<TenantDetail />} />
            <Route path="waitlist" element={<Waitlist />} />
            <Route path="feedback" element={<FeedbackInbox />} />
            <Route path="invites" element={<InviteList />} />
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
