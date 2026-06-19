# Configuração de Integrações — VendaPop

Guia rápido para configurar as integrações externas necessárias para o lançamento beta.

---

## 1. MercadoPago (Gateway de Pagamento)

> Documentação completa: [configuracao-mercadopago.md](./configuracao-mercadopago.md)

### Variáveis obrigatórias no `backend/.env`

```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxxxxxxxxxxxxxxxxxx
MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxxxxxxxxxxxxxxxxxx
MERCADOPAGO_CLIENT_ID=xxxxxxxxxxxx
MERCADOPAGO_CLIENT_SECRET=xxxxxxxxxxxx
```

### Onde obter

1. Acesse [mercadopago.com.br/developers](https://mercadopago.com.br/developers)
2. Selecione sua aplicação (ou crie uma)
3. Vá em **Credenciais** → escolha **Teste** (sandbox) ou **Produção**
4. Copie os 4 valores acima

### Após configurar

```bash
docker compose exec -u www-data backend php artisan config:clear
```

### Teste rápido

```bash
curl -s -X POST http://localhost:8000/api/admin/subscription/create \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"plan_type": "basic", "billing_cycle": "monthly"}' | jq .
# Esperado: 201 + {"checkout_url": "https://...mercadopago..."}
```

---

## 2. Google Analytics (Analytics)

> Documentação completa: [configuracao-google-analytics.md](./configuracao-google-analytics.md)

### Variável obrigatória no `frontend/.env.production`

```env
VITE_GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

### Onde obter

1. Acesse [analytics.google.com](https://analytics.google.com)
2. Crie uma propriedade GA4 para o domínio do VendaPop
3. Vá em **Administrador** → **Fluxos de dados** → **Web**
4. Copie o **ID da medição** (formato `G-XXXXXXXXXX`)

### Observação

O GA está configurado no frontend via `VITE_GA_MEASUREMENT_ID`. Em desenvolvimento local, **não é necessário** configurar — o tracking só dispara em produção (ou quando `VITE_GA_MEASUREMENT_ID` estiver definido no `.env`).

---

## 3. Checklist de Go-Live

| Item | Variável | Arquivo | Status |
|------|----------|---------|--------|
| MP Access Token (produção) | `MERCADOPAGO_ACCESS_TOKEN` | `backend/.env` | [ ] |
| MP Public Key (produção) | `MERCADOPAGO_PUBLIC_KEY` | `backend/.env` | [ ] |
| MP Client ID | `MERCADOPAGO_CLIENT_ID` | `backend/.env` | [ ] |
| MP Client Secret | `MERCADOPAGO_CLIENT_SECRET` | `backend/.env` | [ ] |
| GA4 Measurement ID | `VITE_GA_MEASUREMENT_ID` | `frontend/.env.production` | [ ] |
| Webhook MP configurado | URL: `/api/webhooks/payment/mercadopago` | Painel MP | [ ] |

---

## 4. Sandbox vs Produção

| Ambiente | MP Access Token começa com | Pagamentos reais |
|----------|---------------------------|------------------|
| Teste | `TEST-` | Não |
| Produção | `APP_USR-` | Sim |

Use credenciais de **Teste** durante o beta para não processar cobranças reais acidentalmente.
