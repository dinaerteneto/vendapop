---
status: completed
title: Google OAuth backend
type: backend
complexity: medium
dependencies:
  - task_01
---

# Task 04: Google OAuth backend

## Overview

Implement Google OAuth authentication on the backend using Laravel Socialite. This includes the redirect/callback flow, account linking for existing users, and a post-Google onboarding endpoint for first-time users.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- MUST install `laravel/socialite` via composer
- MUST configure Google OAuth credentials in `config/services.php` under 'google' key (client_id, client_secret, redirect)
- MUST create `GoogleAuthService` with: getRedirectUrl, handleCallback, findOrCreateUser, linkToExistingUser
- MUST create `GoogleAuthController` with: redirect (GET), callback (GET), link (POST)
- Callback flow:
  - New user: return temporary signed token + user data — frontend redirects to onboarding
  - Existing user, email verified: log in, return Sanctum token
  - Existing user, email NOT verified: return `link_required: true` + user data — frontend shows confirmation dialog
- POST `/admin/auth/google/link` MUST accept email + confirmation to link Google account, verify email, and log in
- POST `/admin/onboarding` MUST accept temporary token + store_name + store_slug + whatsapp_number, create Tenant + User with email_verified, return Sanctum token
- All Socialite calls MUST use `stateless()` (API authentication, no session)
</requirements>

## Subtasks
- [ ] 4.1 `composer require laravel/socialite`
- [ ] 4.2 Add Google config to config/services.php
- [ ] 4.3 Create App\Services\GoogleAuthService
- [ ] 4.4 Create App\Http\Controllers\Api\Admin\GoogleAuthController
- [ ] 4.5 Add routes to api.php
- [ ] 4.6 Implement onboarding endpoint (POST /admin/onboarding)

## Implementation Details

Reference TechSpec "Interfaces Principais", "Endpoint da API", and "Pontos de Integração" sections.

### Relevant Files
- `backend/config/services.php` — add Google OAuth config
- `backend/app/Http/Controllers/Api/Admin/RegistrationController.php` — reference for Tenant + User creation logic
- `backend/app/Http/Controllers/Api/Admin/AuthController.php` — reference for Sanctum token generation
- `backend/routes/api.php` — add new routes
- `backend/composer.json` — will be updated by composer require

### Dependent Files
- `backend/app/Services/GoogleAuthService.php` — new
- `backend/app/Http/Controllers/Api/Admin/GoogleAuthController.php` — new

### Related ADRs
- [ADR-002: Schema de Banco para Google OAuth e OTP](../adrs/adr-002.md) — google_id, google_token, google_refresh_token on users table
- [ADR-004: Fluxo de Onboarding para Novos Usuários Google](../adrs/adr-004.md) — Post-Google onboarding step

## Deliverables
- Updated composer.json + composer.lock (Socialite)
- Updated config/services.php
- New App\Services\GoogleAuthService
- New App\Http\Controllers\Api\Admin\GoogleAuthController
- Updated routes/api.php
- Unit tests for GoogleAuthService
- Integration tests for GoogleAuthController

## Tests
- Unit tests:
  - [ ] GoogleAuthService::findOrCreateUser with existing verified user
  - [ ] GoogleAuthService::findOrCreateUser with existing unverified user (returns needs linking)
  - [ ] GoogleAuthService::findOrCreateUser with new user (returns temp state)
  - [ ] GoogleAuthService::linkToExistingUser marks email verified and stores google_id
- Integration tests:
  - [ ] GET `/admin/auth/google` returns redirect (mock Socialite)
  - [ ] GET `/admin/auth/google/callback` handles callback with mocked Socialite user
  - [ ] POST `/admin/auth/google/link` links account and logs in
  - [ ] POST `/admin/onboarding` creates tenant+user and returns token
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- Google OAuth redirect flow works end-to-end with real credentials
- Account linking works: unverified user links Google -> email verified
- New Google user onboarding creates tenant+user correctly
