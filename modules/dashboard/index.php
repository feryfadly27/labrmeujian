<?php

declare(strict_types=1);

requireAuthentication();

$statistics = [];
$recentSchedules = [];
$recentResults = [];
$error = null;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $counts = $database->query(
        'SELECT (SELECT COUNT(*) FROM kelas) AS kelas,
                (SELECT COUNT(*) FROM mahasiswa) AS mahasiswa,
                (SELECT COUNT(*) FROM mata_kuliah) AS mata_kuliah,
                (SELECT COUNT(*) FROM jadwal_ujian) AS jadwal_ujian'
    )->fetch();
    $statistics = [
        ['label' => 'Data Kelas', 'value' => $counts['kelas'], 'url' => 'classes.php'],
        ['label' => 'Mahasiswa', 'value' => $counts['mahasiswa'], 'url' => 'students.php'],
        ['label' => 'Mata Kuliah', 'value' => $counts['mata_kuliah'], 'url' => 'courses.php'],
        ['label' => 'Jadwal Ujian', 'value' => $counts['jadwal_ujian'], 'url' => 'schedules.php'],
    ];
    $recentSchedules = $database->query(
        'SELECT jadwal_ujian.tanggal_ujian, mata_kuliah.kode_mk, mata_kuliah.nama_mk, kelas.kode_kelas
         FROM jadwal_ujian
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         ORDER BY jadwal_ujian.tanggal_ujian DESC, jadwal_ujian.id DESC
         LIMIT 5'
    )->fetchAll();
    $recentResults = $database->query(
        'SELECT generate_workstation.id, generate_workstation.generated_at,
                mata_kuliah.kode_mk, mata_kuliah.nama_mk, kelas.kode_kelas
         FROM generate_workstation
         INNER JOIN jadwal_ujian ON jadwal_ujian.id = generate_workstation.jadwal_ujian_id
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         ORDER BY generate_workstation.generated_at DESC
         LIMIT 5'
    )->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Ringkasan dashboard belum dapat dimuat.';
    $statistics = [
        ['label' => 'Data Kelas', 'value' => 0, 'url' => 'classes.php'],
        ['label' => 'Mahasiswa', 'value' => 0, 'url' => 'students.php'],
        ['label' => 'Mata Kuliah', 'value' => 0, 'url' => 'courses.php'],
        ['label' => 'Jadwal Ujian', 'value' => 0, 'url' => 'schedules.php'],
    ];
}

renderView('dashboard/index', [
    'pageTitle' => 'Dashboard',
    'statistics' => $statistics,
    'recentSchedules' => $recentSchedules,
    'recentResults' => $recentResults,
    'error' => $error,
]);
