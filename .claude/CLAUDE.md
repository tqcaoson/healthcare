# CLAUDE.md — Laravel Healthcare Information System

Hệ thống EHR/HIS xây dựng trên Laravel 11 (PHP 8.3), phục vụ bệnh viện/phòng khám vừa và nhỏ.
**Bỏ qua:** tính năng gọi điện (call), AI/ML inference trực tiếp.

---

## Nội dung chi tiết

### Architect — Kiến trúc & Schema
- [architect/overview.md](architect/overview.md) — Tech stack, layered architecture, cấu trúc thư mục
- [architect/database.md](architect/database.md) — Toàn bộ MySQL schema (patients, encounters, orders, results, billing, audit...)
- [architect/fhir.md](architect/fhir.md) — FHIR R4 resource mapping (FhirMapper, $everything bundle, routes)

### Agent — Domain Services & Infrastructure
- [agent/services.md](agent/services.md) — MpiService, CpoeService, CdsAlertService, AuditService, QualityMetricsService, TelehealthService
- [agent/middleware.md](agent/middleware.md) — AuditPhiAccess, ScrubPhiFromLogs, AutoLogout
- [agent/jobs.md](agent/jobs.md) — Queue jobs, scheduled tasks, critical value escalation
- [agent/routes.md](agent/routes.md) — API v1 routes, telehealth routes, rate limiting
- [agent/notifications.md](agent/notifications.md) — CriticalLabValue, AppointmentReminder, NewMedicationOrder notifications
- [agent/pharmacy.md](agent/pharmacy.md) — PharmacyService, MAR (Medication Administration Record), 5 Rights enforcement
- [agent/hl7.md](agent/hl7.md) — HL7 config, outbox pattern, idempotency, artisan commands

### Skill — Patterns & How-To
- [skill/eloquent.md](skill/eloquent.md) — PhiModel base, Patient model, encryption casts, Policy authorization
- [skill/api-response.md](skill/api-response.md) — ApiResponse class, pagination format, error handler
- [skill/testing.md](skill/testing.md) — Feature tests: patient, CPOE, medication administration, audit
- [skill/commands.md](skill/commands.md) — Artisan commands: HL7, quality metrics, session purge, audit archive, downtime cache

### Memory — Config & Reference
- [memory/naming.md](memory/naming.md) — Naming conventions (Models, Tables, Controllers, Services...)
- [memory/env.md](memory/env.md) — Environment variables bắt buộc
- [memory/config.md](memory/config.md) — config/health.php (clinical, security, audit, terminology settings)
- [memory/terminology.md](memory/terminology.md) — Terminology lookup tables & TerminologyService
- [memory/downtime.md](memory/downtime.md) — Health check endpoint, downtime read-only mode

### Rule — Quy tắc & Checklist
- [rule/absolute.md](rule/absolute.md) — 10 quy tắc tuyệt đối KHÔNG vi phạm (PHI, audit, CDS, hard-delete...)
- [rule/security.md](rule/security.md) — Security rules: auth, encryption, rate limiting, PHI audit trail
- [rule/checklist.md](rule/checklist.md) — Deploy checklist: security, integration, clinical safety, operations
