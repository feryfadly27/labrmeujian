<?php

declare(strict_types=1);

requireAuthentication();

$search = trim((string) ($_GET['search'] ?? ''));
$classes = [];
$error = null;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $statement = $database->prepare(
        'SELECT id, kode_kelas, nama_kelas
         FROM kelas
            WHERE kode_kelas LIKE :search_code OR nama_kelas LIKE :search_name
         ORDER BY kode_kelas ASC'
    );
        $searchValue = '%' . $search . '%';
        $statement->execute(['search_code' => $searchValue, 'search_name' => $searchValue]);
    $classes = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Data kelas belum dapat dimuat.';
}

renderView('classes/index', [
    'pageTitle' => 'Data Kelas',
    'classes' => $classes,
    'search' => $search,
    'error' => $error,
]);
