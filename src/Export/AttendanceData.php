<?php

declare(strict_types=1);

function loadAttendanceExportData(PDO $database, int $generateId): array
{
    $resultStatement = $database->prepare(
        'SELECT generate_workstation.id, generate_workstation.generated_at,
                jadwal_ujian.tanggal_ujian, mata_kuliah.kode_mk, mata_kuliah.nama_mk,
                kelas.kode_kelas
         FROM generate_workstation
         INNER JOIN jadwal_ujian ON jadwal_ujian.id = generate_workstation.jadwal_ujian_id
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         WHERE generate_workstation.id = :id'
    );
    $resultStatement->execute(['id' => $generateId]);
    $result = $resultStatement->fetch();
    if (!is_array($result)) {
        throw new RuntimeException('Hasil generate tidak ditemukan.');
    }

    $detailStatement = $database->prepare(
        'SELECT nomor_urut, workstation_no, nim_snapshot, nama_snapshot, kode_kelas_snapshot
         FROM generate_workstation_detail WHERE generate_id = :id ORDER BY nim_snapshot'
    );
    $detailStatement->execute(['id' => $generateId]);

    $metadataStatement = $database->prepare(
        'SELECT jenis_ujian, program_studi, periode_akademik, tahun_akademik, semester,
                waktu, ruang, kelompok, kota, pengawas_json, pengajar_json, catatan_json
         FROM generate_export_metadata WHERE generate_id = :id'
    );
    $metadataStatement->execute(['id' => $generateId]);
    $metadataRow = $metadataStatement->fetch() ?: [];
    $metadata = [
        'jenis_ujian' => $metadataRow['jenis_ujian'] ?? 'UAS',
        'program_studi' => $metadataRow['program_studi'] ?? '',
        'periode_akademik' => $metadataRow['periode_akademik'] ?? '',
        'tahun_akademik' => $metadataRow['tahun_akademik'] ?? '',
        'semester' => $metadataRow['semester'] ?? '',
        'waktu' => $metadataRow['waktu'] ?? '',
        'ruang' => $metadataRow['ruang'] ?? '',
        'kelompok' => $metadataRow['kelompok'] ?? '',
        'kota' => $metadataRow['kota'] ?? 'Tasikmalaya',
        'pengawas' => json_decode((string) ($metadataRow['pengawas_json'] ?? '[]'), true) ?: [],
        'pengajar' => json_decode((string) ($metadataRow['pengajar_json'] ?? '[]'), true) ?: [],
        'catatan' => json_decode((string) ($metadataRow['catatan_json'] ?? '[]'), true) ?: [],
    ];

    return ['result' => $result, 'details' => $detailStatement->fetchAll(), 'metadata' => $metadata];
}
