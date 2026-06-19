# Guia de Configuracao — Google Analytics (GA4)

> **Data:** 19 de Junho de 2026
> **Objetivo:** Configurar o Google Analytics 4 para rastrear visitantes, cadastros e upgrades no VendaPop. Essencial para medir o CAC (custo de aquisicao) e ROI do Google Ads.

---

## 1. Criar Conta e Propriedade no GA4

### 1.1 Acessar o Google Analytics

1. Acesse: https://analytics.google.com
2. Faca login com a conta Google da empresa: `admin@vendapop.com.br`
3. Se for o primeiro acesso, clique em **"Iniciar medicaoo"**
4. Se ja tiver outras propriedades, va em **Admin** (engrenagem no canto inferior esquerdo)

### 1.2 Criar Propriedade

1. Em **Admin** > **"Criar"** > selecione **"Propriedade"**
2. Preencha:
   - **Nome da propriedade:** `VendaPop`
   - **Fuso horario:** `(GMT-03:00) Sao Paulo`
   - **Moeda:** `BRL (R$ Real brasileiro)`
3. Clique em **"Proxima"**
4. Selecione:
   - Categoria: `Compras` ou `Negocios e servicos`
   - Porte da empresa: `Pequena`
5. Clique em **"Proxima"**
6. Selecione seus objetivos (marque todos):
   - [x] Gerar mais leads
   - [x] Aumentar vendas online
   - [x] Analisar o comportamento do usuario
7. Clique em **"Criar"**
8. Aceite os Termos de Servico

---

## 2. Criar Fluxo de Dados (Data Stream)

### 2.1 Adicionar Stream Web

1. Apos criar a propriedade, voce sera levado para **"Fluxos de dados"**
2. Clique em **"Adicionar fluxo"** > selecione **"Web"**
3. Preencha:
   - **URL do site:** `https://vendapop.com.br`
   - **Nome do fluxo:** `VendaPop (web)`
4. Clique em **"Criar fluxo"**

### 2.2 Obter o Measurement ID

Na tela de detalhes do fluxo, copie o **"ID da medicao"** (formato `G-XXXXXXXXXX`).

Voce vai precisar dele para o passo 3.

---

## 3. Configurar no Frontend do VendaPop

### 3.1 Onde Colocar o Script

O VendaPop ja tem suporte para GA4 no `index.html` (na build de producao). O Measurement ID e injetado via variavel de ambiente.

### 3.2 Configurar Variavel de Ambiente

No arquivo `.env.production` do frontend:

```env
VITE_GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

### 3.3 Verificar Injecao

O script do GA4 deve ser carregado no `<head>` de TODAS as paginas do VendaPop:

```html
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

**Importante:** O script NAO deve ser injetado no painel admin (`/admin/*`) — apenas na landing page e nas lojas publicas (vitrines).

### 3.4 Testar se Esta Funcionando

1. Acesse `https://vendapop.com.br`
2. Va no GA4 > **Relatorios** > **Tempo real**
3. Voce deve ver 1 usuario ativo (voce)
4. Navegue por algumas paginas e verifique se as visualizacoes aparecem

---

## 4. Configurar Eventos de Conversao

### 4.1 Eventos a Configurar

| Nome do Evento | Quando Dispara | Parametros |
|---------------|---------------|-----------|
| `signup` | Pos-cadastro (sucesso no registro) | `plan_type`, `has_invite`, `source` |
| `waitlist_signup` | Cadastro na lista de espera | `spots_exhausted`, `source` |
| `spot_view` | Ao carregar a landing page | `spots_remaining` |
| `begin_checkout` | Ao iniciar checkout de plano | `plan_type`, `value` |
| `purchase` | Pagamento aprovado (webhook) | `plan_type`, `value`, `currency`, `transaction_id` |
| `limit_reached` | Ao atingir limite de produtos | `plan_type`, `current`, `limit` |

### 4.2 Como Disparar Eventos no Codigo

**Exemplo — evento `signup` (no frontend, apos registro bem-sucedido):**

```javascript
// Apos resposta 201 do POST /api/admin/register
gtag('event', 'signup', {
    plan_type: 'basic',
    has_invite: true,
    source: new URLSearchParams(window.location.search).get('utm_source') || 'direct'
});
```

**Exemplo — evento `purchase` (no backend, ao processar webhook aprovado):**

```javascript
// O frontend recebe confirmacao de pagamento e dispara:
gtag('event', 'purchase', {
    plan_type: 'professional',
    value: 59.90,
    currency: 'BRL',
    transaction_id: 'sub_12345'
});
```

### 4.3 Marcar Eventos como Conversao no GA4

1. No GA4, va em **Admin** > **Eventos**
2. Clique em **"Criar evento"**
3. Para cada evento acima, crie um evento e marque como **"Conversao"**:
   - `signup`
   - `purchase`
   - `begin_checkout`
4. Isso permite medir a taxa de conversao diretamente no GA4

---

## 5. Configurar UTMs (Parametros de Campanha)

Todo link compartilhado DEVE ter UTMs para rastrear a origem do trafego.

### 5.1 Estrutura de UTMs

```
https://vendapop.com.br/?utm_source={origem}&utm_medium={meio}&utm_campaign={campanha}&utm_content={conteudo}
```

### 5.2 Exemplos por Canal

**WhatsApp (grupos de moda):**
```
https://vendapop.com.br/?utm_source=whatsapp&utm_medium=grupo&utm_campaign=beta_moda&utm_content=script_abordagem
```

**WhatsApp (grupos de imobiliaria):**
```
https://vendapop.com.br/?utm_source=whatsapp&utm_medium=grupo&utm_campaign=beta_imob
```

**Google Ads (anuncio de moda):**
```
https://vendapop.com.br/?utm_source=google&utm_medium=cpc&utm_campaign=beta_moda&utm_content=ad_1
```

**Instagram Direct:**
```
https://vendapop.com.br/?utm_source=instagram&utm_medium=dm&utm_campaign=beta_{setor}
```

**Email marketing (trial):**
```
https://vendapop.com.br/admin/planos?utm_source=email&utm_medium=trial&utm_campaign=day_30
```

### 5.3 Verificar UTMs no GA4

1. Acesse o site com UTMs (ex: `?utm_source=whatsapp&utm_medium=grupo`)
2. Va em GA4 > **Relatorios** > **Aquisicaoo** > **Aquisicaoo de trafego**
3. Filtre por "Origem/Midia" e veja `whatsapp / grupo` aparecendo

---

## 6. Vincular Google Ads ao GA4

### 6.1 Criar Vinculo

1. No GA4, va em **Admin** > **Vinculos com outros produtos** > **Google Ads**
2. Clique em **"Vincular"**
3. Selecione a conta do Google Ads do VendaPop
4. Ative a opcao **"Ativar dados de relatorio de atribuicao"**
5. Clique em **"Salvar"**

### 6.2 Importar Converses do GA4 para o Google Ads

1. No Google Ads, va em **Ferramentas** > **Medicoo** > **Converses**
2. Clique em **"Nova acao de conversao"** > **"Importar"** > **"Google Analytics 4"**
3. Selecione os eventos de conversao (`signup`, `purchase`, `begin_checkout`)
4. Clique em **"Salvar"**

Com isso, o Google Ads sabe exatamente quantos cadastros e vendas cada anuncio gerou. O CAC e calculado automaticamente.

---

## 7. Criar Audiencias de Remarketing

### 7.1 Audiencias Uteis

| Audiencia | Gatilho | Uso |
|-----------|---------|-----|
| Visitantes da landing (7 dias) | Visitou `/` | Remarketing: anuncio com case real |
| Visitantes que nao cadastraram (7 dias) | Visitou `/` mas nao disparou `signup` | Anuncio: "Ainda nao criou sua loja?" |
| Usuarios que iniciaram checkout (30 dias) | Evento `begin_checkout` | Anuncio: "Sua loja esta te esperando" |
| Usuarios cadastrados (30 dias) | Evento `signup` | Excluir de anuncios de aquisicao |
| Usuarios pagantes (permanente) | Evento `purchase` | Excluir de anuncios de aquisicao |

### 7.2 Criar Audiencia

1. GA4 > **Admin** > **Audiencias** > **"Nova audiencia"**
2. Selecione "Personalizada"
3. Defina a condicao (ex: `event_name = signup`)
4. Atribua um periodo de associacao (ex: 30 dias)
5. Clique em **"Salvar"**
6. A audiencia fica disponivel no Google Ads para segmentacao

---

## 8. Dashboard Personalizado no GA4

### 8.1 Metricas Essenciais

Crie um relatorio personalizado com:

| Metrica | Dimensao | Fonte |
|---------|----------|-------|
| Usuarios | Origem / Midia | Padrao |
| Novos cadastros (`signup`) | Origem / Midia | Evento `signup` |
| Compras (`purchase`) | Origem / Midia | Evento `purchase` |
| Taxa de conversao (visitante → cadastro) | Origem / Midia | Calculado: signup / usuarios |
| Receita (`purchase` value) | Origem / Midia | Evento `purchase` |
| CAC (custo / compras) | Origem / Midia | Importar custo do Google Ads |

### 8.2 Criar Relatorio

1. GA4 > **Explorar** > **"Em branco"**
2. Adicione as metricas acima
3. Adicione a dimensao "Origem da sessao / Midia"
4. Salve como `VendaPop — Funnel de Aquisicao`

---

## 9. Checklist de Ativacao

- [ ] Conta Google Analytics criada (`admin@vendapop.com.br`)
- [ ] Propriedade `VendaPop` criada
- [ ] Fluxo de dados Web criado para `vendapop.com.br`
- [ ] Measurement ID (`G-XXXXXXXXXX`) obtido e copiado
- [ ] `VITE_GA_MEASUREMENT_ID` configurado no `.env.production`
- [ ] Script GA4 carregando em todas as paginas publicas (landing + lojas)
- [ ] Script NAO carregando no painel admin
- [ ] Eventos de conversao configurados (`signup`, `purchase`, `begin_checkout`)
- [ ] Eventos marcados como conversao no GA4
- [ ] Google Ads vinculado ao GA4 (se ja tiver conta de Ads)
- [ ] Conversoes importadas para o Google Ads
- [ ] Audiencias de remarketing criadas
- [ ] Relatorio de funil personalizado criado
- [ ] Teste: acessar o site e ver usuario ativo no relatorio Tempo Real

---

## 10. Troubleshooting

| Problema | Causa provavel | Solucao |
|----------|---------------|---------|
| Nenhum dado no GA4 | Script nao carregou ou Measurement ID errado | Verificar se o script esta no `<head>`. Conferir ID no console do navegador (aba Network, filtrar por `gtag`) |
| Eventos nao aparecem | `gtag()` nao definido ou evento com erro de JS | Testar no console: `gtag('event', 'test')` e ver no Tempo Real |
| Duplicacao de dados | Script carregado 2x (ex: index.html + injecao no codigo) | Verificar se ha apenas 1 chamada pra `gtag('config', ...)` |
| UTMs nao aparecem nos relatorios | Parametros mal formatados ou page view antes dos UTMs | Usar o URL Builder do Google: https://ga-dev-tools.google/ga4/campaign-url-builder/ |
| Google Ads nao mostra conversoes | Vinculo GA4 ↔ Ads nao configurado ou conversao nao importada | Revisar passo 6.2 |

---

## Referencias

- Documentacao oficial GA4: https://support.google.com/analytics/answer/9304153
- Eventos recomendados: https://support.google.com/analytics/answer/9267735
- URL Builder: https://ga-dev-tools.google/ga4/campaign-url-builder/
- Vincular GA4 ao Google Ads: https://support.google.com/analytics/answer/9262535
