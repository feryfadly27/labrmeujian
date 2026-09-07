CREATE DATABASE IF NOT EXISTS jadwal_lab
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE jadwal_lab;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    role ENUM('admin', 'petugas') NOT NULL DEFAULT 'admin',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE = InnoDB;

CREATE TABLE kelas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kode_kelas VARCHAR(30) NOT NULL,
    nama_kelas VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_kelas_kode (kode_kelas)
) ENGINE = InnoDB;

CREATE TABLE mahasiswa (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nim VARCHAR(40) NOT NULL,
    nama_mahasiswa VARCHAR(150) NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    angkatan SMALLINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mahasiswa_nim (nim),
    KEY idx_mahasiswa_kelas (kelas_id),
    CONSTRAINT fk_mahasiswa_kelas
        FOREIGN KEY (kelas_id) REFERENCES kelas (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE mata_kuliah (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kode_mk VARCHAR(30) NOT NULL,
    nama_mk VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mata_kuliah_kode (kode_mk)
) ENGINE = InnoDB;

CREATE TABLE jadwal_ujian (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    mata_kuliah_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    tanggal_ujian DATE NOT NULL,
    sesi VARCHAR(50) NULL,
    keterangan VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_jadwal_bisnis (mata_kuliah_id, kelas_id, tanggal_ujian),
    KEY idx_jadwal_tanggal (tanggal_ujian),
    CONSTRAINT fk_jadwal_mata_kuliah
        FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_jadwal_kelas
        FOREIGN KEY (kelas_id) REFERENCES kelas (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE generate_workstation (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    jadwal_ujian_id BIGINT UNSIGNED NOT NULL,
    generated_by BIGINT UNSIGNED NOT NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'void') NOT NULL DEFAULT 'active',
    PRIMARY KEY (id),
    UNIQUE KEY uq_generate_jadwal (jadwal_ujian_id),
    KEY idx_generate_created (generated_at),
    CONSTRAINT fk_generate_jadwal
        FOREIGN KEY (jadwal_ujian_id) REFERENCES jadwal_ujian (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_generate_user
        FOREIGN KEY (generated_by) REFERENCES users (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE generate_workstation_detail (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    generate_id BIGINT UNSIGNED NOT NULL,
    mahasiswa_id BIGINT UNSIGNED NOT NULL,
    nim_snapshot VARCHAR(40) NOT NULL,
    nama_snapshot VARCHAR(150) NOT NULL,
    kode_kelas_snapshot VARCHAR(30) NOT NULL,
    nomor_urut SMALLINT UNSIGNED NOT NULL,
    workstation_no SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_detail_mahasiswa (generate_id, mahasiswa_id),
    UNIQUE KEY uq_detail_nomor (generate_id, nomor_urut),
    UNIQUE KEY uq_detail_workstation (generate_id, workstation_no),
    KEY idx_detail_mahasiswa (mahasiswa_id),
    CONSTRAINT fk_detail_generate
        FOREIGN KEY (generate_id) REFERENCES generate_workstation (id)
        ON UPDATE RESTRICT ON DELETE CASCADE,
    CONSTRAINT fk_detail_mahasiswa
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT chk_detail_nomor_positif CHECK (nomor_urut > 0),
    CONSTRAINT chk_detail_workstation_positif CHECK (workstation_no > 0)
) ENGINE = InnoDB;

CREATE TABLE import_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    imported_by BIGINT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_format ENUM('xlsx') NOT NULL,
    total_rows INT UNSIGNED NOT NULL DEFAULT 0,
    success_rows INT UNSIGNED NOT NULL DEFAULT 0,
    failed_rows INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_import_user
        FOREIGN KEY (imported_by) REFERENCES users (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_entity (entity_type, entity_id),
    KEY idx_audit_user_time (user_id, created_at),
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE = InnoDB;
