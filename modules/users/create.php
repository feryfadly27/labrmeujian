<?php

declare(strict_types=1);

requireRole('admin');

$values = ['username' => '', 'nama_lengkap' => '', 'role' => 'petugas'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['username'] = strtolower(trim((string) ($_POST['username'] ?? '')));
    $values['nama_lengkap'] = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $values['role'] = (string) ($_POST['role'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!preg_match('/^[a-z0-9._-]{3,50}$/', $values['username'])) {
        $error = 'Username minimal 3 karakter, hanya huruf kecil, angka, titik, garis bawah, atau tanda hubung.';
    } elseif ($values['nama_lengkap'] === '' || strlen($values['nama_lengkap']) > 150) {
        $error = 'Nama lengkap wajib diisi dan maksimal 150 karakter.';
    } elseif (!in_array($values['role'], ['admin', 'petugas'], true)) {
        $error = 'Role tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        try {
            $database = createDatabaseConnection(databaseConfigFromEnvironment());
            $statement = $database->prepare(
                'INSERT INTO users (username, password_hash, nama_lengkap, role)
                 VALUES (:username, :password_hash, :nama_lengkap, :role)'
            );
            $statement->execute([
                'username' => $values['username'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'nama_lengkap' => $values['nama_lengkap'],
                'role' => $values['role'],
            ]);
            $newUserId = (int) $database->lastInsertId();
            logAction($database, (int) currentUser()['id'], 'create_user', 'users', $newUserId, [
                'username' => $values['username'],
                'role' => $values['role'],
            ]);
            flashSuccess('User "' . $values['nama_lengkap'] . '" berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'users.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000'
                ? 'Username sudah digunakan.'
                : 'Data pengguna belum dapat disimpan.';
        }
    }
}

renderView('users/form', [
    'pageTitle' => 'Tambah User Admin',
    'formAction' => $app['base_url'] . 'users/create.php',
    'values' => $values,
    'isEdit' => false,
    'error' => $error,
]);
