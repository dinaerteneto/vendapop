# PRD: Melhorias de Autenticação

## Visão Geral

O VesteZap atualmente suporta apenas login com email + senha com verificação obrigatória de email. Quando um usuário se cadastra mas não recebe (ou perde) o email de verificação, ele é bloqueado com um erro 403 na tela de login sem nenhuma opção de reenvio. Além disso, a plataforma não oferece alternativas modernas de login — Google OAuth e login sem senha via OTP/magic link — que são cada vez mais esperadas pelos usuários.

Este PRD aborda três problemas interligados: (1) desbloquear usuários presos na verificação de email adicionando um mecanismo de reenvio inline, (2) reduzir o atrito do login adicionando Google OAuth, e (3) oferecer uma alternativa sem senha via OTP + magic link para usuários que esqueceram a senha ou preferem não usar senhas.

## Metas

- Reduzir a taxa de usuários bloqueados: quem recebe o 403 por email não verificado deve conseguir se resolver com um clique
- Aumentar a taxa de login bem-sucedido: oferecer Google OAuth e OTP/magic link como caminhos alternativos
- Manter a postura de segurança: todos os novos fluxos protegidos por reCAPTCHA v3 e rate limiting
- Entregar as três funcionalidades no mesmo ciclo sem quebrar o fluxo de login existente

## Histórias de Usuário

- Como um **novo lojista que não recebeu o email de verificação**, quero solicitar um novo email de verificação pela tela de login para ativar minha conta sem precisar de suporte.
- Como um **lojista**, quero entrar com minha conta Google para não precisar lembrar de mais uma senha.
- Como um **lojista que esqueceu a senha**, quero receber um código de uso único ou link mágico por email para acessar meu painel imediatamente sem passar pelo fluxo de redefinição de senha.
- Como um **lojista com conta existente por email que tenta login com Google**, quero ser perguntado se desejo vincular as contas para não criar uma duplicata ou ficar confuso.

## Funcionalidades Principais

### F1: Reenvio Inline de Verificação de Email

A tela de login deve detectar a resposta 403 `email_not_verified` do backend e exibir uma mensagem inline com um botão "Reenviar e-mail de verificação" abaixo do texto de erro. Ao clicar no botão:

- Executa reCAPTCHA v3 (invisível, usando a mesma site key já configurada)
- Chama o endpoint existente `POST /admin/resend-verification` com o email já preenchido
- Mostra um toast de sucesso ("Novo e-mail enviado. Verifique sua caixa de entrada.") ou feedback de erro
- Tem rate limit de 1 requisição a cada 2 minutos por email (imposto pelo servidor)
- Não sai da página de login

O backend já possui a rota `/admin/resend-verification` funcional. As únicas mudanças necessárias no backend são:
- Adicionar verificação reCAPTCHA v3 antes de enviar (mesma lógica do cadastro)
- Adicionar rate limiting no servidor
- **Importante**: o reenvio não deve mais regenerar a senha do usuário. A senha gerada no cadastro inicial deve ser preservada. O email de reenvio deve apenas conter o link de verificação, não uma nova senha.

### F2: Login com Google OAuth

Um botão "Entrar com Google" deve ser exibido abaixo do formulário de senha na tela de login.

Fluxo:
1. Usuário clica em "Entrar com Google"
2. Redirecionado para a tela de consentimento OAuth do Google
3. Google redireciona de volta para uma URL de callback
4. Backend recebe os dados do Google (id, nome, email)
5. Backend verifica se já existe um usuário com aquele email:
   - **Usuário não existe**: redirecionar para um onboarding rápido para coletar nome da loja, slug e WhatsApp, e criar o Tenant + Usuário já com email verificado
   - **Usuário existe, email verificado**: fazer login direto e retornar token
   - **Usuário existe, email NÃO verificado**: retornar resposta perguntando se o usuário quer vincular a conta Google. O frontend exibe um diálogo de confirmação: "Uma conta com este e-mail já existe mas ainda não foi verificada. Deseja vincular sua conta Google e verificar automaticamente?" Se sim, marcar email como verificado, vincular google_id e fazer login. Se não, exibir mensagem para usar outra conta Google ou verificar o email.
6. Após autenticação, o mesmo fluxo de token Sanctum se aplica

O backend requer adição do pacote Laravel Socialite. As credenciais do Google OAuth (client_id, client_secret) devem ser configuradas em `config/services.php`.

### F3: Login com OTP + Magic Link

Um link "Entrar com código por e-mail" deve ser exibido abaixo do formulário de senha.

Fluxo:
1. Usuário clica em "Entrar com código por e-mail"
2. O formulário de login muda para uma visão simplificada: apenas campo de email e botão "Enviar código"
3. Usuário digita o email e clica em enviar
4. Backend valida que o email existe e gera:
   - Um código OTP numérico de 6 dígitos (válido por 10 minutos)
   - Uma URL magic link assinada criptograficamente (válida por 30 minutos)
5. Backend envia um único email contendo tanto o código OTP quanto o magic link
6. Frontend exibe um campo de entrada de 6 dígitos para o OTP
7. Usuário pode:
   - Digitar o código de 6 dígitos manualmente → backend verifica OTP → faz login
   - Clicar no magic link no email → o link abre uma página que autentica automaticamente a sessão → redireciona para o dashboard
8. Endpoint de OTP protegido por reCAPTCHA v3 + rate limiting (1 requisição a cada 30 segundos por email)

A página de magic link (`/admin/magic-login?token=...`) deve ser uma nova rota no frontend que valida o token assinado e faz login do usuário sem exigir nenhuma entrada.

## Experiência do Usuário

**Alterações no SignIn.tsx:**
- Ao receber 403 `email_not_verified`: exibir mensagem de erro + botão "Reenviar e-mail de verificação" abaixo
- Abaixo do formulário de senha: botão "Entrar com Google" + link "Entrar com código por e-mail"
- Ao clicar em "Entrar com código por e-mail": o formulário faz transição para mostrar campo de email + botão "Enviar código", e após o envio mostra campo de 6 dígitos para o OTP
- Login com Google dispara um redirect de página completa (fluxo OAuth padrão)

**Novas rotas:**
- `/admin/magic-login` — página de destino do magic link que autentica automaticamente
- `/admin/auth/google` — redirecionamento Google OAuth
- `/admin/auth/google/callback` — callback do Google OAuth

**Onboarding após primeiro Google login (se usuário não existir ainda):**
- Usuários que entram pelo Google sem conta existente devem completar um cadastro rápido: nome da loja, slug, WhatsApp (mesmos campos do formulário de registro atual)
- Isso é apresentado como uma etapa pós-autenticação

## Restrições Técnicas de Alto Nível

- Deve integrar com o sistema de autenticação existente baseado em Laravel Sanctum
- Deve usar as mesmas chaves reCAPTCHA v3 já configuradas para o cadastro
- Google OAuth requer HTTPS em produção (as URIs de redirect devem corresponder exatamente)
- Rate limiting deve ser no servidor (não apenas no cliente) para prevenir abuso
- Todas as novas rotas devem estar sob o prefixo `/admin` para manter consistência da API
- Tokens de magic link devem ser assinados criptograficamente (armazenados ou não em DB — decisão de implementação)
- O reenvio de verificação NÃO deve mais alterar a senha do usuário

## Não Escopo

- Login com Facebook, Apple ou outros provedores sociais
- OTP via WhatsApp (deferido para futuro)
- Autenticação biométrica (digital, facial)
- Lembrar-me / sessões persistentes além do tempo de vida do token Sanctum
- Gerenciamento de perfil de usuário (já coberto por funcionalidades existentes)
- Gerenciamento de usuários administrativos (convidar membros da equipe)

## Plano de Rollout em Fases

### MVP (Fase 1)
- Botão de reenvio inline na tela de login com reCAPTCHA v3
- Rate limiting no endpoint de reenvio
- Correção: reenvio não deve mais regenerar a senha
- **Critério de sucesso**: usuários conseguem reenviar email de verificação da tela de login e completar a verificação

### Fase 2
- Login com Google OAuth com diálogo de vinculação de conta
- Integração Laravel Socialite
- Etapa de onboarding para registros via Google
- **Critério de sucesso**: usuários conseguem logar com Google; usuários existentes podem vincular conta Google

### Fase 3
- Login sem senha via OTP + magic link
- Nova rota `/admin/magic-login`
- Interface de entrada de OTP de 6 dígitos
- **Critério de sucesso**: usuários conseguem logar sem senha usando OTP ou magic link

## Métricas de Sucesso

- Redução de tickets de suporte relacionados a "não recebi email de verificação"
- Percentual de logins via Google OAuth vs senha vs OTP
- Taxa de clique do magic link
- Taxa de conversão do OTP (usuários que solicitam OTP e logam com sucesso)
- Frequência de disparo de rate limit (deve ser baixa, indicando pouco abuso)

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Usuários abusam dos endpoints de reenvio/OTP | reCAPTCHA v3 + rate limiting por email + IP |
| Vinculação Google OAuth confunde usuários | Diálogo de confirmação claro em português com linguagem não técnica |
| Atraso na entrega de email frustra usuários | Feedback claro: "E-mail enviado. Pode levar alguns minutos." |
| Código OTP interceptado | Expiração curta (10 min), uso único, tentativas com rate limit |
| Reenvio atual regenera senha incorretamente | Corrigir: reenvio não deve modificar a senha existente |

## Registros de Decisão de Arquitetura

- [ADR-001: Abordagem de Expansão Progressiva de Autenticação](adrs/adr-001.md) — Evoluir o login progressivamente: reenvio inline, depois Google OAuth, depois OTP/magic link, mantendo o fluxo de senha intacto

## Perguntas em Aberto

- O Google OAuth deve estar disponível para usuários completamente novos (sem cadastro prévio) ou apenas como método secundário para quem já se cadastrou por email? Se disponível para novos, quais campos mínimos de onboarding são necessários?
- Qual o limite de rate limiting para requisições de OTP? (Proposto: 1 a cada 30s por email, máximo 5 por hora)
- Os códigos OTP devem ser apenas numéricos (6 dígitos) ou alfanuméricos?
- Deve haver um limite de tentativas de OTP antes de bloquear temporariamente? (Ex: 3 tentativas erradas = bloqueio de 15 minutos)
