# Implementation Plan

## Status

This plan turns the PRD into small, verifiable implementation stages. No application source code exists yet.

## Checklist

- [x] Lock business and security decisions.
- [x] Draft the database schema and constraints.
- [x] Create the PHP native application skeleton.
- [x] Implement authentication, sessions, CSRF, and authorization.
- [ ] Implement master data modules.
- [x] Implement class list, create, edit, and delete flows.
- [x] Implement student list, filter, create, edit, and delete flows.
- [x] Implement course list, search, create, edit, and delete flows.
- [x] Implement exam schedule list, search, create, edit, and delete flows.
- [x] Implement validated XLSX-only student import.
- [x] Implement transactional workstation generation.
- [x] Implement stored-result detail pages.
- [x] Implement PDF, XLSX, and DOCX exports from one shared template data source.
- [x] Add business, security, and regression tests.
- [ ] Run final validation and patch verified failures.

## Delivery Rules

- Complete one stage before starting the next stage.
- Keep each patch focused on one concern.
- Run a focused validation after every patch.
- Do not expose database errors, stack traces, or secrets to users.
- Preserve generated results as immutable snapshots.

## Stage 1: Business and Security Decisions

### Default decisions for the MVP

- One exam schedule targets one class.
- A generated result is unique per `mata_kuliah + kelas + tanggal_ujian`.
- `sesi` is descriptive and is not part of the uniqueness key unless the business owner changes this decision.
- A generated result stores participant snapshots (`nim`, name, and class code), not only foreign keys.
- A generated result cannot be edited in place. Corrections use an explicit delete-and-regenerate action subject to authorization and audit logging.
- Master records already referenced by schedules or generated results cannot be hard-deleted.
- The application timezone is `Asia/Jakarta`.
- Only XLSX is accepted for import. Uploaded files are stored outside the public web root.
- Duplicate NIM values are rejected during import unless an explicit update policy is added later.
- Workstation numbers equal the generated sequence number for the MVP.

### Security baseline

- Passwords use `password_hash()` and `password_verify()`.
- Every state-changing request requires a CSRF token.
- Session IDs are regenerated after successful login.
- Session cookies use `HttpOnly`, `Secure` in HTTPS, and `SameSite=Lax`.
- Every protected endpoint performs server-side authentication and role checks.
- Database access uses PDO prepared statements only.
- Output is escaped by context: HTML, attribute, PDF/DOCX template, and spreadsheet cell.
- Uploads use extension, MIME, size, header, and content validation; random filenames are required.
- Generate uses a database unique constraint plus a transaction and duplicate-key handling.

## Stage Acceptance Criteria

- The schema can enforce one generated result for the same business key.
- Historical output remains unchanged after master data is edited.
- Unauthorized, forged, malformed, or duplicate requests are rejected server-side.
- Import and generate operations cannot leave partial data after an error.
- Every later module has a corresponding focused test or executable verification.

## Implementation Order

1. Finalize this decision record.
2. Create the MySQL schema and indexes.
3. Create the PHP native application skeleton and configuration boundary.
4. Implement authentication, sessions, CSRF, and authorization.
5. Implement class, student, course, and exam schedule modules.
6. Implement validated spreadsheet import.
7. Implement transactional workstation generation and audit data.
8. Implement stored-result detail pages and PDF/XLSX/DOCX exports.
9. Add business, security, and regression tests.
10. Run final checks and patch only verified failures.

## Open Decisions Before Schema Freeze

- Confirm whether one schedule may target multiple classes in a later phase.
- Confirm whether `sesi` must distinguish two exams on the same date.
- Confirm the required institutional header and signature fields for exports.
- Confirm the MySQL server version and PHP version available in deployment.
