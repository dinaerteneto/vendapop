# Guia de Configuracao — MercadoPago

> **Data:** 19 de Junho de 2026
> **Objetivo:** Configurar o MercadoPago como gateway de pagamento do VendaPop (checkout + assinaturas recorrentes).

---

## 1. Criar Conta no MercadoPago

### 1.1 Cadastro

1. Acesse: https://www.mercadopago.com.br
2. Clique em **"Criar conta"**
3. Preencha com os dados da empresa (CNPJ do VendaPop):
   - Razaoo social
   - CNPJ
   - E-mail: `financeiro@vendapop.com.br`
   - Telefone de contato
4. Complete a verificacao de identidade (envio de documentos)

### 1.2 Acessar o Painel de Desenvolvedor

1. Apos conta criada, acesse: https://www.mercadopago.com.br/developers/panel
2. Va em **"Suas integracoes"** > **"Criar aplicacao"**
3. Preencha:
   - Nome da aplicacao: `VendaPop`
   - Solucao a integrar: **Checkout Pro** (checkout transparente)
4. Clique em **"Criar aplicacao"**

---

## 2. Obter Credenciais

### 2.1 Credenciais de Producao

No painel da aplicacao recem-criada, va em **"Credenciais"**:

| Campo | Valor | Onde colocar |
|-------|-------|-------------|
| Access Token (Producao) | `APP_USR-XXXXXXXXXXXXX...` | `.env` → `MERCADOPAGO_ACCESS_TOKEN` |
| Public Key (Producao) | `APP_USR-XXXXXXXXXXXXX...` | `.env` → `MERCADOPAGO_PUBLIC_KEY` |
| Client ID | `XXXXXXXXXXXXX` | `.env` → `MERCADOPAGO_CLIENT_ID` |
| Client Secret | `XXXXXXXXXXXXX` | `.env` → `MERCADOPAGO_CLIENT_SECRET` |

### 2.2 Credenciais de Teste (Sandbox)

Para desenvolvimento local, use as credenciais de **teste** (mesmo local, aba "Credenciais de teste"):

| Campo | Valor |
|-------|-------|
| Access Token (Teste) | `TEST-XXXXXXXXXXXXX...` |
| Public Key (Teste) | `TEST-XXXXXXXXXXXXX...` |

**Importante:** No ambiente local (`.env`), use as credenciais de TESTE. Em producao (`.env.production`), use as de PRODUCAO.

---

## 3. Configurar `.env`

Adicionar ao `backend/.env`:

```env
# MercadoPago
PAYMENT_GATEWAY=mercadopago
MERCADOPAGO_ACCESS_TOKEN=APP_USR-XXXXXXXXXXXXX
MERCADOPAGO_PUBLIC_KEY=APP_USR-XXXXXXXXXXXXX
MERCADOPAGO_CLIENT_ID=XXXXXXXXXXXXX
MERCADOPAGO_CLIENT_SECRET=XXXXXXXXXXXXX
```

Para producao (`.env.production` na VPS):
```env
PAYMENT_GATEWAY=mercadopago
MERCADOPAGO_ACCESS_TOKEN=APP_USR-XXXXXXXXXXXXX
MERCADOPAGO_PUBLIC_KEY=APP_USR-XXXXXXXXXXXXX
MERCADOPAGO_CLIENT_ID=XXXXXXXXXXXXX
MERCADOPAGO_CLIENT_SECRET=XXXXXXXXXXXXX
```

---

## 4. Configurar Webhook (Notificacoes de Pagamento)

### 4.1 No Painel do MercadoPago

1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Va em **"Suas integracoes"** > clique na aplicacao `VendaPop`
3. Va em **"Webhooks"**
4. Clique em **"Configurar"**
5. Preencha:
   - **URL de notificacao (producao):** `https://api.vendapop.com.br/api/webhooks/payment`
   - **URL de notificacao (teste):** `https://seu-dominio-teste.com/api/webhooks/payment`
   - Eventos: selecione **"Pagos"** (payment)
6. Clique em **"Salvar"**

### 4.2 Testar o Webhook

1. No painel de desenvolvedor, va em **"Webhooks"** > **"Testar"**
2. Selecione o topico `payment`
3. Envie uma notificacao de teste
4. Verifique se o VendaPop recebeu (logs do backend)

---

## 5. Criar Planos de Assinatura no MercadoPago

O MercadoPago gerencia assinaturas via **"Planos"** (preapproval plans).

### 5.1 Acessar Planos

1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Va em **"Planos"** (menu lateral, ou dentro de "Assinaturas")

### 5.2 Criar os 3 Planos

#### Plano Basico

| Campo | Valor |
|-------|-------|
| Nome | `VendaPop Basico` |
| Preco | `R$ 29,90` |
| Frequencia | Mensal |
| Duração | Indeterminada (assina ate cancelar) |
| Trial | 0 dias (o trial de 45 dias e gerenciado pelo VendaPop) |
| Metodo de pagamento | Cartao de credito + PIX |

#### Plano Profissional

| Campo | Valor |
|-------|-------|
| Nome | `VendaPop Profissional` |
| Preco | `R$ 59,90` |
| Frequencia | Mensal |
| Duracao | Indeterminada |

#### Plano Premium

| Campo | Valor |
|-------|-------|
| Nome | `VendaPop Premium` |
| Preco | `R$ 99,90` |
| Frequencia | Mensal |
| Duração | Indeterminada |

### 5.3 Planos Anuais (Opcional — Fase 2)

| Plano | Preco anual | Desconto |
|-------|------------|----------|
| Basico Anual | `R$ 299,00` | 17% |
| Profissional Anual | `R$ 599,00` | 17% |
| Premium Anual | `R$ 999,00` | 17% |

---

## 6. Configurar no Codigo

### 6.1 Instalar SDK

```bash
cd backend
composer require mercadopago/dx-php
```

### 6.2 Verificar Config

O arquivo `config/services.php` deve conter:

```php
'payment_gateway' => env('PAYMENT_GATEWAY', 'mercadopago'),

'mercadopago' => [
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'client_id' => env('MERCADOPAGO_CLIENT_ID'),
    'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
],
```

---

## 7. Fluxo de Pagamento (Como Funciona)

```
1. Lojista clica "Assinar Basico — R$29,90/mes"
2. VendaPop backend cria Preference no MercadoPago
3. MercadoPago retorna URL de checkout (ex: https://www.mercadopago.com.br/checkout/v1/...)
4. Lojista e redirecionado para essa URL
5. Lojista paga com cartao ou PIX
6. MercadoPago redireciona de volta pro VendaPop (/admin/planos/sucesso)
7. MercadoPago envia webhook POST para /api/webhooks/payment
8. VendaPop backend valida assinatura do webhook
9. Se pagamento aprovado: ativa subscription (plan_status = active)
10. Se pagamento rejeitado: marca subscription como cancelled
```

---

## 8. Testar com Sandbox

### 8.1 Configurar Ambiente Local

```env
# .env local
PAYMENT_GATEWAY=mercadopago
MERCADOPAGO_ACCESS_TOKEN=TEST-XXXXXXXXXXXXX
MERCADOPAGO_PUBLIC_KEY=TEST-XXXXXXXXXXXXX
```

### 8.2 Cartoes de Teste

O MercadoPago fornece cartoes de teste para sandbox:

| Bandeira | Numero | Resultado |
|----------|--------|-----------|
| Mastercard | 5031 7557 3453 0604 | Aprovado |
| Visa | 4235 6477 2802 5682 | Rejeitado |
| Amex | 3753 6515 3568 0024 | Aprovado |

CVV: `123` | Validade: qualquer data futura

### 8.3 Fluxo de Teste

1. Va para `/admin/planos`
2. Clique em "Assinar Basico"
3. No checkout MercadoPago, use um cartao de teste
4. Verifique se o webhook foi recebido (logs)
5. Verifique se a subscription foi ativada no banco

---

## 9. Checklist de Producao

- [ ] Conta MercadoPago criada com CNPJ do VendaPop
- [ ] Aplicacao criada no painel de desenvolvedor
- [ ] Credenciais de producao obtidas (Access Token + Public Key)
- [ ] Planos de assinatura criados no MercadoPago (Basico, Profissional, Premium)
- [ ] Webhook configurado para URL de producao
- [ ] `.env.production` preenchido com credenciais de producao
- [ ] SSL ativo no dominio (obrigatorio para webhooks)
- [ ] Teste com transacao real de R$ 1,00 antes de abrir ao publico
- [ ] Verificar se o MercadoPago redireciona corretamente para `/admin/planos/sucesso`

---

## 10. Troubleshooting

| Problema | Causa provavel | Solucao |
|----------|---------------|---------|
| Webhook nao chega | URL inacessivel ou SSL invalido | Verificar se o dominio tem HTTPS valido. Testar com `curl` |
| Pagamento aprovado mas subscription nao ativa | Erro no webhook handler | Verificar logs do backend (`storage/logs/laravel.log`) |
| Checkout nao carrega | Access token invalido ou expirado | Regenerar token no painel do MP |
| Erro 401 no webhook | Assinatura invalida | Verificar se o `x-signature` header esta sendo validado corretamente |
| Sandbox funciona mas producao nao | Credenciais de teste em producao | Confirmar que `.env.production` usa tokens `APP_USR-` (nao `TEST-`) |

---

## Referencias

- Documentacao oficial: https://www.mercadopago.com.br/developers/pt/docs
- Checkout Pro: https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/landing
- Webhooks: https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
- Cartoes de teste: https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/additional-content/test-cards
