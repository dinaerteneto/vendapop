import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../services/api';
import { SEOHead } from '../../../components/common/SEOHead';

interface SubscriptionData {
  plan_type: string;
  status: string;
  limits: {
    max_products: number | null;
    current_products: number;
  };
  trial_ends_at?: string | null;
  next_billing_at?: string | null;
}

interface PlanFeature {
  text: string;
  included: boolean;
}

interface Plan {
  id: string;
  name: string;
  monthlyPrice: number;
  annualPrice: number;
  features: PlanFeature[];
  popular?: boolean;
}

const PLANS: Plan[] = [
  {
    id: 'free',
    name: 'Grátis',
    monthlyPrice: 0,
    annualPrice: 0,
    features: [
      { text: 'Até 6 produtos', included: true },
      { text: 'Categorias ilimitadas', included: true },
      { text: 'Pedidos ilimitados', included: true },
      { text: 'Integração WhatsApp', included: true },
      { text: 'Cobrança de taxa por pedido', included: true },
    ],
  },
  {
    id: 'basic',
    name: 'Básico',
    monthlyPrice: 29.90,
    annualPrice: 299,
    features: [
      { text: 'Até 30 produtos', included: true },
      { text: 'Categorias ilimitadas', included: true },
      { text: 'Pedidos ilimitados', included: true },
      { text: 'Integração WhatsApp', included: true },
      { text: 'Sem taxa por pedido', included: true },
      { text: 'Múltiplas imagens', included: true },
    ],
    popular: false,
  },
  {
    id: 'pro',
    name: 'Pro',
    monthlyPrice: 99.90,
    annualPrice: 999,
    features: [
      { text: 'Produtos ilimitados', included: true },
      { text: 'Categorias ilimitadas', included: true },
      { text: 'Pedidos ilimitados', included: true },
      { text: 'Integração WhatsApp', included: true },
      { text: 'Sem taxa por pedido', included: true },
      { text: 'Múltiplas imagens', included: true },
      { text: 'Variações de produto', included: true },
      { text: 'Suporte prioritário', included: true },
    ],
    popular: true,
  },
];

type BillingCycle = 'monthly' | 'annual';

const PlanosPage: React.FC = () => {
  const [subscription, setSubscription] = useState<SubscriptionData | null>(null);
  const [loading, setLoading] = useState(true);
  const [billingCycle, setBillingCycle] = useState<BillingCycle>('monthly');
  const [confirmPlan, setConfirmPlan] = useState<Plan | null>(null);
  const [checkoutLoading, setCheckoutLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();

  useEffect(() => {
    loadSubscription();
  }, []);

  const loadSubscription = async () => {
    try {
      setLoading(true);
      const { data } = await api.get('/admin/subscription');
      setSubscription(data);
    } catch (err) {
      console.error('Erro ao carregar assinatura', err);
    } finally {
      setLoading(false);
    }
  };

  const currentPlanId = subscription?.plan_type || 'free';
  const planLabelMap: Record<string, string> = {
    free: 'Grátis',
    basic: 'Básico',
    pro: 'Pro',
  };

  const formatPrice = (price: number) => {
    return price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  };

  const annualDiscount = (monthlyPrice: number, annualPrice: number) => {
    if (monthlyPrice === 0) return 0;
    const monthlyAnnual = monthlyPrice * 12;
    return Math.round((1 - annualPrice / monthlyAnnual) * 100);
  };

  const handleAssinar = (plan: Plan) => {
    setError(null);
    setConfirmPlan(plan);
  };

  const handleConfirmCheckout = async () => {
    if (!confirmPlan) return;
    setCheckoutLoading(true);
    setError(null);

    try {
      const { data } = await api.post('/admin/subscription/create', {
        plan_type: confirmPlan.id,
        billing_cycle: billingCycle,
      });

      if (typeof window.gtag === 'function') {
        window.gtag('event', 'begin_checkout', {
          plan_type: confirmPlan.id,
          billing_cycle: billingCycle,
          value: billingCycle === 'monthly' ? confirmPlan.monthlyPrice : confirmPlan.annualPrice,
          currency: 'BRL',
        });
      }

      if (data.checkout_url) {
        window.location.href = data.checkout_url;
      } else if (data.url) {
        window.location.href = data.url;
      } else {
        navigate('/admin/planos/sucesso');
      }
    } catch (err: any) {
      const status = err?.response?.status;
      const msg = err?.response?.data?.message;

      if (status === 409) {
        setError(msg || 'Você já possui uma assinatura pendente. Aguarde a confirmação ou entre em contato com o suporte.');
      } else if (status === 400) {
        setError(msg || 'Não é possível fazer downgrade. Entre em contato com o suporte.');
      } else {
        setError(msg || 'Erro ao criar checkout. Tente novamente.');
      }
    } finally {
      setCheckoutLoading(false);
    }
  };

  if (loading) {
    return (
      <div>
        <h1 className="mb-6 text-2xl font-bold text-gray-900">Planos</h1>
        <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
          {[1, 2, 3].map((i) => (
            <div key={i} className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm animate-pulse">
              <div className="h-6 bg-gray-200 rounded w-24 mb-4"></div>
              <div className="h-8 bg-gray-200 rounded w-32 mb-4"></div>
              <div className="h-4 bg-gray-200 rounded w-full mb-2"></div>
              <div className="h-4 bg-gray-200 rounded w-full mb-2"></div>
              <div className="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
              <div className="h-10 bg-gray-200 rounded w-full"></div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  const getCtaLabel = (plan: Plan) => {
    if (plan.id === currentPlanId) return 'Seu plano atual';
    const planOrder = ['free', 'basic', 'pro'];
    if (planOrder.indexOf(plan.id) < planOrder.indexOf(currentPlanId)) {
      return 'Plano anterior';
    }
    return 'Assinar';
  };

  return (
    <div>
      <SEOHead title="Planos — VendaPop" noIndex />
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Escolha seu plano</h1>
        <p className="mt-1 text-gray-600">
          {subscription
            ? `Você está no plano ${planLabelMap[currentPlanId] || currentPlanId}. Faça upgrade para desbloquear mais recursos.`
            : 'Selecione o plano ideal para o seu negócio.'}
        </p>
      </div>

      {/* Billing Toggle */}
      <div className="flex items-center justify-center gap-3 mb-8">
        <button
          onClick={() => setBillingCycle('monthly')}
          className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
            billingCycle === 'monthly'
              ? 'bg-indigo-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          }`}
        >
          Mensal
        </button>
        <button
          onClick={() => setBillingCycle('annual')}
          className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
            billingCycle === 'annual'
              ? 'bg-indigo-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          }`}
        >
          Anual
          {billingCycle === 'annual' && (
            <span className="ml-1 text-xs opacity-80">(17% off)</span>
          )}
        </button>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p className="text-red-700 text-sm">{error}</p>
        </div>
      )}

      {/* Plan Grid */}
      <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
        {PLANS.map((plan) => {
          const isCurrentPlan = plan.id === currentPlanId;
          const ctaLabel = getCtaLabel(plan);
          const canSubscribe = ctaLabel === 'Assinar';
          const price = billingCycle === 'monthly' ? plan.monthlyPrice : plan.annualPrice;

          return (
            <div
              key={plan.id}
              className={`relative rounded-lg border bg-white shadow-sm flex flex-col ${
                plan.popular ? 'border-indigo-300 ring-2 ring-indigo-200' : 'border-gray-200'
              }`}
            >
              {plan.popular && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                  Mais popular
                </span>
              )}

              {isCurrentPlan && (
                <span className="absolute top-3 right-3 bg-green-100 text-green-700 text-xs font-medium px-2 py-1 rounded-full">
                  Seu plano
                </span>
              )}

              <div className="p-6 flex flex-col flex-1">
                <h2 className="text-lg font-bold text-gray-900 mb-1">{plan.name}</h2>

                <div className="mb-4">
                  {price === 0 ? (
                    <span className="text-3xl font-bold text-gray-900">Grátis</span>
                  ) : (
                    <>
                      <span className="text-3xl font-bold text-gray-900">
                        {formatPrice(price)}
                      </span>
                      <span className="text-gray-500 ml-1 text-sm">
                        /{billingCycle === 'monthly' ? 'mês' : 'ano'}
                      </span>
                      {billingCycle === 'annual' && plan.monthlyPrice > 0 && (
                        <div className="text-xs text-green-600 mt-1">
                          Economia de {annualDiscount(plan.monthlyPrice, plan.annualPrice)}%
                        </div>
                      )}
                    </>
                  )}
                </div>

                <ul className="space-y-2 mb-6 flex-1">
                  {plan.features.map((feature, idx) => (
                    <li key={idx} className="flex items-start gap-2 text-sm text-gray-600">
                      {feature.included ? (
                        <svg className="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                      ) : (
                        <svg className="w-5 h-5 text-gray-300 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      )}
                      {feature.text}
                    </li>
                  ))}
                </ul>

                {isCurrentPlan ? (
                  <div className="w-full px-4 py-2.5 text-center text-sm font-medium bg-gray-100 text-gray-500 rounded-lg cursor-default">
                    Plano atual
                  </div>
                ) : canSubscribe ? (
                  <button
                    onClick={() => handleAssinar(plan)}
                    className={`w-full px-4 py-2.5 text-center text-sm font-medium rounded-lg transition ${
                      plan.popular
                        ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    Assinar
                  </button>
                ) : (
                  <div className="w-full px-4 py-2.5 text-center text-sm font-medium bg-gray-100 text-gray-400 rounded-lg cursor-default">
                    Indisponível
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>

      {/* Confirmation Modal */}
      {confirmPlan && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
            <h2 className="text-xl font-bold text-gray-800 mb-4">Confirmar assinatura</h2>

            <div className="bg-gray-50 rounded-lg p-4 mb-6">
              <div className="flex justify-between items-center mb-2">
                <span className="text-gray-600">Plano</span>
                <span className="font-bold text-gray-900">{confirmPlan.name}</span>
              </div>
              <div className="flex justify-between items-center mb-2">
                <span className="text-gray-600">Ciclo</span>
                <span className="font-medium text-gray-900">
                  {billingCycle === 'monthly' ? 'Mensal' : 'Anual'}
                </span>
              </div>
              <div className="flex justify-between items-center pt-2 border-t border-gray-200">
                <span className="text-gray-600">Valor</span>
                <span className="text-xl font-bold text-indigo-600">
                  {formatPrice(billingCycle === 'monthly' ? confirmPlan.monthlyPrice : confirmPlan.annualPrice)}
                  <span className="text-sm font-normal text-gray-500 ml-1">
                    /{billingCycle === 'monthly' ? 'mês' : 'ano'}
                  </span>
                </span>
              </div>
            </div>

            <div className="mb-6">
              <h3 className="text-sm font-medium text-gray-700 mb-2">Recursos incluídos:</h3>
              <ul className="space-y-1">
                {confirmPlan.features.map((feature, idx) => (
                  <li key={idx} className="text-sm text-gray-600 flex items-center gap-2">
                    <svg className="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                    {feature.text}
                  </li>
                ))}
              </ul>
            </div>

            {error && (
              <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p className="text-red-700 text-sm">{error}</p>
              </div>
            )}

            <div className="flex flex-col gap-3">
              <button
                onClick={handleConfirmCheckout}
                disabled={checkoutLoading}
                className="w-full px-4 py-3 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                {checkoutLoading ? (
                  <>
                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Redirecionando...
                  </>
                ) : (
                  `Ir para pagamento — ${formatPrice(billingCycle === 'monthly' ? confirmPlan.monthlyPrice : confirmPlan.annualPrice)}`
                )}
              </button>
              <button
                onClick={() => {
                  setConfirmPlan(null);
                  setError(null);
                }}
                disabled={checkoutLoading}
                className="w-full px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition text-sm disabled:opacity-50"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PlanosPage;
