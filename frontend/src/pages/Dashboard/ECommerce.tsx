import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import InvitePanel from '../../components/admin/InvitePanel';
import OnboardingBanner from '../../components/onboarding/OnboardingBanner';
import LimitBanner from '../../components/subscription/LimitBanner';

interface DashboardStats {
  sales_today: string;
  new_orders: number;
  active_products: number;
  orders_today: number;
  total_customers: number;
  sales_this_month: string;
}

interface SubscriptionData {
  plan_type: string;
  plan_status: string;
  limits: {
    max_products: number | null;
    current_products: number;
  };
  ends_at?: string | null;
  next_billing_at?: string | null;
}

const planLabel: Record<string, string> = {
  free: 'Grátis',
  basic: 'Básico',
  pro: 'Pro',
};

const statusLabel: Record<string, string> = {
  active: 'Ativo',
  trial: 'Período gratuito',
  trialing: 'Período gratuito',
  pending: 'Pendente',
  canceled: 'Cancelado',
  cancelled: 'Cancelado',
};

const statusColors: Record<string, string> = {
  active: 'bg-green-100 text-green-700',
  trial: 'bg-blue-100 text-blue-700',
  trialing: 'bg-blue-100 text-blue-700',
  pending: 'bg-amber-100 text-amber-700',
  canceled: 'bg-red-100 text-red-700',
  cancelled: 'bg-red-100 text-red-700',
};

const ECommerce: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [subscriptionData, setSubscriptionData] = useState<SubscriptionData | null>(null);
  const [loading, setLoading] = useState(true);
  const [bannerDismissed, setBannerDismissed] = useState(false);
  const [limitBannerDismissed, setLimitBannerDismissed] = useState(false);
  const [trialBannerDismissed, setTrialBannerDismissed] = useState(false);
  const navigate = useNavigate();

  const BANNER_DISMISS_KEY = 'onboarding_banner_dismissed_at';
  const LIMIT_BANNER_DISMISS_KEY = 'limit_banner_dismissed_at';
  const TRIAL_BANNER_DISMISS_KEY = 'trial_banner_dismissed_at';

  const onboardingStep = useMemo(() => {
    const saved = localStorage.getItem('onboarding_step');
    return saved ? parseInt(saved, 10) : 0;
  }, []);

  const showBanner = useMemo(() => {
    const tenantStr = localStorage.getItem('tenant');
    if (!tenantStr) return false;
    try {
      const tenant = JSON.parse(tenantStr);
      if (tenant.onboarding_completed) return false;
    } catch { return false; }

    const dismissed = localStorage.getItem(BANNER_DISMISS_KEY);
    if (!dismissed) return true;
    const daysAgo = (Date.now() - Number(dismissed)) / (1000 * 60 * 60 * 24);
    return daysAgo >= 30;
  }, []);

  const shouldShowLimitBanner = useMemo(() => {
    if (!subscriptionData) return false;
    const { max_products, current_products } = subscriptionData.limits;
    if (max_products === null) return false;

    if (current_products < max_products - 1) return false;

    const dismissed = localStorage.getItem(LIMIT_BANNER_DISMISS_KEY);
    if (dismissed) {
      const daysAgo = (Date.now() - Number(dismissed)) / (1000 * 60 * 60 * 24);
      if (daysAgo < 30) return false;
    }

    return true;
  }, [subscriptionData]);

  const trialDaysRemaining = useMemo(() => {
    if (!subscriptionData?.ends_at) return null;
    if (subscriptionData.plan_status !== 'trial' && subscriptionData.plan_status !== 'trialing') return null;
    const now = new Date();
    const trialEnd = new Date(subscriptionData.ends_at);
    const diff = trialEnd.getTime() - now.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
  }, [subscriptionData]);

  const shouldShowTrialBanner = useMemo(() => {
    if (trialDaysRemaining === null || trialDaysRemaining > 7) return false;
    if (trialBannerDismissed) return false;
    const dismissed = localStorage.getItem(TRIAL_BANNER_DISMISS_KEY);
    if (dismissed) {
      const daysAgo = (Date.now() - Number(dismissed)) / (1000 * 60 * 60 * 24);
      if (daysAgo < 7) return false;
    }
    return true;
  }, [trialDaysRemaining, trialBannerDismissed]);

  const handleDismissBanner = () => {
    localStorage.setItem(BANNER_DISMISS_KEY, String(Date.now()));
    setBannerDismissed(true);
  };

  const handleDismissLimitBanner = useCallback(() => {
    localStorage.setItem(LIMIT_BANNER_DISMISS_KEY, String(Date.now()));
    setLimitBannerDismissed(true);
  }, []);

  const handleDismissTrialBanner = useCallback(() => {
    localStorage.setItem(TRIAL_BANNER_DISMISS_KEY, String(Date.now()));
    setTrialBannerDismissed(true);
  }, []);

  useEffect(() => {
    if (shouldShowLimitBanner && typeof window.gtag === 'function') {
      window.gtag('event', 'limit_warning_shown', {
        plan_type: subscriptionData!.plan_type,
        products_used: subscriptionData!.limits.current_products,
        product_limit: subscriptionData!.limits.max_products,
      });
    }
  }, [shouldShowLimitBanner, subscriptionData]);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [dashboardRes, subscriptionRes] = await Promise.all([
        api.get('/admin/dashboard'),
        api.get('/admin/subscription'),
      ]);
      setStats(dashboardRes.data);
      setSubscriptionData(subscriptionRes.data);
    } catch (error) {
      console.error('Erro ao carregar dados do dashboard', error);
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (value: string) => {
    return parseFloat(value).toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    });
  };

  if (loading) {
    return (
      <div>
        <h2 className="mb-4 text-2xl font-bold text-gray-900">Dashboard</h2>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
          {[1, 2, 3].map((i) => (
            <div key={i} className="rounded-lg border border-gray-200 bg-white px-7.5 py-6 shadow-sm animate-pulse">
              <div className="h-8 bg-gray-200 rounded w-24 mb-2"></div>
              <div className="h-4 bg-gray-200 rounded w-32"></div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div>
      <SEOHead title="Dashboard — VendaPop" noIndex />
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Dashboard</h2>

        {subscriptionData && (
          <div className="flex items-center gap-3">
            <span className={`px-3 py-1 rounded-full text-xs font-medium ${statusColors[subscriptionData.plan_status] || 'bg-gray-100 text-gray-600'}`}>
              {statusLabel[subscriptionData.plan_status] || subscriptionData.plan_status}
            </span>
            <span className="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
              {planLabel[subscriptionData.plan_type] || subscriptionData.plan_type}
            </span>
            <button
              onClick={() => navigate('/admin/planos')}
              className="text-xs text-indigo-600 hover:text-indigo-800 underline"
            >
              Gerenciar
            </button>
          </div>
        )}
      </div>

      {showBanner && !bannerDismissed && (
        <OnboardingBanner
          step={onboardingStep}
          onContinue={() => navigate('/admin/setup')}
          onDismiss={handleDismissBanner}
        />
      )}

      {shouldShowTrialBanner && (
        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between mb-6">
          <div>
            <p className="font-medium text-blue-800">
              Seu período gratuito termina em {trialDaysRemaining} {trialDaysRemaining === 1 ? 'dia' : 'dias'}. Assine agora.
            </p>
            <p className="text-sm text-blue-700">
              Faça upgrade para não perder acesso aos recursos da sua loja.
            </p>
          </div>
          <div className="flex gap-2 shrink-0">
            <a
              href="/admin/planos"
              className="inline-block bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition"
            >
              Ver planos
            </a>
            <button
              onClick={handleDismissTrialBanner}
              className="text-blue-600 hover:text-blue-800 text-sm px-2"
              aria-label="Dispensar aviso"
            >
              ✕
            </button>
          </div>
        </div>
      )}

      {shouldShowLimitBanner && !limitBannerDismissed && (
        <LimitBanner
          planType={subscriptionData!.plan_type}
          current={subscriptionData!.limits.current_products}
          limit={subscriptionData!.limits.max_products!}
          onDismiss={handleDismissLimitBanner}
        />
      )}
      
      <div className="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Vendas Hoje</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats ? formatCurrency(stats.sales_today) : 'R$ 0,00'}
              </h4>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Pedidos Novos</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats?.new_orders || 0}
              </h4>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>

        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Produtos Ativos</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats?.active_products || 0}
              </h4>
            </div>
            <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Pedidos Hoje</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats?.orders_today || 0}
              </h4>
            </div>
            <div className="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>

        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Total de Clientes</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats?.total_customers || 0}
              </h4>
            </div>
            <div className="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div className="rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:shadow-md transition-shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500 mb-1">Vendas do Mês</p>
              <h4 className="text-2xl font-bold text-gray-900">
                {stats ? formatCurrency(stats.sales_this_month) : 'R$ 0,00'}
              </h4>
            </div>
            <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
              <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div className="mb-6">
        <InvitePanel />
      </div>
    </div>
  );
};

export default ECommerce;
