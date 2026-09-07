<?php

declare(strict_types=1);

requireAuthentication();

$search = trim((string) ($_GET['search'] ?? ''));
$dosenList = [];
$error = null;

$perPage = 20;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $whereClause = ' WHERE nip LIKE :search_nip OR nama_dosen LIKE :search_nama';
    $parameters = [
        'search_nip' => '%' . $search . '%',
        'search_nama' => '%' . $search . '%',
    ];

    $countStatement = $database->prepare('SELECT COUNT(*) FROM dosen' . $whereClause);
    $countStatement->execute($parameters);
    $totalRows = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $statement = $database->prepare(
        'SELECT id, nip, nama_dosen FROM dosen'
        . $whereClause
        . ' ORDER BY nama_dosen ASC'
        . ' LIMIT ' . $perPage . ' OFFSET ' . $offset
    );
    $statement->execute($parameters);
    $dosenList = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Data dosen belum dapat dimuat.';
    $totalRows = 0;
    $totalPages = 1;
}

renderView('dosen/index', [
    'pageTitle' => 'Data Dosen',
    'dosenList' => $dosenList,
    'search' => $search,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'perPage' => $perPage,
    'error' => $error,
]);
