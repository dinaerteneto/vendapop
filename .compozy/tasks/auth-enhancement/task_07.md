---
status: completed
title: "Frontend: OTP + Magic Link"
type: frontend
complexity: medium
dependencies:
  - task_06
---

# Task 07: Frontend: OTP + Magic Link

## Overview

Add the "Entrar com código por e-mail" flow to the login page: when toggled, the form switches to email input → OTP code input. Also create the magic link landing page that auto-authenticates the user.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- MUST add a "Entrar com código por e-mail" link below the password form in SignIn.tsx
- Clicking the link MUST switch the form to show only: email input + "Enviar código" button
- After email submission (POST /admin/otp/send), MUST show a 6-digit OTP input field
- Each digit input MUST auto-advance to the next field (standard OTP UX)
- When all 6 digits filled, MUST auto-submit to POST /admin/otp/verify (or have a "Verificar" button)
- On success, MUST store token and redirect to dashboard
- On error, MUST show error toast
- MUST create a MagicLogin.tsx page component at route `/admin/magic-login`
  - Reads token and email from query params
  - Calls GET `/admin/magic-login?email=&token=` (or the corresponding API)
  - On success, stores token and redirects to dashboard
  - On error, shows error message and link to login page
- MUST add the magic-login route to App.tsx
</requirements>

## Subtasks
- [ ] 7.1 Add "Entrar com código por e-mail" toggle to SignIn.tsx
- [ ] 7.2 Create email input → OTP code input transition UI
- [ ] 7.3 Create 6-digit OTP input component with auto-advance
- [ ] 7.4 Implement OTP send + verify API calls
- [ ] 7.5 Create MagicLogin.tsx page
- [ ] 7.6 Add /admin/magic-login route to App.tsx

## Implementation Details

The OTP input should accept numeric input only (type="text" with inputMode="numeric" pattern="[0-9]*"). Each digit should be a separate input for better UX, or a single masked input.

### Relevant Files
- `frontend/src/pages/AuthPages/SignIn.tsx` — main target (extensive modifications)
- `frontend/src/pages/AuthPages/VerifyEmail.tsx` — reference for API call + redirect pattern
- `frontend/src/App.tsx` — add magic-login route

### Dependent Files
- `frontend/src/pages/AuthPages/MagicLogin.tsx` — new page
- `frontend/src/components/ui/OtpInput.tsx` — optional new component

## Deliverables
- Updated SignIn.tsx with OTP flow
- New MagicLogin.tsx page
- Updated App.tsx routes
- Unit tests for OTP flow UI states

## Tests
- Unit tests:
  - [ ] "Entrar com código por e-mail" link is visible
  - [ ] Clicking link hides password form, shows email input
  - [ ] Email input → OTP input transition works
  - [ ] OTP input accepts only digits
  - [ ] OTP auto-submits on 6th digit
  - [ ] MagicLogin page shows loading state
  - [ ] MagicLogin page shows error state on failure
- Integration tests:
  - [ ] OTP flow calls POST /admin/otp/send then /admin/otp/verify
  - [ ] Successful OTP verification stores token and redirects
  - [ ] MagicLogin processes token and redirects
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- Manual test: click "Entrar com código" → enter email → receive OTP → enter code → logged in
- Manual test: click magic link in email → auto-authenticated → dashboard
