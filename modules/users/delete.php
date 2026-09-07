<?php

declare(strict_types=1);

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid user ID');
}

if ($id === (int) currentUser()['id']) {
    http_response_code(400);
    exit('Anda tidak dapat menghapus akun Anda sendiri.');
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $find = $database->prepare('SELECT nama_lengkap FROM users WHERE id = :id');
    $find->execute(['id' => $id]);
    $deletedName = $find->fetchColumn();

    $statement = $database->prepare('DELETE FROM users WHERE id = :id');
    $statement->execute(['id' => $id]);

    logAction($database, (int) currentUser()['id'], 'delete_user', 'users', $id);

    flashSuccess(($deletedName !== false ? 'User "' . $deletedName . '"' : 'User') . ' berhasil dihapus.');
    header('Location: ' . $app['base_url'] . 'users.php', true, 303);
    exit;
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(409);
    exit('User ini tidak dapat dihapus karena memiliki riwayat generate workstation atau import data. Nonaktifkan aksesnya dengan mengubah password, atau hubungi administrator basis data.');
}
