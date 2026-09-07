<?php

declare(strict_types=1);

require_once BASE_PATH . '/src/Support/IndonesianDate.php';

const WORKSTATION_VIEW_OPEN_HOUR = 7;

$nim = trim((string) ($_GET['nim'] ?? ''));
$results = [];
$error = null;
$searched = $nim !== '';

if ($searched) {
    if (strlen($nim) > 40) {
        $error = 'NIM tidak valid.';
    } else {
        try {
            $database = createDatabaseConnection(databaseConfigFromEnvironment());

            if (isLookupRateLimited($database)) {
                $error = 'Terlalu banyak percobaan pencarian. Silakan coba lagi beberapa menit lagi.';
            } else {
                $statement = $database->prepare(
                    'SELECT generate_workstation_detail.nomor_urut, generate_workstation_detail.workstation_no,
                            generate_workstation_detail.nama_snapshot, generate_workstation_detail.kode_kelas_snapshot,
                            jadwal_ujian.tanggal_ujian, mata_kuliah.kode_mk, mata_kuliah.nama_mk,
                            generate_export_metadata.waktu, generate_export_metadata.ruang
                     FROM generate_workstation_detail
                     INNER JOIN generate_workstation ON generate_workstation.id = generate_workstation_detail.generate_id
                     INNER JOIN jadwal_ujian ON jadwal_ujian.id = generate_workstation.jadwal_ujian_id
                     INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
                     LEFT JOIN generate_export_metadata ON generate_export_metadata.generate_id = generate_workstation.id
                     WHERE generate_workstation_detail.nim_snapshot = :nim
                     ORDER BY jadwal_ujian.tanggal_ujian DESC'
                );
                $statement->execute(['nim' => $nim]);
                $allMatches = $statement->fetchAll();
                recordLookupAttempt($database);

                $today = date('Y-m-d');
                $isPastOpenHour = (int) date('G') >= WORKSTATION_VIEW_OPEN_HOUR;
                $results = array_values(array_filter(
                    $allMatches,
                    static fn (array $row): bool => $row['tanggal_ujian'] === $today && $isPastOpenHour
                ));

                if ($results === [] && $allMatches !== []) {
                    $error = sprintf(
                        'Nomor workstation baru bisa dilihat mulai pukul %02d:00 pada hari ujian berlangsung.',
                        WORKSTATION_VIEW_OPEN_HOUR
                    );
                } elseif ($results === []) {
                    $error = 'NIM tidak ditemukan atau belum memiliki nomor workstation.';
                }
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'Pencarian belum dapat diproses. Silakan coba lagi.';
        }
    }
}

renderView('landing/lookup', [
    'pageTitle' => 'Cari Nomor Workstation',
    'bulletinPage' => true,
    'nim' => $nim,
    'results' => $results,
    'searched' => $searched,
    'error' => $error,
]);
