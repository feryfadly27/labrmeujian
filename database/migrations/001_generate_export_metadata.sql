CREATE TABLE IF NOT EXISTS generate_export_metadata (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    generate_id BIGINT UNSIGNED NOT NULL,
    jenis_ujian ENUM('UAS', 'UTS', 'KUIS', 'REMEDIAL') NOT NULL DEFAULT 'UAS',
    program_studi VARCHAR(150) NOT NULL DEFAULT '',
    periode_akademik VARCHAR(100) NOT NULL DEFAULT '',
    tahun_akademik VARCHAR(20) NOT NULL DEFAULT '',
    semester VARCHAR(30) NOT NULL DEFAULT '',
    waktu VARCHAR(50) NOT NULL DEFAULT '',
    ruang VARCHAR(100) NOT NULL DEFAULT '',
    kelompok VARCHAR(100) NOT NULL DEFAULT '',
    kota VARCHAR(100) NOT NULL DEFAULT 'Tasikmalaya',
    pengawas_json JSON NULL,
    pengajar_json JSON NULL,
    catatan_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_export_metadata_generate (generate_id),
    CONSTRAINT fk_export_metadata_generate
        FOREIGN KEY (generate_id) REFERENCES generate_workstation (id)
        ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE = InnoDB;
