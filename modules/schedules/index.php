<?php

declare(strict_types=1);

requireAuthentication();

$search = trim((string) ($_GET['search'] ?? ''));
$schedules = [];
$error = null;

$perPage = 20;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $whereClause =
        ' WHERE mata_kuliah.kode_mk LIKE :search_code
            OR mata_kuliah.nama_mk LIKE :search_name
            OR kelas.kode_kelas LIKE :search_class
            OR jadwal_ujian.tanggal_ujian LIKE :search_date';
    $parameters = [
        'search_code' => '%' . $search . '%',
        'search_name' => '%' . $search . '%',
        'search_class' => '%' . $search . '%',
        'search_date' => '%' . $search . '%',
    ];

    $countStatement = $database->prepare(
        'SELECT COUNT(*)
         FROM jadwal_ujian
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id'
        . $whereClause
    );
    $countStatement->execute($parameters);
    $totalRows = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $statement = $database->prepare(
        'SELECT jadwal_ujian.id, jadwal_ujian.tanggal_ujian, jadwal_ujian.sesi,
                mata_kuliah.kode_mk, mata_kuliah.nama_mk, kelas.kode_kelas
         FROM jadwal_ujian
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id'
        . $whereClause
        . ' ORDER BY jadwal_ujian.tanggal_ujian DESC
         LIMIT ' . $perPage . ' OFFSET ' . $offset
    );
    $statement->execute($parameters);
    $schedules = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Jadwal ujian belum dapat dimuat.';
    $totalRows = 0;
    $totalPages = 1;
}

renderView('schedules/index', [
    'pageTitle' => 'Jadwal Ujian',
    'schedules' => $schedules,
    'search' => $search,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'perPage' => $perPage,
    'error' => $error,
]);
