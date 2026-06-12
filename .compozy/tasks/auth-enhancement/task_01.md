---
status: completed
title: Migrations + Models
type: backend
complexity: low
dependencies: []
---

# Task 01: Migrations + Models

## Overview

Create the database schema changes needed for Google OAuth and OTP/magic link authentication. This task adds columns to the users table and creates a new otp_tokens table, plus the corresponding Eloquent model.

<critical>
- ALWAYS READ the PRD and TechSpec before starting
- REFERENCE TECHSPEC for implementation details — do not duplicate here
- FOCUS ON "WHAT" — describe what needs to be accomplished, not how
- MINIMIZE CODE — show code only to illustrate current structure or problem areas
- TESTS REQUIRED — every task MUST include tests in deliverables
</critical>

<requirements>
- MUST add google_id (string, nullable, unique), google_token (text, nullable), google_refresh_token (text, nullable) columns to the users table
- MUST create otp_tokens table with: id, email (indexed), code (nullable, hashed OTP), magic_link_token (nullable, hashed), expires_at, used_at (nullable), timestamps
- MUST create OtpToken Eloquent model with guarded and casts
- MUST update User model fillable array to include google_id, google_token, google_refresh_token
- Migrations MUST be reversible (down method)
</requirements>

## Subtasks
- [ ] 1.1 Create migration `add_google_auth_fields_to_users_table`
- [ ] 1.2 Create migration `create_otp_tokens_table`
- [ ] 1.3 Create `App\Models\OtpToken` model
- [ ] 1.4 Update `App\Models\User` fillable + casts

## Implementation Details

Reference TechSpec "Modelos de Dados" section for exact column definitions.

### Relevant Files
- `backend/database/migrations/0001_01_01_000001_create_tenants_and_users_table.php` — existing users schema
- `backend/app/Models/User.php` — model to extend

### Dependent Files
- `backend/app/Models/OtpToken.php` — new model (created here)
- `backend/database/migrations/2025_11_20_172146_add_email_verified_at_to_users_table.php` — pattern for adding columns

### Related ADRs
- [ADR-002: Schema de Banco para Google OAuth e OTP](../adrs/adr-002.md) — Columns on users table, separate otp_tokens table
- [ADR-003: Estratégia de Armazenamento OTP e Magic Link](../adrs/adr-003.md) — Both tokens stored with hash

## Deliverables
- Two migration files (up/down)
- App\Models\OtpToken model file
- Updated App\Models\User model
- Unit tests for OtpToken model (fillable, casts, relationships)

## Tests
- Unit tests:
  - [ ] OtpToken has correct fillable attributes
  - [ ] OtpToken casts dates correctly (expires_at, used_at, created_at)
  - [ ] OtpToken belongs to user relationship (if applicable)
- Integration tests:
  - [ ] Migrations run successfully up and down
- Test coverage target: >=80%

## Success Criteria
- All tests passing
- `php artisan migrate` runs without errors
- `php artisan migrate:rollback` reverses changes
- User model accepts new google fields
