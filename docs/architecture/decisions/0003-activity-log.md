# ADR-0003: Activity Log Strategy

- **Status:** Accepted
- **Date:** 2026-05-05
- **Decision drivers:** product-owner emphasis ("سجل العمليات لكل العمليات في النظام لكل المستخدمين"), audit & compliance.

## Context
Makeen handles non-profit funds and produces formal approvals across multiple stakeholders. A complete, tamper-resistant audit trail is a hard requirement, not a nice-to-have.

## Decision
**Layered logging strategy**:

| Layer | Source | Purpose |
|---|---|---|
| Eloquent | `LogsActivity` trait on every domain model (`spatie/laravel-activitylog`) | CRUD diffs |
| State Machine | Listener on `Spatie\ModelStates\StateChanged` | Lifecycle transitions |
| Authorization | Listener on `Illuminate\Auth\Events\*` + custom `PolicyDenied` event | Login/logout/2FA/lockout/denied access |
| Permissions | Listener on `Spatie\Permission\Events\*` | Role/permission grants & revocations |
| Notifications | Listener on `NotificationSent` | Outbound mail/db notifications |
| HTTP | Custom middleware (`AuditRequestMiddleware`) | IP, user-agent, route, payload summary |
| Domain | Manually-emitted entries via `activity()` | Approve / Reject / Sign / Pay etc. |

**Storage** uses spatie's `activity_log` table with extra columns added in a migration:
- `ip_address VARCHAR(45)`
- `user_agent TEXT`
- `event_category ENUM('data','security','workflow','system','notification')`
- `severity ENUM('info','warning','critical')`

**Read-only enforcement:** `ACTIVITY_LOG_LOCKED=true` in production blocks all delete operations from the UI (enforced in the corresponding Policy + Filament resource action visibility).

**Retention:** logs older than 3 years archived to S3 (post-MVP).

**Sensitive masking:** passwords, secrets, tokens never written to `properties` JSON (configured globally in spatie's `LogOptions`).

## UX
- **Super-admin:** global searchable view at `/admin/activity-log`.
- **Per-role:** scoped view in each panel (excellence/donor see initiatives they manage; consultant sees their assigned initiatives; association sees their own org).
- **Per-initiative:** "Activity Log" tab inside the Workspace.
- **Per-user:** drill-down page from `/admin/users/{id}/activity`.

## Consequences
- All write operations cost ~1 extra DB write — acceptable.
- `activity_log` table grows fast; index on `(causer_id, subject_type, subject_id, created_at, log_name)` is mandatory.
- Tests must assert log entries for critical flows (approval, payment, role-change).
