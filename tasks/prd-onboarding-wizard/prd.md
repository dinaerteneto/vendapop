# PRD — Onboarding Wizard & Upload de Imagem Unificado

**Produto:** VendaPop  
**Data:** 2026-06-17  
**Status:** Em revisão  
**Autor:** Dinaerte Neto  
**Design spec de referência:** `docs/superpowers/specs/2026-06-17-onboarding-wizard-design.md`

---

## 1. Visão Geral

### 1.1 Resumo

O VendaPop está iniciando o beta fechado por convites. Lojistas receberão links de convite, se cadastrarão e precisarão configurar sua loja para que ela fique parecida com a loja demo `modachic`. O risco central é abandono precoce: a lojista entra no sistema, não sabe o que fazer primeiro, e desiste antes de ver o valor do produto.

Esta feature entrega três mecanismos integrados para eliminar esse risco:

1. **Loja pré-populada** — a loja nasce com conteúdo demo real para que o link já funcione no primeiro login
2. **Wizard de primeiro acesso** — guia a lojista pelos 4 passos essenciais com preview ao vivo da loja
3. **ImageUploader unificado** — resolve o principal atrito técnico: adicionar fotos de produtos de forma simples, seja por upload ou por URL

### 1.2 Problema

| # | Ponto de atrito | Impacto |
|---|-----------------|---------|
| 1 | Dashboard vazio sem direção clara | Lojista não sabe por onde começar |
| 2 | Upload de imagem complexo | Quer usar foto do Instagram mas não consegue |
| 3 | Termos técnicos opacos ("slug", "banner", "URL") | Percepção de dificuldade → abandono |
| 4 | Sem suporte visível | Quando trava, não sabe pedir ajuda |

**Diagnóstico técnico adicional:** O `ImageCropper` existente (`react-easy-crop`, proporção 2:3) só é acionado no upload de arquivo. O formulário de produto abre em modo URL por padrão — logo a maioria das lojistas nunca vê o crop. Fotos quadradas do Instagram chegam sem recorte e são cortadas de forma inesperada pelo CSS da loja (`object-fit: cover`).

### 1.3 Objetivo

Reduzir o tempo entre cadastro e "loja funcionando e pronta para compartilhar" de indefinido para **menos de 8 minutos**.

---

## 2. Usuários-Alvo

### Persona Principal: Lojista de Moda Iniciante

- Mulher, 22–40 anos, vende roupas pelo Instagram e WhatsApp
- Microempreendedora informal ou MEI, sem equipe
- Usa smartphone como ferramenta principal (não computador)
- Tira fotos das peças no celular ou reutiliza fotos dos fornecedores
- Não tem conhecimento técnico: não sabe o que é "slug", "banner" ou "URL"
- Tem baixa tolerância a atrito — se travar, abandona e tenta outro dia (ou não volta)

### Persona Secundária: Lojista com Convite de Amiga

- Chegou via indicação ("minha amiga usa e adorei como ficou a loja dela")
- Alta motivação inicial, mas expectativa igualmente alta ("quero que fique igual")
- Pode ter ainda menos paciência técnica por esperar algo "fácil"

---

## 3. Requisitos Funcionais

### 3.1 Componente 1 — Loja Pré-populada com Dados Demo

**RF-01** Ao concluir o cadastro (com ou sem código de convite), o sistema deve criar automaticamente os seguintes dados demo para o novo tenant:
- 2 categorias: "Novidades" e "Promoções"
- 4 produtos com nome fictício, preço fictício, variações P/M/G/GG e fotos de moda (imagens externas de banco gratuito como Unsplash)
- Logo placeholder gerada com as iniciais do nome da loja em fundo colorido
- Cor primária padrão (a ser definida pelo time — sugestão: roxo da identidade VendaPop)
- 1 banner padrão com foto de moda genérica

**RF-02** Todos os produtos e categorias criados como demo devem ser marcados com flag `is_demo = true`.

**RF-03** Os dados demo **não são removidos automaticamente**. A lojista é responsável por editá-los ou apagá-los manualmente quando quiser.

**RF-04** O link da loja deve estar acessível e funcional imediatamente após o cadastro, antes mesmo do primeiro login.

---

### 3.2 Componente 2 — Wizard de Primeiro Acesso

**RF-05** O wizard deve abrir automaticamente em modal no primeiro login da lojista (condição: `tenant.onboarding_completed = false`).

**RF-06** O wizard deve conter 4 passos sequenciais com barra de progresso visível:
1. Identidade (logo + cor primária)
2. Vitrine (editar produtos demo)
3. WhatsApp (número + mensagem padrão)
4. Compartilhar (ver loja + copiar link)

**RF-07** O layout do wizard deve ser dividido em duas colunas:
- Esquerda (40%): formulário do passo atual
- Direita (60%): preview ao vivo da loja (iframe apontando para a loja do tenant, atualizado após cada salvamento)

**RF-08 — Passo 1 (Identidade):** Deve permitir upload de logo via drag & drop ou seleção de arquivo, e seleção de cor primária via 8 chips pré-definidos + campo hex opcional.

**RF-09 — Passo 2 (Vitrine):** Deve exibir os 4 produtos demo em grid com botão "Editar" individual. Ao editar, deve permitir alterar nome, preço e foto (via ImageUploader unificado — RF-14 a RF-20). Deve exibir banner informativo: *"Esses são produtos de exemplo. Substitua pelas suas peças — ou pule e faça depois."* Deve ter botão "Pular" para avançar sem editar.

**RF-10 — Passo 3 (WhatsApp):** Deve exibir campo de número pré-preenchido com o WhatsApp do cadastro (editável, com máscara brasileira). Deve ter campo de texto para mensagem padrão do pedido. Deve exibir mockup visual de conversa de WhatsApp mostrando como o pedido chega para a lojista.

**RF-11 — Passo 4 (Compartilhar):** Deve exibir iframe da loja em viewport de celular (~375px). Deve ter botão "Copiar link da loja". Deve exibir instrução visual simplificada: *"1. Copie o link · 2. Cole na bio do seu Instagram"*. Deve ter botão "Ver minha loja completa" (abre nova aba). Deve ter botão "Concluir configuração" que: marca `tenant.onboarding_completed = true`, fecha o modal e exibe animação de confete por 2 segundos.

**RF-12** A lojista deve poder fechar o wizard a qualquer momento com "Fazer depois". Nesse caso:
- O `onboarding_step` deve ser salvo com o último passo concluído
- Um banner de progresso deve aparecer no topo do dashboard
- O banner deve desaparecer automaticamente após 30 dias, mesmo que o wizard não seja concluído
- Ao clicar no banner, o wizard reabre no passo onde parou

**RF-13** O sistema deve salvar `onboarding_step` a cada passo concluído para que o wizard retome de onde parou se fechado.

---

### 3.3 Componente 3 — ImageUploader Unificado

**RF-14** O ImageUploader deve substituir o comportamento atual de upload de imagem nos seguintes locais:
- Formulário de produto (foto principal)
- Configurações da loja (logo)
- Cadastro de banner rotativo

**RF-15** O ImageUploader deve aceitar duas formas de entrada em uma única interface:
- Área de drag & drop / clique para selecionar arquivo
- Campo de URL com botão "Usar"

**RF-16** Para **upload de arquivo**: após seleção do arquivo, o ImageCropper deve abrir automaticamente com a proporção correta para o contexto.

**RF-17** Para **URL**: após colar URL e clicar "Usar", o sistema deve:
1. Fazer fetch da imagem no frontend (via `<img>` ou `fetch`)
2. Verificar se é acessível
3. Abrir o ImageCropper com a imagem carregada — **mesma experiência do upload de arquivo**

**RF-18** Toda imagem — independente da origem (arquivo ou URL) — deve passar pelo crop antes de ser salva. Não deve ser possível salvar uma imagem sem passar pelo cropper.

**RF-19** Após o crop, a imagem deve ser salva como arquivo local no storage do VendaPop (não como URL externa). Imagens salvas via URL devem ser baixadas e armazenadas como arquivo.

**RF-20** O ImageUploader deve usar as seguintes proporções e dimensões por contexto:

| Contexto | Proporção | Largura | Altura |
|----------|-----------|---------|--------|
| Produto  | 2:3       | 600px   | 900px  |
| Logo     | 1:1       | 400px   | 400px  |
| Banner   | 16:9      | 1200px  | 675px  |

**RF-21** Tratamento de erros ao fazer fetch de URL:
- URL inacessível (erro de rede, 404, CORS): exibir mensagem *"Não conseguimos carregar essa imagem. Tente fazer o upload do arquivo."*
- Imagem menor que 200px em qualquer dimensão: exibir aviso *"Essa foto pode ficar borrada. Prefira fotos maiores."* (não bloqueia — é um aviso)

---

## 4. Requisitos Não-Funcionais

**RNF-01** O wizard não deve bloquear o acesso ao dashboard — a lojista pode fechar e acessar as demais funcionalidades a qualquer momento.

**RNF-02** O preview ao vivo (iframe) deve atualizar após cada ação de salvar, não em tempo real contínuo, para não sobrecarregar a API.

**RNF-03** O fetch de imagem via URL no frontend deve ter timeout de 10 segundos. Após esse tempo, exibir mensagem de erro amigável.

**RNF-04** O ImageUploader deve funcionar corretamente em dispositivos móveis (touch drag, câmera, galeria de fotos).

**RNF-05** O sistema de dados demo não deve impactar a performance de tenants existentes — a criação dos dados deve ocorrer de forma assíncrona ou ao menos não bloquear a resposta de cadastro.

**RNF-06** Imagens baixadas via URL devem ser validadas como imagens reais (verificar Content-Type) antes de armazenar.

---

## 5. Fora do Escopo

| Item | Motivo |
|------|--------|
| Vídeos tutoriais no wizard | Requer produção de conteúdo, não código |
| Página de FAQ / base de conhecimento | Escopo separado, pode ser adicionado depois |
| Botão de suporte WhatsApp no admin | Pode ser adicionado em < 1h como escopo isolado |
| Domínio próprio no onboarding | Feature Premium — roadmap futuro |
| Extração automática de foto via URL de post do Instagram | Requer API Meta — não disponível no MVP |
| Email de reativação para wizard abandonado | Pode ser adicionado ao sistema de email existente futuramente |
| Galeria de fotos no wizard (somente foto principal por produto) | Simplificação intencional para o onboarding |

---

## 6. Fluxo do Usuário

```
[Recebe link de convite]
        ↓
[vendapop.com.br/register?invite=ABC123]
        ↓
[Preenche: nome da loja, slug, WhatsApp, email]
        ↓
[Verifica email → recebe senha]
        ↓
[Faz login]
        ↓
[Sistema cria dados demo (RF-01 a RF-04)]
        ↓
[Wizard abre automaticamente]
        ↓
    ┌── Passo 1: Logo + Cor → Preview ao vivo
    ├── Passo 2: Editar produtos demo → Preview ao vivo
    ├── Passo 3: WhatsApp + Mensagem → Mockup WhatsApp
    └── Passo 4: Ver loja → Copiar link → Compartilhar
        ↓
[onboarding_completed = true]
        ↓
[Dashboard com confete + loja no ar]
```

**Tempo alvo:** 5 a 8 minutos do cadastro ao link compartilhável.

---

## 7. Modelo de Dados — Alterações Necessárias

### Tabela `tenants`
```
+ onboarding_completed  BOOLEAN  DEFAULT FALSE
+ onboarding_step       TINYINT  DEFAULT 0
```

### Tabela `products`
```
+ is_demo  BOOLEAN  DEFAULT FALSE
```

### Tabela `categories`
```
+ is_demo  BOOLEAN  DEFAULT FALSE
```

### Tabela `product_images`
Sem alteração de estrutura. O campo `is_external` permanece mas, com o novo fluxo, imagens de produtos criadas via ImageUploader sempre chegarão como arquivo local (`is_external = false`). Registros existentes com `is_external = true` continuam funcionando normalmente.

---

## 8. Critérios de Aceitação (DoD)

| # | Critério |
|---|----------|
| AC-01 | Novo cadastro (com ou sem convite) abre a loja já com 4 produtos, 2 categorias, logo e banner |
| AC-02 | Wizard abre automaticamente no primeiro login |
| AC-03 | Preview ao vivo reflete as mudanças feitas em cada passo após salvar |
| AC-04 | Lojista consegue fazer upload de foto do produto arrastando um arquivo |
| AC-05 | Lojista consegue usar foto colando uma URL — crop abre igual ao upload |
| AC-06 | Toda imagem salva via ImageUploader está armazenada no storage local (não como URL externa) |
| AC-07 | Imagens de produto aparecem na proporção 2:3 sem distorção na loja pública |
| AC-08 | Ao fechar o wizard, banner aparece no dashboard; ao clicar, wizard retoma no passo correto |
| AC-09 | Banner some após 30 dias se wizard não for concluído |
| AC-10 | Ao clicar "Concluir configuração", confete aparece e `onboarding_completed` é gravado como `true` |
| AC-11 | Logo usa proporção 1:1 e banner usa proporção 16:9 no ImageUploader |
| AC-12 | URL inacessível exibe mensagem de erro amigável sem quebrar a interface |

---

## 9. Métricas de Sucesso

| Métrica | Meta | Como medir |
|---------|------|------------|
| Tempo até loja compartilhável | < 8 minutos | Diferença entre `created_at` do tenant e `onboarding_completed_at` |
| Taxa de conclusão do wizard | > 60% | `onboarding_completed = true` / total de cadastros |
| Abandono no passo de imagem | < 10% | Passo 2 iniciado vs. concluído |
| Imagens com proporção correta | 100% | Zero reports de foto distorcida no suporte |

---

## 10. Dependências

- `react-easy-crop` já instalado no frontend — sem nova dependência
- Storage `public` do Laravel já configurado — sem nova configuração de infraestrutura
- Sistema de convites já implementado — criação de demo data se integra ao `RegisterController` existente
- `ImageCropper.tsx` existente é reutilizado — Componente 3 é uma refatoração, não reescrita

---

## 11. Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| CORS bloqueia fetch de URL de imagem no frontend | Alta | Médio | Criar endpoint proxy no backend para fazer o fetch server-side |
| Lojista não entende que deve substituir os dados demo | Média | Baixo | Banner informativo no Passo 2 + flag `is_demo` visível nos cards |
| Iframe de preview bloqueado por X-Frame-Options | Resolvido | — | Nginx define `SAMEORIGIN`. Admin e loja estão em `vendapop.com.br` — mesma origem. Iframe funciona sem alteração. Única exceção: acesso via `vendapop.dynasolutions.com.br` (evitar nos convites). |
| Imagens do Unsplash mudam de URL ao longo do tempo | Baixa | Baixo | Usar IDs fixos da API Unsplash ou baixar as imagens para o storage no seeder |
