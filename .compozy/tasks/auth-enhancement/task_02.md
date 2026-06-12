---
status: completed
title: Corrigir endpoint resend (reCAPTCHA, rate limit, sem regenerar senha)
type: backend
complexity: low
dependencies:
  - task_01
---

# Task 02: Corrigir endpoint resend (reCAPTCHA, rate limit, sem regenerar senha)

## Overview

Fix the `/admin/resend-verification` endpoint: add reCAPTCHA v3 validation, remove password regeneration, and add rate limiting. Update WelcomeMail to accept an optional password and adjust the Blade template accordingly.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- POST `/admin/resend-verification` MUST validate reCAPTCHA v3 token before sending (same logic as RegistrationController)
- POST `/admin/resend-verification` MUST NOT regenerate the user's password
- POST `/admin/resend-verification` MUST have rate limiting (1 per 2 minutes per email, return 429)
- WelcomeMail constructor MUST accept password as optional (`?string $password = null`)
- WelcomeMail Blade template MUST conditionally display password section only when password is provided
- RegistrationController MUST pass password to WelcomeMail as before
- EmailVerificationController@resend MUST pass null for password when calling WelcomeMail
</requirements>

## Subtasks
- [ ] 2.1 Add reCAPTCHA v3 validation to EmailVerificationController@resend
- [ ] 2.2 Remove password regeneration logic from resend
- [ ] 2.3 Add `Illuminate\Routing\Middleware\ThrottleRequests` to resend route (or use `RateLimiter` facade)
- [ ] 2.4 Update WelcomeMail constructor to make password optional
- [ ] 2.5 Update welcome.blade.php to show password section only when present
- [ ] 2.6 Verify RegistrationController still passes password correctly

## Implementation Details

Reference TechSpec "Endpoints da API" section for request/response format. The reCAPTCHA validation logic can be copied from RegistrationController.

### Relevant Files
- `backend/app/Http/Controllers/Api/Admin/EmailVerificationController.php` — main target
- `backend/app/Mail/WelcomeMail.php` — make password optional
- `backend/resources/views/emails/welcome.blade.php` — conditional password display
- `backend/app/Http/Controllers/Api/Admin/RegistrationController.php` — passes password, must stay unchanged
- `backend/routes/api.php` — add throttle middleware to resend route

### Dependent Files
- All callers of WelcomeMail constructor (RegistrationController, EmailVerificationController)

### Related ADRs
- [ADR-005: Reenvio de Verificação Não Regenera Senha](../adrs/adr-005.md) — Resend only sends verification link

## Deliverables
- Updated EmailVerificationController@resend
- Updated WelcomeMail with optional password
- Updated welcome.blade.php template
- Updated api.php with throttle middleware on resend route
- Unit tests for WelcomeMail with and without password
- Integration tests for resend endpoint

## Tests
- Unit tests:
  - [ ] WelcomeMail renders password section when password is provided
  - [ ] WelcomeMail omits password section when password is null
- Integration tests:
  - [ ] POST `/admin/resend-verification` with valid data returns 200 and sends email
  - [ ] POST `/admin/resend-verification` without reCAPTCHA returns 422
  - [ ] POST `/admin/resend-verification` hit rate limit returns 429
  - [ ] POST `/admin/resend-verification` does NOT change user password in database
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- Rate limiting works (2nd request within 2min returns 429)
- User password remains unchanged after resend
- Registration email still includes password
