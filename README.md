# منصة مكين — Makeen Platform

نظام لارافل لإدارة المبادرات والتمكين المؤسسي للجمعيات غير الربحية، يدعم دورة حياة المبادرة من التسجيل والاعتماد إلى التنفيذ والإغلاق على مدى 32 شهراً.

A Laravel 12 + Filament 4 platform for managing improvement initiatives across non-profit associations — spanning registration, multi-party approval, monthly visits & reports, KPI tracking, payments, and closure over a 32-month lifecycle.

> **Status:** Sprint 1 — Identity, RBAC, Brand identity, public association registration. Domain modules (initiatives, visits, payments, …) come in Sprint 2+.

## Brand identity
The visual identity is sourced from the official `Primary Colors.pdf` and the Makeen + Masar Al Ejadh logos. All UI tokens live in `config/brand.php` (single source of truth):
- **Gold** `#f9ad1c` — Makeen primary, used by the admin panel.
- **Navy** `#283979` — Masar Al Ejadh primary, used by the excellence panel.
- **Teal** `#21b2b8` — used by the donor panel.
- **Slate** `#2b354f` — used by the consultant panel.

The full Makeen logo appears on every panel header and on the public guest pages; a "Powered by Masar Al Ejadh" footer is included on guest pages.

## Stack
- PHP **8.2+**
- **Laravel 12.x**
- **Filament 4.x** (5 panels: admin / excellence / donor / consultant / association)
- Spatie suite: `permission`, `activitylog`, `model-states`, `medialibrary`, `query-builder`
- Pest 3 · Larastan · Pint
- MySQL 8 / MariaDB 10.6+ for production · SQLite for local dev/tests
- Languages: Arabic (default) + English (translation files in `lang/`)

## Architecture
- **Modular Monolith** organised under `app/Domain/{Identity, Reference, Initiatives, Execution, Communication, Finance, Evaluation, Notifications, SystemAdmin}`.
- **5 Filament Panels** with role-based access (see `app/Providers/Filament/*PanelProvider.php`).
- **Initiative Workspace UX** — see [ADR-0002](docs/architecture/decisions/0002-initiative-workspace.md).
- **Layered Activity Log** — see [ADR-0003](docs/architecture/decisions/0003-activity-log.md).
- **Stack rationale** — see [ADR-0001](docs/architecture/decisions/0001-stack.md).

## Quick start

```bash
# 1) Install PHP dependencies
composer install

# 2) Environment
cp .env.example .env
php artisan key:generate

# 3) Database (SQLite by default)
php artisan migrate

# 4) Run dev server
php artisan serve

# Quality checks
composer test          # Pest
vendor/bin/pint --test # code style (read-only)
vendor/bin/phpstan     # Larastan static analysis
```

Default routes:
- `/` → redirects to `/admin`
- `/admin` → super-admin panel (default panel)
- `/excellence` · `/donor` · `/consultant` · `/association` → role-specific panels
- `/register/association` → public association self-registration form
- `/register/association/pending` → "your account is awaiting admin approval" page

After running `php artisan migrate --seed`, the seeders create:
- 7 canonical roles + 17 permissions wired via `RolePermissionSeeder`
- One default `super_admin` user (credentials from `SUPER_ADMIN_*` env vars; see `.env.example`).

## Roles
| Role | Panel | Notes |
|---|---|---|
| `super_admin` | admin | full cross-tenant access |
| `excellence_manager` / `excellence_member` | excellence | reviews initiatives, manages standards |
| `donor_admin` | donor | approves initiatives & payments |
| `consultant` | consultant | reviews assigned initiatives, monthly reports |
| `association_manager` / `association_member` | association | submits & runs initiatives (tenant-scoped) |

Consultants and donor users are created from the **admin** panel; associations self-register via `/register/association`.

## Repository layout
```
app/
├── Domain/                 # business modules (DDD-lite)
│   ├── Identity/
│   ├── Reference/
│   ├── Initiatives/
│   ├── Execution/
│   ├── Communication/
│   ├── Finance/
│   ├── Evaluation/
│   ├── Notifications/
│   └── SystemAdmin/
├── Filament/               # admin panel (default)
├── Filament/Excellence/
├── Filament/Donor/
├── Filament/Consultant/
├── Filament/Association/
├── Http/Controllers/Public/  # public registration etc.
└── Providers/Filament/     # 5 panel providers

docs/
├── architecture/decisions/ # ADRs
└── business/               # specs, form definitions, etc.

lang/{ar,en}/               # translation files
```

## License
Proprietary — RaafatAraby / Makeen.
