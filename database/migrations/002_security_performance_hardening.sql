-- Index yang hilang untuk kolom yang sering difilter/di-JOIN (lihat security & performance review).
ALTER TABLE mahasiswa
    ADD KEY idx_mahasiswa_angkatan (angkatan);

ALTER TABLE generate_workstation_detail
    ADD KEY idx_detail_nim_snapshot (nim_snapshot);

ALTER TABLE audit_log
    ADD KEY idx_audit_action (action);

-- Rate limit percobaan login & pencarian NIM publik, dikunci ke IP (+ username untuk login),
-- disimpan di database agar TIDAK bisa dilewati hanya dengan membuang cookie sesi.
CREATE TABLE IF NOT EXISTS rate_limit_attempt (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    scope VARCHAR(20) NOT NULL,
    attempt_key VARCHAR(64) NOT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 1,
    first_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rate_limit_scope_key (scope, attempt_key),
    KEY idx_rate_limit_last_attempt (last_attempt_at)
) ENGINE = InnoDB;
