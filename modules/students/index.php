<?php

declare(strict_types=1);

requireAuthentication();

$search = trim((string) ($_GET['search'] ?? ''));
$kelasId = (string) ($_GET['kelas_id'] ?? '');
$angkatan = trim((string) ($_GET['angkatan'] ?? ''));

$sortColumns = [
    'nim' => 'mahasiswa.nim',
    'nama_mahasiswa' => 'mahasiswa.nama_mahasiswa',
    'kode_kelas' => 'kelas.kode_kelas',
    'angkatan' => 'mahasiswa.angkatan',
];
$sort = (string) ($_GET['sort'] ?? 'nama_mahasiswa');
if (!isset($sortColumns[$sort])) {
    $sort = 'nama_mahasiswa';
}
$dir = strtolower((string) ($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

$perPage = 20;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

$students = [];
$classes = [];
$angkatanList = [];
$error = null;
$totalRows = 0;
$totalPages = 1;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $classes = $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll();
    $angkatanList = $database->query('SELECT DISTINCT angkatan FROM mahasiswa WHERE angkatan IS NOT NULL ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN);

    $whereClause = ' WHERE (mahasiswa.nim LIKE :search_nim OR mahasiswa.nama_mahasiswa LIKE :search_name)';
    $parameters = [
        'search_nim' => '%' . $search . '%',
        'search_name' => '%' . $search . '%',
    ];

    if ($kelasId !== '' && filter_var($kelasId, FILTER_VALIDATE_INT) !== false) {
        $whereClause .= ' AND mahasiswa.kelas_id = :kelas_id';
        $parameters['kelas_id'] = (int) $kelasId;
    }

    if ($angkatan !== '' && filter_var($angkatan, FILTER_VALIDATE_INT) !== false) {
        $whereClause .= ' AND mahasiswa.angkatan = :angkatan';
        $parameters['angkatan'] = (int) $angkatan;
    }

    $countStatement = $database->prepare(
        'SELECT COUNT(*)
         FROM mahasiswa INNER JOIN kelas ON kelas.id = mahasiswa.kelas_id'
        . $whereClause
    );
    $countStatement->execute($parameters);
    $totalRows = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $query =
        'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama_mahasiswa, mahasiswa.angkatan, kelas.kode_kelas
         FROM mahasiswa INNER JOIN kelas ON kelas.id = mahasiswa.kelas_id'
        . $whereClause
        . ' ORDER BY ' . $sortColumns[$sort] . ' ' . strtoupper($dir)
        . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;
    $statement = $database->prepare($query);
    $statement->execute($parameters);
    $students = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Data mahasiswa belum dapat dimuat.';
}

renderView('students/index', [
    'pageTitle' => 'Data Mahasiswa',
    'students' => $students,
    'classes' => $classes,
    'angkatanList' => $angkatanList,
    'search' => $search,
    'kelasId' => $kelasId,
    'angkatan' => $angkatan,
    'sort' => $sort,
    'dir' => $dir,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'perPage' => $perPage,
    'error' => $error,
]);
