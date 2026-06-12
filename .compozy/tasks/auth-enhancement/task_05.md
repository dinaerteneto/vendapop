---
status: completed
title: "Frontend: botão Google + onboarding"
type: frontend
complexity: medium
dependencies:
  - task_04
---

# Task 05: Frontend: botão Google + onboarding

## Overview

Add the "Entrar com Google" button to the login screen and handle all post-auth flows: account linking confirmation dialog, and the onboarding page for first-time Google users.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- MUST add a "Entrar com Google" button below the password form in SignIn.tsx
- The Google button MUST redirect to GET `/admin/auth/google` (full-page redirect)
- When callback returns `link_required: true`, frontend MUST show a confirmation dialog: "Uma conta com este e-mail já existe mas ainda não foi verificada. Deseja vincular sua conta Google e verificar automaticamente?"
  - "Sim" calls POST `/admin/auth/google/link` -> on success stores token and redirects to dashboard
  - "Não" shows message to use another account
- When callback returns a new user flow (temporary token), frontend MUST redirect to `/admin/onboarding?token=...`
- The onboarding page MUST show a form: store_name, store_slug, whatsapp_number (same fields as Register)
- Onboarding form submission calls POST `/admin/onboarding` with temp token -> on success stores token and redirects to dashboard
- Create GoogleOnboarding.tsx page component
- Add /admin/onboarding route to App.tsx
</requirements>

## Subtasks
- [ ] 5.1 Add Google button to SignIn.tsx
- [ ] 5.2 Handle OAuth callback response (link_required vs new user vs direct login)
- [ ] 5.3 Create account linking confirmation dialog component
- [ ] 5.4 Create GoogleOnboarding.tsx page
- [ ] 5.5 Add /admin/onboarding route to App.tsx

## Implementation Details

The Google OAuth callback URL must match what's configured in `config/services.php`. The frontend may receive the callback result via a dedicated redirect page that processes the token and redirects accordingly.

### Relevant Files
- `frontend/src/pages/AuthPages/SignIn.tsx` — add Google button + callback handling
- `frontend/src/pages/AuthPages/Register.tsx` — reference for onboarding form fields
- `frontend/src/App.tsx` — add /admin/onboarding route

### Dependent Files
- `frontend/src/pages/AuthPages/GoogleOnboarding.tsx` — new page

### Related ADRs
- [ADR-004: Fluxo de Onboarding para Novos Usuários Google](../adrs/adr-004.md) — Post-Google onboarding step

## Deliverables
- Updated SignIn.tsx with Google button
- New GoogleOnboarding.tsx page
- New or updated confirmation dialog component
- Updated App.tsx routes

## Tests
- Unit tests:
  - [ ] Google button renders on login page
  - [ ] Google button has correct redirect URL
  - [ ] Confirmation dialog renders when link_required is true
  - [ ] GoogleOnboarding form validates required fields
- Integration tests:
  - [ ] Onboarding form submits to POST /admin/onboarding
  - [ ] Successful submission stores token and redirects
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- Google button visible on login page
- Clicking Google button redirects to Google OAuth
- Linking dialog appears for unverified users
- Onboarding form works for new Google users
