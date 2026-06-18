import { test, expect } from '@playwright/test';
import { getOrCreateTestTenant } from './fixtures/api';

test.describe('Onboarding Wizard', () => {
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
        onboarding_completed: false,
        onboarding_step: 0,
      }));
    }, token);
  });

  test('wizard opens and shows progress bar', async ({ page }) => {
    await page.goto('/admin/setup');

    await expect(page.getByText('Identidade', { exact: true })).toBeVisible();
    await expect(page.getByText('Vitrine', { exact: true })).toBeVisible();
    await expect(page.getByText('Compartilhar', { exact: true })).toBeVisible();

    // Step 1 is active
    const activeStep = page.locator('[data-active="true"]');
    await expect(activeStep).toContainText('Identidade');
  });

  test('wizard progress bar highlights current step', async ({ page }) => {
    await page.goto('/admin/setup');

    // Step 1 should be active (purple circle)
    const step1 = page.locator('[data-active="true"]');
    await expect(step1).toContainText('Identidade');

    // Verify 4 steps exist
    await expect(page.getByText('Identidade', { exact: true })).toBeVisible();
    await expect(page.getByText('Vitrine', { exact: true })).toBeVisible();
    await expect(page.getByText('WhatsApp', { exact: true })).toBeVisible();
    await expect(page.getByText('Compartilhar', { exact: true })).toBeVisible();
  });

  test('step 1 shows identity form', async ({ page }) => {
    await page.goto('/admin/setup');

    await expect(page.getByTestId('step-identidade')).toBeVisible();
    await expect(page.getByText('Como é a identidade da sua loja?')).toBeVisible();

    // Color chips are visible
    await expect(page.getByTitle('Roxo')).toBeVisible();
    await expect(page.getByTitle('Azul')).toBeVisible();
    await expect(page.getByTitle('Verde')).toBeVisible();

    // Skip and Next buttons
    await expect(page.getByText('Pular')).toBeVisible();
    await expect(page.getByText('Próximo →')).toBeVisible();
  });

  test('skip advances to next step', async ({ page }) => {
    await page.goto('/admin/setup');

    await page.click('text=Pular');
    await expect(page.getByTestId('step-vitrine')).toBeVisible();
    await expect(page.getByText('Personalize sua vitrine')).toBeVisible();
  });

  test('step 4 shows sharing and confetti', async ({ page }) => {
    await page.goto('/admin/setup');

    // Skip through steps 1-3
    for (let i = 0; i < 3; i++) {
      await page.click('text=Pular');
      await page.waitForTimeout(200);
    }

    // Should be on step 4
    await expect(page.getByTestId('step-compartilhar')).toBeVisible();
    await expect(page.getByText('Sua loja está pronta!')).toBeVisible();
    await expect(page.getByText('Copiar link da loja')).toBeVisible();
    await expect(page.getByText('Ver minha loja completa')).toBeVisible();
    await expect(page.getByText('Concluir configuração')).toBeVisible();
  });

  test('login redirects to wizard when onboarding incomplete', async ({ page }) => {
    await page.addInitScript((t: string) => {
      window.localStorage.setItem('admin_token', t);
      window.localStorage.setItem('tenant', JSON.stringify({
        slug: 'test',
        onboarding_completed: false,
        onboarding_step: 0,
      }));
    }, token);

    await page.goto('/admin/setup');
    await expect(page.getByTestId('step-identidade')).toBeVisible();
  });
});
