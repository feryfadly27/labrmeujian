<?php

declare(strict_types=1);

require_once BASE_PATH . '/src/Support/IndonesianDate.php';

$hari = trim((string) ($_GET['hari'] ?? ''));
$kelasId = (string) ($_GET['kelas_id'] ?? '');
$angkatan = trim((string) ($_GET['angkatan'] ?? ''));

$dayNumbers = [
    'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4,
    'jumat' => 5, 'sabtu' => 6, 'minggu' => 0,
];

$schedules = [];
$classes = [];
$angkatanList = [];
$error = null;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $classes = $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll();
    $angkatanList = $database->query('SELECT DISTINCT angkatan FROM mahasiswa WHERE angkatan IS NOT NULL ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN);

    $query =
        'SELECT jadwal_ujian.id, jadwal_ujian.tanggal_ujian, jadwal_ujian.sesi, jadwal_ujian.keterangan,
                mata_kuliah.kode_mk, mata_kuliah.nama_mk,
                kelas.id AS kelas_id, kelas.kode_kelas, kelas.nama_kelas,
                generate_workstation.id AS generate_id,
                generate_export_metadata.waktu, generate_export_metadata.ruang
         FROM jadwal_ujian
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         LEFT JOIN generate_workstation ON generate_workstation.jadwal_ujian_id = jadwal_ujian.id
         LEFT JOIN generate_export_metadata ON generate_export_metadata.generate_id = generate_workstation.id
         WHERE 1 = 1';
    $parameters = [];

    if ($kelasId !== '' && filter_var($kelasId, FILTER_VALIDATE_INT) !== false) {
        $query .= ' AND kelas.id = :kelas_id';
        $parameters['kelas_id'] = (int) $kelasId;
    }

    if ($angkatan !== '' && filter_var($angkatan, FILTER_VALIDATE_INT) !== false) {
        $query .= ' AND EXISTS (SELECT 1 FROM mahasiswa WHERE mahasiswa.kelas_id = kelas.id AND mahasiswa.angkatan = :angkatan)';
        $parameters['angkatan'] = (int) $angkatan;
    }

    $query .= ' ORDER BY jadwal_ujian.tanggal_ujian ASC, jadwal_ujian.id ASC';
    $statement = $database->prepare($query);
    $statement->execute($parameters);
    $allSchedules = $statement->fetchAll();

    $hariKey = strtolower($hari);
    if ($hari !== '' && isset($dayNumbers[$hariKey])) {
        $targetDow = $dayNumbers[$hariKey];
        $schedules = array_values(array_filter(
            $allSchedules,
            static fn (array $row): bool => (int) (new DateTimeImmutable($row['tanggal_ujian']))->format('w') === $targetDow
        ));
    } else {
        $schedules = $allSchedules;
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Jadwal belum dapat dimuat. Silakan coba lagi nanti.';
}

renderView('landing/index', [
    'pageTitle' => 'Jadwal Ujian & Penggunaan Lab RME',
    'bulletinPage' => true,
    'schedules' => $schedules,
    'classes' => $classes,
    'angkatanList' => $angkatanList,
    'hari' => $hari,
    'kelasId' => $kelasId,
    'angkatan' => $angkatan,
    'error' => $error,
]);
