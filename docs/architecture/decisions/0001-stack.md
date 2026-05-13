# ADR-0001: Technology Stack — Laravel 12 + Filament 4

- **Status:** Accepted
- **Date:** 2026-05-05
- **Decision drivers:** product-owner request, longevity, Filament v4 stability, performance gains in v4 tables.

## Context
The original `makeen-README.md` proposed Laravel 11 + Filament 3.2. Both Laravel 12 (released Feb 2025) and Filament 4 (stable Aug 2025) are now available and supported.

## Decision
Use **Laravel 12.x + Filament 4.x + PHP 8.2+**.

## Consequences
- Laravel 12 EOL: bug fixes until Aug 2026, security until Feb 2027.
- Filament 4 brings ~3× table render perf, unified Schema components, and improved multi-tenancy primitives — all directly useful to Makeen.
- Some Spatie packages required `^8.4` for their newest major versions; we pin acceptable older majors that explicitly support Laravel 12 + PHP 8.2+ (see `composer.lock`).

## Notes
The README will be updated in Sprint 1 to reference 12/4. References in the historical README to "Laravel 11" / "Filament 3.2" should be considered superseded by this ADR.
