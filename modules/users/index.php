<?php

declare(strict_types=1);

requireRole('admin');

$search = trim((string) ($_GET['search'] ?? ''));
$users = [];
$error = null;

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $statement = $database->prepare(
        'SELECT id, username, nama_lengkap, role, last_login_at, created_at
         FROM users
         WHERE username LIKE :search_username OR nama_lengkap LIKE :search_nama
         ORDER BY nama_lengkap ASC'
    );
    $searchValue = '%' . $search . '%';
    $statement->execute(['search_username' => $searchValue, 'search_nama' => $searchValue]);
    $users = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Data pengguna belum dapat dimuat.';
}

renderView('users/index', [
    'pageTitle' => 'Manajemen User Admin',
    'users' => $users,
    'search' => $search,
    'error' => $error,
]);
