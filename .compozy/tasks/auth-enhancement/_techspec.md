# TechSpec: Melhorias de Autenticação

## Resumo Executivo

Adicionar três fluxos de autenticação ao login existente (email+senha) do VesteZap: (1) reenvio inline de verificação de email pela tela de login, (2) Google OAuth via Laravel Socialite, e (3) login sem senha via OTP + magic link. Todos os novos fluxos preservam a arquitetura existente de tokens Sanctum. O trade-off principal é entre a simplicidade do AuthController monolítico atual e a necessidade de classes de serviço separadas para cada nova estratégia de autenticação, evitando acoplamento.

## Arquitetura do Sistema

### Visão de Componentes

```
┌─────────────────────────────────────────────────────────────┐
│                   Frontend (React)                          │
│  ┌──────────┐  ┌───────────┐  ┌────────────┐  ┌─────────┐  │
│  │ SignIn   │  │ GoogleBtn │  │ OTPForm    │  │MagicLink│  │
│  │ (ext.)   │  │           │  │            │  │ Page    │  │
│  └────┬─────┘  └─────┬─────┘  └─────┬──────┘  └────┬────┘  │
│       └──────────────┴──────────────┴───────────────┘       │
│                           │ Chamadas API                    │
└───────────────────────────┼─────────────────────────────────┘
                            │
┌───────────────────────────┼─────────────────────────────────┐
│                   Backend (Laravel)                          │
│  ┌────────────────────────┴──────────────────────────────┐  │
│  │                  Controllers                          │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │  AuthController (estendido)                     │  │  │
│  │  │  EmailVerificationController (modificado)       │  │  │
│  │  │  GoogleAuthController (novo)                    │  │  │
│  │  │  OTPAuthController (novo)                       │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────┘  │
│                           │                                  │
│  ┌────────────────────────┴──────────────────────────────┐  │
│  │                  Services                             │  │
│  │  ┌─────────────────────┐  ┌────────────────────────┐  │  │
│  │  │ GoogleAuthService   │  │ OTPAuthService         │  │  │
│  │  │ (wrapper Socialite) │  │ (geração OTP+magic lnk)│  │  │
│  │  └─────────────────────┘  └────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────┘  │
│                           │                                  │
│  ┌────────────────────────┴──────────────────────────────┐  │
│  │                  Models                               │  │
│  │  ┌─────────────┐  ┌───────────────────────────────┐  │  │
│  │  │ User (ext.) │  │ OtpToken (novo Model)         │  │  │
│  │  │ +google_id  │  │                               │  │  │
│  │  │ +google_*   │  │                               │  │  │
│  │  └─────────────┘  └───────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Fluxo de Dados

- **Reenvio**: SignIn → POST `/admin/resend-verification` (com reCAPTCHA token) → EmailVerificationController → valida reCAPTCHA → rate limit → gera token → envia email → retorna sucesso
- **Google**: SignIn → redirect Google OAuth → tela de consentimento Google → callback → GoogleAuthController → Socialite user → vincula ou cria conta → retorna token Sanctum
- **OTP**: SignIn → POST `/admin/otp/send` (email) → OTPAuthController → valida reCAPTCHA → rate limit → gera OTP + magic link → salva em otp_tokens → envia email → frontend mostra input OTP → POST `/admin/otp/verify` (código) → verifica → deleta token → retorna token Sanctum

## Design de Implementação

### Interfaces Principais

```php
// App/Services/GoogleAuthService.php
class GoogleAuthService
{
    public function getRedirectUrl(): string;
    public function handleCallback(): SocialiteUser;
    public function findOrCreateUser(SocialiteUser $googleUser): User;
    public function linkToExistingUser(User $user, SocialiteUser $googleUser): User;
}

// App/Services/OTPAuthService.php
class OTPAuthService
{
    public function generateAndSend(string $email): void;
    public function verifyOtp(string $email, string $code): User;
    public function verifyMagicLink(string $email, string $token): User;
}
```

### Modelos de Dados

**Adições na tabela users** (migration):

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('google_id')->nullable()->unique()->after('email_verified_at');
    $table->text('google_token')->nullable()->after('google_id');
    $table->text('google_refresh_token')->nullable()->after('google_token');
});
```

**Tabela otp_tokens** (nova migration):

```php
Schema::create('otp_tokens', function (Blueprint $table) {
    $table->id();
    $table->string('email')->index();
    $table->string('code')->nullable();            // OTP de 6 dígitos (hash)
    $table->string('magic_link_token')->nullable(); // token magic link (hash)
    $table->timestamp('expires_at');
    $table->timestamp('used_at')->nullable();
    $table->timestamps();
});
```

### Endpoints da API

| Método | Path | Descrição | Auth |
|--------|------|-----------|------|
| POST | `/admin/resend-verification` | Reenviar email de verificação | Não (reCAPTCHA) |
| GET | `/admin/auth/google` | Redirecionar para Google OAuth | Não |
| GET | `/admin/auth/google/callback` | Callback do Google OAuth | Não |
| POST | `/admin/auth/google/link` | Confirmar vinculação de conta | Sim (token temporário) |
| POST | `/admin/otp/send` | Enviar email com OTP + magic link | Não (reCAPTCHA) |
| POST | `/admin/otp/verify` | Verificar código OTP | Não |
| GET | `/admin/magic-login` | Processar magic link | Não (token assinado) |
| POST | `/admin/onboarding` | Completar cadastro após Google | Sim (token temporário) |

**POST `/admin/resend-verification`**

Request: `{ email: string, recaptcha_token: string }`

Response 200: `{ message: "Se o e-mail existir, um novo link será enviado." }`

Response 429: `{ message: "Muitas tentativas. Tente novamente em alguns minutos." }`

Mudanças: adicionar validação reCAPTCHA, remover regeneração de senha, adicionar rate limiting.

**POST `/admin/otp/send`**

Request: `{ email: string, recaptcha_token: string }`

Response 200: `{ message: "Código enviado para seu e-mail." }`

Rate limit: 1 a cada 30s por email, máximo 5 por hora.

**POST `/admin/otp/verify`**

Request: `{ email: string, code: string }` (código OTP de 6 dígitos)

Response 200: `{ token: string, user: object, tenant_slug: string }`

Response 422: `{ message: "Código inválido ou expirado." }`

**GET `/admin/magic-login?email=&token=`**

Valida o token magic link. Em caso de sucesso, redireciona para o frontend com um token temporário que o frontend troca por um token Sanctum.

## Pontos de Integração

| Serviço | Propósito | Auth |
|---------|-----------|------|
| API Google OAuth | Autenticação via Google | OAuth 2.0 client_id/secret |
| reCAPTCHA v3 | Prevenção de abuso (já configurado) | Site key + secret key |

- Google OAuth: usar Laravel Socialite com `stateless()` (API-driven, sem sessão)
- reCAPTCHA: mesmas chaves já usadas no RegistrationController

## Análise de Impacto

| Componente | Tipo de Impacto | Descrição | Ação Necessária |
|-----------|-----------------|-----------|-----------------|
| `AuthController` | Modificado | Adicionar referência de rate limiting, sem mudança de lógica | Nenhuma |
| `EmailVerificationController@resend` | Modificado | Adicionar reCAPTCHA, remover regeneração de senha | Atualizar método |
| `RegistrationController@store` | Modificado | Apenas email inicial tem senha; resend não envia senha | Ajustar chamada WelcomeMail |
| `User` model | Modificado | Adicionar google_id, google_token, google_refresh_token | Migration + fillable |
| `SignIn.tsx` | Modificado | Adicionar botão reenvio, botão Google, toggle OTP | Atualização UI significativa |
| `VerifyEmail.tsx` | Sem mudança | Já funciona com endpoint existente | Nenhuma |
| `WelcomeMail` | Modificado | Tornar password opcional (null para resend) | Atualizar construtor |
| `welcome.blade.php` | Modificado | Mostrar senha condicionalmente | Atualizar template |
| `api.php` routes | Modificado | Adicionar rotas Google, OTP, magic link, onboarding | Adicionar definições |
| `composer.json` | Modificado | Adicionar `laravel/socialite` | Executar composer require |

## Abordagem de Testes

### Testes Unitários

- `GoogleAuthService`: mock Socialite, testar findOrCreateUser com contas existentes/nova/vinculada
- `OTPAuthService`: testar geração OTP, verificação, validação magic link, expiração, uso único
- Verificar se rate limiting é acionado corretamente

### Testes de Integração

- `POST /admin/resend-verification`: testar com reCAPTCHA mock, verificar email enviado, verificar senha NÃO alterada
- `POST /admin/otp/send`: testar rate limiting, envio de email
- `POST /admin/otp/verify`: testar código válido, expirado, errado, já usado
- `GET /admin/auth/google/callback`: testar fluxo completo OAuth com Socialite mockado
- `GET /admin/magic-login`: testar token válido e expirado

### Testes Frontend

- SignIn renderiza botão de reenvio ao receber 403
- SignIn faz transição para view OTP ao clicar no link
- Botão Google dispara redirect
- Estados de erro exibidos corretamente

## Sequenciamento de Desenvolvimento

### Ordem de Construção

1. **Migration + Model** — Sem dependências
   - Criar migration `add_google_auth_fields_to_users_table`
   - Criar migration `create_otp_tokens_table`
   - Criar model `OtpToken`
   - Atualizar model `User` (fillable, casts)

2. **Corrigir endpoint resend** — Depende do passo 1
   - Adicionar validação reCAPTCHA em `EmailVerificationController@resend`
   - Remover regeneração de senha
   - Adicionar rate limiting (middleware ThrottleRequests)
   - Tornar `WelcomeMail` opcional (`?string $password = null`)
   - Atualizar `welcome.blade.php` para exibir senha condicionalmente

3. **Frontend: botão reenvio no SignIn** — Depende do passo 2
   - Detectar resposta 403 `email_not_verified`
   - Mostrar botão inline chamando `/admin/resend-verification` com reCAPTCHA
   - Feedback de sucesso/erro (toast)

4. **Google OAuth** — Depende do passo 1
   - `composer require laravel/socialite`
   - Criar `GoogleAuthService`
   - Criar `GoogleAuthController` (redirect, callback, link)
   - Adicionar rotas (`/admin/auth/google`, `/admin/auth/google/callback`, `/admin/auth/google/link`)
   - Implementar onboarding para novos usuários Google (token temporário → `/admin/onboarding`)

5. **Frontend: botão Google no SignIn** — Depende do passo 4
   - Adicionar botão "Entrar com Google"
   - Implementar diálogo de confirmação de vinculação
   - Implementar redirecionamento para onboarding de novos usuários Google

6. **OTP + Magic Link backend** — Depende do passo 1
   - Criar `OTPAuthService`
   - Criar `OTPAuthController` (send, verify)
   - Adicionar rotas (`/admin/otp/send`, `/admin/otp/verify`, `/admin/magic-login`)
   - Adicionar rate limiting no send
   - Criar Mailable `OTPMail` com código + magic link

7. **Frontend: OTP + Magic Link** — Depende do passo 6
   - Adicionar toggle "Entrar com código por e-mail" no SignIn
   - Fluxo: input email → input código OTP
   - Página magic link (`/admin/magic-login`) que autentica automaticamente
   - Criar componente `MagicLogin.tsx`

8. **Cleanup + cron** — Depende do passo 1
   - Criar comando artisan `auth:cleanup-expired-tokens`
   - Agendar execução diária no `Kernel.php`

### Dependências Técnicas

- Google OAuth requer HTTPS para callback URLs em produção
- É necessário um projeto no Google API Console com credenciais OAuth 2.0 (client_id, client_secret)
- Chaves reCAPTCHA v3 já configuradas (sem dependência)

## Monitoramento e Observabilidade

- Eventos de log: `resend_verification_sent`, `google_login_success`, `google_login_link_prompted`, `otp_sent`, `otp_verified`, `magic_link_used`
- Rate limit excedido: logar como warning com email + IP
- Alertar se rate limit for atingido 10+ vezes pelo mesmo IP em 1 hora (possível abuso)

## Considerações Técnicas

### Decisões Principais

- **Google auth na tabela users**: Queries mais simples do que tabela normalizada social_logins; YAGNI se aplica pois apenas Google está planejado
- **OTP + magic link em tabela DB**: Permite rate limiting, revogação e cleanup; trade-off é escrita em DB por requisição de auth
- **Socialite stateless**: Usar método `stateless()` pois VesteZap é API-driven (tokens Sanctum, sem sessão)
- **Resend não regenera senha**: Usuários que perderam a senha usam o fluxo "Esqueci minha senha" existente

### Riscos Conhecidos

| Risco | Probabilidade | Mitigação |
|-------|--------------|-----------|
| Timeout no callback Google OAuth | Baixa | Usar fila para operações pesadas; chamada Socialite é síncrona |
| Atraso na entrega de OTP | Média | Usuário vê feedback "Verifique seu e-mail"; magic link no mesmo email como fallback |
| Falso positivo em rate limiting | Baixa | Limites conservadores (30s por email); monitorar logs |
| Cleanup de tokens expirados | Baixa | Cron diário deleta registros expirados de `otp_tokens` e `email_verifications` |

## Registros de Decisão de Arquitetura

- [ADR-001: Abordagem de Expansão Progressiva de Autenticação](adrs/adr-001.md) — Evoluir login progressivamente: reenvio inline, Google OAuth, OTP/magic link
- [ADR-002: Schema de Banco para Google OAuth e OTP](adrs/adr-002.md) — Colunas Google na tabela users; tabela separada otp_tokens
- [ADR-003: Estratégia de Armazenamento OTP e Magic Link](adrs/adr-003.md) — Ambos tokens armazenados em otp_tokens com hash
- [ADR-004: Fluxo de Onboarding para Novos Usuários Google](adrs/adr-004.md) — Etapa de onboarding pós-autenticação Google para novos usuários
- [ADR-005: Reenvio de Verificação Não Regenera Senha](adrs/adr-005.md) — Resend envia apenas link de verificação, não modifica a senha
