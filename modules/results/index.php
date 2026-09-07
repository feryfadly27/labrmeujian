<?php

declare(strict_types=1);

requireAuthentication();

$results = [];
$error = null;

$perPage = 20;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $totalRows = (int) $database->query('SELECT COUNT(*) FROM generate_workstation')->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $results = $database->query(
        'SELECT generate_workstation.id, generate_workstation.generated_at,
                jadwal_ujian.tanggal_ujian, mata_kuliah.kode_mk, mata_kuliah.nama_mk, kelas.kode_kelas
         FROM generate_workstation
         INNER JOIN jadwal_ujian ON jadwal_ujian.id = generate_workstation.jadwal_ujian_id
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         ORDER BY generate_workstation.generated_at DESC
         LIMIT ' . $perPage . ' OFFSET ' . $offset
    )->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Hasil generate belum dapat dimuat.';
    $totalRows = 0;
    $totalPages = 1;
}

renderView('results/index', [
    'pageTitle' => 'Hasil Generate',
    'results' => $results,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'perPage' => $perPage,
    'error' => $error,
]);
