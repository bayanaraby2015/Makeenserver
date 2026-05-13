# ADR-0002: Initiative-as-Workspace UX

- **Status:** Accepted
- **Date:** 2026-05-05
- **Decision drivers:** product-owner request, UX of long-running (32-month) initiatives, Zoho-Projects-style reference.

## Context
A Makeen initiative has a 32-month lifecycle with monthly visits, monthly reports, 5 payments, KPIs, risks, tickets, evidence uploads, and approvals from 3 different actors. Treating it as a flat CRUD list of edit-fields scattered across resources fragments the user experience and increases mistakes.

## Decision
Each initiative is presented as a **Workspace** — a Filament page mounted at `/{panel}/initiatives/{id}` with internal **tabs**:

1. Overview (Dashboard)
2. Initiative Card (the 9-section form, section-level editable based on State + Role)
3. Gantt Plan (timeline of outputs/phases)
4. Outputs & Evidence
5. Visits & Meetings
6. Monthly Reports
7. Finance & Payments
8. KPIs (heatmap + history)
9. Risks
10. Tickets
11. Team (assigned consultants)
12. Documents
13. Evaluations
14. Activity Log (per-initiative)

Tabs are dynamically shown/hidden based on the user's role and the initiative's state.

The resource list (`/{panel}/initiatives`) remains as the entry point and continues to support cross-initiative filtering, but the click-through opens the Workspace, not a flat edit form.

## Consequences
- All initiative-related queries are scoped to one record while inside the Workspace — better caching, easier authorization.
- Section-level editability is enforced via `InitiativePolicy::editSectionN()` methods — single source of truth for UI + API.
- Cross-initiative views (e.g. "all open tickets across associations") are still supported as separate top-level resources.

## Implementation notes (for Sprint 4)
- Single Filament Page class `ViewInitiativeWorkspace` with `Tabs::make()`.
- Lazy-load tabs to avoid loading 14 tabs of data on every visit.
- Gantt: `frappe-gantt` JS lib via Vite — no PHP-side dependency.
