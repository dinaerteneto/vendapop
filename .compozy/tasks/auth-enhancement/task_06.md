---
status: completed
title: OTP + Magic Link backend
type: backend
complexity: medium
dependencies:
  - task_01
---

# Task 06: OTP + Magic Link backend

## Overview

Implement passwordless authentication via 6-digit OTP code and magic link, both delivered by email. The backend generates, stores, verifies, and expires these tokens.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- POST `/admin/otp/send` MUST accept `{ email, recaptcha_token }`
  - MUST validate reCAPTCHA v3 token
  - MUST validate email exists in users table (don't reveal if not found — return generic message)
  - MUST rate limit: 1 per 30s per email, max 5 per hour
  - MUST generate a 6-digit numeric OTP (random, not hash yet), hash it with bcrypt, store in otp_tokens
  - MUST generate a random 64-char magic link token, hash it, store in otp_tokens
  - MUST send a single email containing both the OTP code and the magic link URL
  - OTP expires in 10 minutes, magic link in 30 minutes
- POST `/admin/otp/verify` MUST accept `{ email, code }`
  - MUST find the latest unused OTP for that email
  - MUST verify code with Hash::check
  - MUST mark token as used (used_at)
  - On success, return Sanctum token + user data
  - On failure, return 422 with descriptive message
- GET `/admin/magic-login?email=&token=` MUST validate the magic link token
  - MUST find the latest unused magic link for that email
  - MUST verify token with Hash::check
  - MUST mark token as used
  - MUST return Sanctum token (or redirect with token param)
- Create App\Mail\OTPMail mailable with OTP code + magic link URL
- Create emails.otp Blade template
</requirements>

## Subtasks
- [ ] 6.1 Create App\Services\OTPAuthService with generate, verifyOtp, verifyMagicLink methods
- [ ] 6.2 Create App\Http\Controllers\Api\Admin\OTPAuthController (send, verify)
- [ ] 6.3 Create App\Mail\OTPMail mailable
- [ ] 6.4 Create resources/views/emails/otp.blade.php
- [ ] 6.5 Add routes with rate limiting middleware
- [ ] 6.6 Implement magic link processing in controller

## Implementation Details

Reference TechSpec "Interfaces Principais", "Modelos de Dados", and "Endpoints da API" sections.

### Relevant Files
- `backend/app/Models/OtpToken.php` — created in task_01, use here
- `backend/app/Mail/WelcomeMail.php` — reference for mailable pattern
- `backend/resources/views/emails/password-reset.blade.php` — reference for email template
- `backend/app/Http/Controllers/Api/Admin/PasswordResetController.php` — reference for token generation pattern
- `backend/routes/api.php` — add new routes

### Dependent Files
- `backend/app/Services/OTPAuthService.php` — new
- `backend/app/Http/Controllers/Api/Admin/OTPAuthController.php` — new
- `backend/app/Mail/OTPMail.php` — new
- `backend/resources/views/emails/otp.blade.php` — new

### Related ADRs
- [ADR-003: Estratégia de Armazenamento OTP e Magic Link](../adrs/adr-003.md) — Both tokens stored in otp_tokens with hash

## Deliverables
- New OTPAuthService
- New OTPAuthController
- New OTPMail mailable + Blade template
- Updated routes/api.php
- Unit tests for OTPAuthService
- Integration tests for OTP endpoints

## Tests
- Unit tests:
  - [ ] OTP generation creates 6-digit numeric code
  - [ ] Magic link token is 64-character random string
  - [ ] verifyOtp with correct code returns User
  - [ ] verifyOtp with wrong code returns error
  - [ ] verifyOtp with expired code returns error
  - [ ] verifyOtp with already-used code returns error
  - [ ] verifyMagicLink with valid token returns User
  - [ ] verifyMagicLink with expired/used token returns error
- Integration tests:
  - [ ] POST `/admin/otp/send` with valid email returns 200
  - [ ] POST `/admin/otp/send` without reCAPTCHA returns 422
  - [ ] POST `/admin/otp/send` rate limited returns 429
  - [ ] POST `/admin/otp/verify` with correct OTP returns token
  - [ ] POST `/admin/otp/verify` with wrong OTP returns 422
  - [ ] GET `/admin/magic-login` with valid token returns success
  - [ ] GET `/admin/magic-login` with expired token returns error
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- OTP email is sent with both code and magic link
- OTP verification logs user in
- Magic link auto-authenticates user
- Rate limiting prevents abuse
- Used/expired tokens are rejected
