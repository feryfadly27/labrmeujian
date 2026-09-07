<?php

declare(strict_types=1);

requireRole('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid class ID');
}

$database = createDatabaseConnection(databaseConfigFromEnvironment());
$find = $database->prepare('SELECT id, kode_kelas, nama_kelas FROM kelas WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();

if (!is_array($values)) {
    http_response_code(404);
    exit('Class not found');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['kode_kelas'] = strtoupper(trim((string) ($_POST['kode_kelas'] ?? '')));
    $values['nama_kelas'] = trim((string) ($_POST['nama_kelas'] ?? ''));

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!preg_match('/^[A-Z0-9._-]{1,30}$/', $values['kode_kelas'])) {
        $error = 'Kode kelas tidak valid.';
    } elseif ($values['nama_kelas'] === '' || strlen($values['nama_kelas']) > 100) {
        $error = 'Nama kelas wajib diisi dan maksimal 100 karakter.';
    } else {
        try {
            $update = $database->prepare(
                'UPDATE kelas SET kode_kelas = :kode_kelas, nama_kelas = :nama_kelas WHERE id = :id'
            );
            $update->execute([
                'id' => $id,
                'kode_kelas' => $values['kode_kelas'],
                'nama_kelas' => $values['nama_kelas'],
            ]);
            flashSuccess('Kelas "' . $values['kode_kelas'] . '" berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'classes.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000'
                ? 'Kode kelas sudah digunakan.'
                : 'Data kelas belum dapat diubah.';
        }
    }
}

renderView('classes/form', [
    'pageTitle' => 'Ubah Kelas',
    'formAction' => $app['base_url'] . 'classes/edit.php?id=' . $id,
    'values' => $values,
    'error' => $error,
]);
