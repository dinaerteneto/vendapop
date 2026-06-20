import { test, expect } from '@playwright/test';
import { getOrCreateTestTenant } from './fixtures/api';

const DASHBOARD_URL = '/admin';
const SUBSCRIPTION_API = '**/api/admin/subscription';

function buildSubscriptionResponse(overrides: Partial<{
  plan_type: string;
  max_products: number | null;
  current_products: number;
}> = {}) {
  const plan_type = overrides.plan_type ?? 'free';
  const max_products = overrides.max_products ?? 6;
  const current_products = overrides.current_products ?? 5;

  return {
    plan_type,
    plan_status: 'active',
    is_active: true,
    limits: {
      max_products,
      max_categories: 3,
      current_products,
      current_categories: 1,
      can_add_product: current_products < (max_products ?? Infinity),
      can_add_category: true,
    },
  };
}

test.describe('Limit Banner', () => {
  let token: string;

  test.beforeAll(async ({ request }) => {
    const tenant = await getOrCreateTestTenant(request);
    token = tenant.token;
  });

  test.beforeEach(async ({ page }) => {
    await page.addInitScript((t: string) => {
      window.localStorage.setItem('admin_token', t);
      window.localStorage.setItem('tenant_slug', 'e2e-test');
      window.localStorage.setItem('tenant', JSON.stringify({
        slug: 'e2e-test',
        onboarding_completed: true,
        onboarding_step: 0,
      }));
      window.localStorage.removeItem('limit_banner_dismissed_at');
    }, token);
  });

  test('banner is visible when products_used = 5 and max_products = 6 (1 away)', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano free')).toBeVisible();
    await expect(page.getByText('(5/6 produtos)')).toBeVisible();
  });

  test('banner is visible when products_used = 6 and max_products = 6 (at limit)', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 6,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano free')).toBeVisible();
    await expect(page.getByText('(6/6 produtos)')).toBeVisible();
  });

  test('banner is NOT visible when products_used = 4 and max_products = 6', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 4,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano')).not.toBeVisible();
  });

  test('banner is NOT visible when max_products = null (premium)', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'premium',
          max_products: null,
          current_products: 999,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano')).not.toBeVisible();
  });

  test('banner is NOT visible when dismissed within 30 days', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    // Dismiss now
    const now = Date.now();
    await page.addInitScript((t: string) => {
      window.localStorage.setItem('limit_banner_dismissed_at', String(t));
    }, String(now));

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano')).not.toBeVisible();
  });

  test('banner IS visible when dismissed more than 30 days ago', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    // Dismiss 31 days ago
    const thirtyOneDaysAgo = Date.now() - 31 * 24 * 60 * 60 * 1000;
    await page.addInitScript((t: string) => {
      window.localStorage.setItem('limit_banner_dismissed_at', String(t));
    }, String(thirtyOneDaysAgo));

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano free')).toBeVisible();
  });

  test('dismiss button hides the banner and stores dismissal timestamp', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano free')).toBeVisible();

    // Click dismiss
    await page.click('button[aria-label="Dispensar aviso"]');

    // Banner should disappear
    await expect(page.getByText('Você está quase no limite do plano free')).not.toBeVisible();

    // localStorage should have the timestamp
    const stored = await page.evaluate(() => localStorage.getItem('limit_banner_dismissed_at'));
    expect(stored).not.toBeNull();
    expect(Number(stored)).toBeGreaterThan(0);
  });

  test('CTA "Ver planos" links to /admin/planos', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);

    const cta = page.getByText('Ver planos');
    await expect(cta).toBeVisible();
    await expect(cta).toHaveAttribute('href', '/admin/planos');
  });

  test('banner text includes plan_type, current, and limit values', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'basic',
          max_products: 30,
          current_products: 29,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano basic')).toBeVisible();
    await expect(page.getByText('(29/30 produtos)')).toBeVisible();
  });

  test('GA4 event limit_warning_shown fires on banner mount', async ({ page }) => {
    const events: unknown[] = [];

    await page.exposeFunction('gtagCollect', (event: string, action: string, payload?: Record<string, unknown>) => {
      if (action === 'limit_warning_shown') {
        events.push({ event, action, payload });
      }
    });

    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 5,
        })),
      });
    });

    await page.addInitScript(() => {
      (window as any).gtag = function (...args: unknown[]) {
        const event = new CustomEvent('gtag-event', { detail: args });
        window.dispatchEvent(event);
      };
    });

    await page.goto(DASHBOARD_URL);

    // Listen for the custom event
    const gtagCalled = await page.evaluate(() => {
      return new Promise<boolean>((resolve) => {
        const timeout = setTimeout(() => resolve(false), 3000);
        window.addEventListener('gtag-event', () => {
          clearTimeout(timeout);
          resolve(true);
        });
      });
    });

    expect(gtagCalled).toBe(true);
  });

  test('dashboard with 4/6 products does NOT show limit banner (free tier)', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'free',
          max_products: 6,
          current_products: 4,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano')).not.toBeVisible();
  });

  test('dashboard with 30/30 products shows limit banner (basic tier)', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'basic',
          max_products: 30,
          current_products: 30,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano basic')).toBeVisible();
    await expect(page.getByText('(30/30 produtos)')).toBeVisible();
  });

  test('dashboard with unlimited plan (premium) does NOT show limit banner', async ({ page }) => {
    await page.route(SUBSCRIPTION_API, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(buildSubscriptionResponse({
          plan_type: 'premium',
          max_products: null,
          current_products: 100,
        })),
      });
    });

    await page.goto(DASHBOARD_URL);
    await expect(page.getByText('Você está quase no limite do plano')).not.toBeVisible();
  });
});
