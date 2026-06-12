---
status: completed
title: Cleanup cron + comando artisan
type: backend
complexity: low
dependencies:
  - task_01
---

# Task 08: Cleanup cron + comando artisan

## Overview

Create an artisan command to clean up expired tokens from `otp_tokens` and `email_verifications` tables, and schedule it to run daily. This prevents database bloat from unused/expired tokens.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- MUST create `auth:cleanup-expired-tokens` artisan command
- Command MUST delete records from `otp_tokens` where `expires_at` is older than now AND `used_at` is not null OR `expires_at` is older than 7 days
- Command MUST delete records from `email_verifications` where `created_at` is older than 48 hours
- Command MUST log the number of deleted records
- Command MUST be scheduled to run daily in `bootstrap/app.php` (Laravel 12 scheduling)
</requirements>

## Subtasks
- [ ] 8.1 Create `app/Console/Commands/CleanupExpiredTokens.php`
- [ ] 8.2 Implement cleanup logic for otp_tokens
- [ ] 8.3 Implement cleanup logic for email_verifications
- [ ] 8.4 Register command in Console/Kernel (or bootstrap/app.php for Laravel 12)
- [ ] 8.5 Schedule daily execution

## Implementation Details

In Laravel 12, scheduling is done in `bootstrap/app.php` using `Schedule` facade. The command should use `DB::table()` directly (no model needed for bulk delete).

### Relevant Files
- `bootstrap/app.php` — schedule the command
- `backend/app/Console/Commands/MigrateProductSizesAndColorsToAttributes.php` — reference for command structure

### Dependent Files
- `backend/app/Console/Commands/CleanupExpiredTokens.php` — new file

## Deliverables
- New CleanupExpiredTokens command
- Updated bootstrap/app.php with schedule
- Unit tests for command

## Tests
- Unit tests:
  - [ ] Command deletes expired otp_tokens
  - [ ] Command deletes old email_verifications
  - [ ] Command does not delete non-expired tokens
  - [ ] Command logs correct count of deleted records
- Integration tests:
  - [ ] `php artisan auth:cleanup-expired-tokens` runs without error
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- `php artisan auth:cleanup-expired-tokens` runs and cleans old records
- Schedule runs daily (verified via `php artisan schedule:list`)
