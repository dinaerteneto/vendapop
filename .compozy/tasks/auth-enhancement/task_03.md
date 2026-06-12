---
status: completed
title: "Frontend: botão reenvio no SignIn"
type: frontend
complexity: low
dependencies:
  - task_02
---

# Task 03: Frontend: botão reenvio no SignIn

## Overview

Add an inline "Reenviar e-mail de verificação" button to the login screen that appears when the backend returns a 403 with `email_not_verified`. The button executes a reCAPTCHA v3 check and calls the resend endpoint without leaving the page.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- When login returns 403 with `email_not_verified: true`, MUST show the current error message + a "Reenviar e-mail de verificação" button below it
- Clicking the button MUST execute reCAPTCHA v3 (using `executeRecaptcha('login_resend')`) and call POST `/admin/resend-verification`
- On success MUST show a success toast "Novo e-mail enviado. Verifique sua caixa de entrada."
- On error MUST show an error toast
- The button MUST NOT navigate away from the login page
- The reCAPTCHA integration MUST follow the same pattern as the Register page's `GoogleReCaptchaProvider` + `useGoogleReCaptcha`
</requirements>

## Subtasks
- [ ] 3.1 Add `GoogleReCaptchaProvider` and `useGoogleReCaptcha` to SignIn (if not already in a parent component)
- [ ] 3.2 Detect 403 email_not_verified in catch block
- [ ] 3.3 Show error message + resend button with loading state
- [ ] 3.4 Implement resend handler with reCAPTCHA execution
- [ ] 3.5 Add success/error toast feedback

## Implementation Details

Reference the SignIn.tsx current catch block (line 26-31). The reCAPTCHA pattern should mirror Register.tsx (GoogleReCaptchaProvider wrapping, executeRecaptcha call).

### Relevant Files
- `frontend/src/pages/AuthPages/SignIn.tsx` — main target
- `frontend/src/pages/AuthPages/Register.tsx` — reference for reCAPTCHA pattern
- `frontend/src/App.tsx` — may need GoogleReCaptchaProvider at a higher level or SignIn can self-wrap

### Dependent Files
- `frontend/src/services/api.ts` — no change needed (reuses existing axios instance)

## Deliverables
- Updated SignIn.tsx with resend button + reCAPTCHA
- Integration tests for resend flow

## Tests
- Unit tests:
  - [ ] Resend button renders when 403 email_not_verified is received
  - [ ] Resend button does not render on other errors (401, 422)
  - [ ] Resend button shows loading state during request
- Integration tests:
  - [ ] Clicking resend button calls POST `/admin/resend-verification` with email + recaptcha_token
  - [ ] Success response shows toast
  - [ ] Error response shows error toast
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- Manual test: login with unverified email -> sees error + resend button -> clicks -> gets toast
