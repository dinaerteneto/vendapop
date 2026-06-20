# Release Notes

---

## v1.14.3 — 2026-06-20

### Correções

- **Planos**: alinha definição de planos entre landing page, painel admin e backend conforme `docs/estrategia-monetizacao.md`. Estrutura correta: Grátis, Básico (R$29,90), Profissional (R$59,90), Premium (R$99,90).
- **MercadoPago**: chaves de pricing corrigidas para `professional_monthly/annual` e `premium_monthly/annual` — elimina erro "The selected plan type is invalid" no checkout.
- **SubscriptionController**: validação `plan_type` atualizada para `in:basic,professional,premium` e `planOrder` correto.
- **plan-limits.php**: substituído plano `pro` por `professional` (100 produtos) e `premium` (ilimitado + domínio próprio).
- **Banco de dados**: removida linha `pro` órfã da tabela `plan_limits`; adicionadas `professional` e `premium` via seeder.

### Deploy

```bash
php artisan db:seed --class=PlanLimitsSeeder
```

---

## v1.14.0 — 2026-06-20

**Lançamento beta fechado**

### Novas funcionalidades

- **Sistema de vagas**: controle de vagas disponíveis para cadastro com reposição automática toda segunda-feira. Lojistas entram em lista de espera quando vagas esgotam e recebem e-mail quando novas vagas são liberadas.
- **Gateway de pagamento MercadoPago**: integração completa com checkout via redirect, recebimento de webhook, confirmação de assinatura e tratamento de falhas. Tela de sucesso e erro pós-checkout implementadas.
- **Enforcement de limites de plano**: middleware verifica limite de produtos antes de criar/atualizar. Retorna 402 com dados do plano para exibição do modal de upgrade. Planos: free (6), basic (30), pro (ilimitado).
- **Emails de trial automatizados**: sequência de 6 e-mails disparados automaticamente (D+0 boas-vindas, D+7 case study, D+15 dicas, D+30 lembrete, D+40 urgente, D+45 encerramento).
- **Google Analytics 4**: script injetado no build de produção via `VITE_GA_MEASUREMENT_ID`. Não carrega no painel `/admin`. Eventos rastreados: `signup`, `waitlist_signup`, `spot_view`, `limit_reached`, `begin_checkout`, `purchase`, `upgrade_modal_viewed`, `upgrade_modal_cta_clicked`, `limit_warning_shown`.

### Correções

- **Onboarding**: `StepIdentidade` sempre envia FormData (independente de upload de arquivo). `StepVitrine` migrado para UUID e com edição de preço inline com máscara BRL.
- **Dashboard**: banner de trial corrigido para usar os campos corretos da API (`ends_at`, `plan_status`). Labels de status de assinatura corrigidos.
- **Produtos**: máscara BRL unificada entre onboarding e formulário de produto. `ProductController` corrigido para decodificar JSON em payload multipart.
- **Logo/favicon**: `IconController` retorna `logo_url` quando disponível em vez de sempre gerar iniciais. URLs convertidas para HTTPS apenas em produção.
- **Planos admin**: middleware `CheckPlanLimits` corrigido para buscar tenant via `$request->user()->tenant` quando variável de contexto não disponível.

### Documentação

- Guia de configuração MercadoPago (sandbox e produção)
- Guia de configuração Google Analytics 4 (eventos, conversões, UTMs, Google Ads)
- Checklist de teste manual para o beta

### Migrations desta versão

```
2026_06_19_094932_create_trial_emails_sent_table
2026_06_19_095000_create_plan_limits_table
2026_06_19_095000_create_spot_batches_table
2026_06_19_095001_create_payment_transactions_table
2026_06_19_095002_add_is_pending_to_subscriptions_table
2026_06_19_095003_add_payment_transaction_id_to_subscriptions_table
```

### Seeders necessários no deploy

```bash
php artisan db:seed --class=PlanLimitsSeeder
php artisan db:seed --class=SpotBatchesSeeder
```

### Variáveis de ambiente novas

**Backend (`.env.production`):**
```env
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_PUBLIC_KEY=
MERCADOPAGO_CLIENT_ID=
MERCADOPAGO_CLIENT_SECRET=
MERCADOPAGO_WEBHOOK_SECRET=
```

**Frontend (`frontend/.env.production` — não versionado):**
```env
VITE_GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

---

## v1.13.1 — anterior

Ver histórico git: `git log v1.13.1 --oneline`
