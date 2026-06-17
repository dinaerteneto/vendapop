# Design: Onboarding Wizard + Upload de Imagem Reformulado

**Data:** 2026-06-17  
**Contexto:** VendaPop está entrando em beta via convites. Lojistas receberão links de convite, se cadastrarão, e precisam configurar a loja para parecer com a loja demo `modachic`. O risco é abandono por dificuldade percebida de configuração.

---

## Problema

Quatro pontos de atrito identificados para novas lojistas:

1. **Não sabe por onde começar** — dashboard vazio sem direção clara
2. **Não tem fotos prontas** — quer usar fotos do Instagram mas não sabe como
3. **Interface percebida como difícil** — "slug", "banner", "URL de imagem" são termos opacos
4. **Sem suporte visível** — quando trava, não sabe pedir ajuda e abandona

Diagnóstico adicional no código: o `ImageCropper` (`react-easy-crop`, proporção 2:3) só é acionado para upload de arquivo. O modo padrão do formulário é URL — logo a maioria das lojistas nunca vê o crop, e imagens quadradas do Instagram chegam sem recorte, sendo cortadas de forma inesperada pelo CSS da loja.

---

## Solução: 3 Componentes Integrados

### Componente 1 — Loja Pré-populada com Dados Demo

Ao completar o cadastro com código de convite, o sistema cria a loja da lojista com conteúdo demo imediato:

**Dados criados automaticamente:**
- 2 categorias: "Novidades" e "Promoções"
- 4 produtos demo com fotos de moda (URLs de banco gratuito: Unsplash), preços fictícios, variações de tamanho (P/M/G/GG)
- Logo placeholder gerada com as iniciais da loja em fundo colorido baseado no nome
- Cor primária padrão (roxo VendaPop — alterável no wizard)
- Banner padrão com foto de moda genérica

**Resultado:** o link da loja já funciona e parece real no primeiro segundo após login. A lojista chega num produto que pode mostrar para alguém, não numa tela vazia.

**Implementação backend:**
- Novo método `TenantSeeder::seedDemoData(Tenant $tenant)` chamado no `RegisterController` quando `invite_code` está presente
- Dados demo marcados com flag `is_demo = true` nos models `Product` e `Category` para facilitar limpeza posterior
- Seeder usa imagens externas (URLs Unsplash), sem custo de storage

---

### Componente 2 — Wizard de Primeiro Acesso (4 Passos)

**Gatilho:** Modal abre automaticamente no primeiro login se `tenant.onboarding_completed = false`. Pode ser fechado com "fazer depois", o que mostra um banner persistente no dashboard com progresso.

**Layout:** Duas colunas.
- Coluna esquerda (40%): formulário do passo atual
- Coluna direita (60%): preview ao vivo da loja (iframe apontando para a loja do tenant, atualizado a cada mudança salva)

**Barra de progresso:** 4 passos visíveis no topo com ícones: Identidade → Vitrine → WhatsApp → Compartilhar

---

#### Passo 1 — Identidade da Loja

**Campos:**
- Upload de logo (área drag & drop com botão "Escolher arquivo") — aciona o novo ImageUploader unificado (ver Componente 3)
- Seletor de cor primária: 8 chips de cor pré-definidos + campo hex opcional

**Preview:** header da loja com logo e cor aplicadas em tempo real

**Campos que NÃO aparecem aqui:** slug (já foi definido no cadastro), nome da loja (idem)

---

#### Passo 2 — Vitrine (Substituir Produtos Demo)

**Apresentação:** grid de 4 cards, cada um mostrando o produto demo com botão "Editar"

**Ao clicar "Editar" num produto:**
- Nome (campo de texto inline)
- Preço (campo numérico)
- Foto: novo ImageUploader unificado (ver Componente 3)
- Salva imediatamente via PATCH

**Comunicação:** banner amarelo claro no topo do passo: "Esses são produtos de exemplo. Substitua pelas suas peças — ou pule e faça depois."

**Ação "Pular":** disponível neste passo — lojista pode ir pro próximo sem substituir nada

---

#### Passo 3 — WhatsApp e Mensagem de Pedido

**Campos:**
- Número de WhatsApp (pré-preenchido com o cadastrado, editável, com máscara `+55 (XX) XXXXX-XXXX`)
- Mensagem padrão do pedido (textarea com placeholder: "Olá! Vi sua loja e quero fazer um pedido.")

**Preview:** mockup de conversa de WhatsApp mostrando como a mensagem chega para a lojista quando um cliente faz pedido

---

#### Passo 4 — Compartilhar

**Conteúdo:**
- Iframe da loja ao vivo (tamanho de tela de celular, ~375px de largura)
- Botão "Copiar link da loja" (copia `vendapop.com.br/slug-da-loja`)
- Instrução ilustrada em 2 linhas: "1. Copie o link  2. Cole na bio do seu Instagram"
- Botão "Ver minha loja completa" — abre nova aba
- Botão "Concluir configuração" — marca `tenant.onboarding_completed = true`, fecha modal, exibe confete por 2 segundos

---

### Componente 3 — ImageUploader Unificado

Substitui o atual comportamento dividido (upload OU URL sem crop) em toda a plataforma: produtos, logo, banners.

**Interface:**

```
┌──────────────────────────────────────────────────────┐
│                                                      │
│   📷  Arraste sua foto aqui ou clique para           │
│       selecionar do celular/computador               │
│                                                      │
│   ─────────────── ou ───────────────                 │
│                                                      │
│   Cole o link da imagem: [________________]  [Usar]  │
│                                                      │
└──────────────────────────────────────────────────────┘
```

**Fluxo para arquivo:**
1. Lojista arrasta ou clica → seleciona arquivo
2. Cropper abre automaticamente (proporção 2:3 para produtos, 16:9 para banners)
3. Cropper mostra ao lado um **minicard de preview** como o produto aparece na loja
4. Lojista ajusta o zoom/posição → "Confirmar"
5. Imagem salva

**Fluxo para URL:**
1. Lojista cola URL (de qualquer fonte — Instagram, Google Fotos, WhatsApp Web)
2. Sistema faz fetch da imagem e verifica se é acessível
3. Cropper abre com a imagem buscada — **mesma experiência do upload de arquivo**
4. Lojista ajusta → "Confirmar"
5. Imagem salva como arquivo local (baixada e armazenada no storage) — evita URLs quebradas no futuro

**Mudança crítica:** URLs não são mais salvas diretamente. Toda imagem — seja de arquivo ou de URL — passa pelo cropper e é salva como arquivo no storage do VendaPop. O campo `is_external` deixa de ser usado para imagens de produtos.

**Tratamento de erros no fetch de URL:**
- URL inacessível (404, CORS): "Não conseguimos carregar essa imagem. Tente fazer o upload do arquivo."
- Imagem muito pequena (< 200px): aviso "Essa foto pode ficar borrada. Prefira fotos maiores."

**Contexto por tipo:**
- **Produto:** proporção 2:3, targetWidth 600, targetHeight 900 (sem mudança)
- **Logo:** proporção 1:1, targetWidth 400, targetHeight 400 (novo)
- **Banner:** proporção 16:9, targetWidth 1200, targetHeight 675 (novo)

---

## Modelo de Dados — Mudanças

### Tabela `tenants`
```sql
ALTER TABLE tenants ADD COLUMN onboarding_completed BOOLEAN DEFAULT FALSE;
ALTER TABLE tenants ADD COLUMN onboarding_step TINYINT DEFAULT 0;
```

### Tabelas `products` e `categories`
```sql
ALTER TABLE products ADD COLUMN is_demo BOOLEAN DEFAULT FALSE;
ALTER TABLE categories ADD COLUMN is_demo BOOLEAN DEFAULT FALSE;
```

### Tabela `product_images`
O campo `is_external` permanece mas o fluxo de criação via URL agora baixa e converte para local. Entradas existentes com `is_external = true` continuam funcionando.

---

## Fluxo Completo do Novo Usuário

```
Recebe link de convite
  → Acessa vendapop.com.br/register?invite=ABC123
  → Preenche: nome da loja, slug, WhatsApp, email
  → Recebe email de verificação
  → Verifica email → recebe senha temporária
  → Faz login
  → Backend: cria dados demo (Componente 1)
  → Wizard abre automaticamente (Componente 2)
    → Passo 1: logo + cor → preview ao vivo
    → Passo 2: edita produtos demo (usa Componente 3)
    → Passo 3: confirma WhatsApp + mensagem
    → Passo 4: copia link → compartilha
  → onboarding_completed = true
  → Dashboard normal com confete
```

Tempo estimado do fluxo completo: **5 a 8 minutos**.

---

## Fora do Escopo

- Vídeos tutoriais (conteúdo, não código — pode ser adicionado depois como URLs no wizard)
- Página de FAQ/base de conhecimento
- Botão de suporte WhatsApp (pode ser adicionado em 30 min como escopo separado)
- Domínio próprio no onboarding (feature Premium futura)
- Integração com URL do Instagram para extração automática de imagem (requer API do Instagram — fora do MVP)

---

## Critérios de Sucesso

- Lojista vê loja funcionando em menos de 2 minutos após primeiro login
- Taxa de conclusão do wizard > 60% (medida por `onboarding_completed`)
- Zero abandonos por "não sei como adicionar foto" (medida por suporte direto)
- Todas as imagens de produto exibidas na proporção correta (zero distorção)
