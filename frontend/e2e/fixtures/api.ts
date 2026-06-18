const API_BASE = process.env.API_BASE_URL || 'http://backend:8000/api';

interface TestTenant {
  slug: string;
  email: string;
  password: string;
  token: string;
}

let cachedTenant: TestTenant | null = null;

export async function getOrCreateTestTenant(request: any): Promise<TestTenant> {
  if (cachedTenant) return cachedTenant;

  const slug = `e2e-test-${Date.now()}`;
  const email = `${slug}@test.com`;
  const password = 'Test123456!';

  const registerRes = await request.post(`${API_BASE}/admin/register`, {
    data: {
      store_name: 'E2E Test Store',
      store_slug: slug,
      whatsapp_number: '5511999999999',
      email,
      terms_accepted: true,
      password,
    },
  });

  if (!registerRes.ok()) {
    const body = await registerRes.json();
    throw new Error(`Registration failed: ${JSON.stringify(body)}`);
  }

  const loginRes = await request.post(`${API_BASE}/admin/login`, {
    data: { email, password },
  });

  if (!loginRes.ok()) {
    throw new Error('Login failed after registration');
  }

  const body = await loginRes.json();

  cachedTenant = { slug, email, password, token: body.token };
  return cachedTenant;
}

export async function apiContext(page: any) {
  const tenant = await getOrCreateTestTenant(page.request);
  return {
    token: tenant.token,
    slug: tenant.slug,
  };
}
