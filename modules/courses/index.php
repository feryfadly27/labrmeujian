<?php

declare(strict_types=1);

requireAuthentication();

$search = trim((string) ($_GET['search'] ?? ''));
$courses = [];
$error = null;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $statement = $database->prepare(
        'SELECT id, kode_mk, nama_mk
         FROM mata_kuliah
         WHERE kode_mk LIKE :search_code OR nama_mk LIKE :search_name
         ORDER BY kode_mk'
    );
    $statement->execute([
        'search_code' => '%' . $search . '%',
        'search_name' => '%' . $search . '%',
    ]);
    $courses = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Data mata kuliah belum dapat dimuat.';
}

renderView('courses/index', [
    'pageTitle' => 'Mata Kuliah',
    'courses' => $courses,
    'search' => $search,
    'error' => $error,
]);
