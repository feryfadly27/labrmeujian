<?php

declare(strict_types=1);

requireRole('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid user ID');
}

$database = createDatabaseConnection(databaseConfigFromEnvironment());
$find = $database->prepare('SELECT id, username, nama_lengkap, role FROM users WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();

if (!is_array($values)) {
    http_response_code(404);
    exit('User not found');
}

$isSelf = $id === (int) currentUser()['id'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nama_lengkap'] = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $values['role'] = (string) ($_POST['role'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif ($values['nama_lengkap'] === '' || strlen($values['nama_lengkap']) > 150) {
        $error = 'Nama lengkap wajib diisi dan maksimal 150 karakter.';
    } elseif (!in_array($values['role'], ['admin', 'petugas'], true)) {
        $error = 'Role tidak valid.';
    } elseif ($isSelf && $values['role'] !== 'admin') {
        $error = 'Anda tidak dapat mengubah role akun Anda sendiri menjadi bukan admin.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } else {
        try {
            if ($password !== '') {
                $update = $database->prepare(
                    'UPDATE users SET nama_lengkap = :nama_lengkap, role = :role, password_hash = :password_hash WHERE id = :id'
                );
                $update->execute([
                    'id' => $id,
                    'nama_lengkap' => $values['nama_lengkap'],
                    'role' => $values['role'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $passwordChanged = true;
            } else {
                $update = $database->prepare(
                    'UPDATE users SET nama_lengkap = :nama_lengkap, role = :role WHERE id = :id'
                );
                $update->execute([
                    'id' => $id,
                    'nama_lengkap' => $values['nama_lengkap'],
                    'role' => $values['role'],
                ]);
                $passwordChanged = false;
            }

            logAction($database, (int) currentUser()['id'], 'update_user', 'users', $id, [
                'nama_lengkap' => $values['nama_lengkap'],
                'role' => $values['role'],
                'password_changed' => $passwordChanged,
            ]);

            flashSuccess('Data user "' . $values['nama_lengkap'] . '" berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'users.php', true, 303);
            exit;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'Data pengguna belum dapat diubah.';
        }
    }
}

renderView('users/form', [
    'pageTitle' => 'Ubah User Admin',
    'formAction' => $app['base_url'] . 'users/edit.php?id=' . $id,
    'values' => $values,
    'isEdit' => true,
    'isSelf' => $isSelf,
    'error' => $error,
]);
