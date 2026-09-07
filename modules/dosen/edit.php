<?php

declare(strict_types=1);

requireRole('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid dosen ID');
}

$database = createDatabaseConnection(databaseConfigFromEnvironment());
$find = $database->prepare('SELECT id, nip, nama_dosen FROM dosen WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();

if (!is_array($values)) {
    http_response_code(404);
    exit('Dosen not found');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nip'] = trim((string) ($_POST['nip'] ?? ''));
    $values['nama_dosen'] = trim((string) ($_POST['nama_dosen'] ?? ''));

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!preg_match('/^[A-Za-z0-9 .\/-]{1,40}$/', $values['nip'])) {
        $error = 'NIP wajib diisi, maksimal 40 karakter (angka, huruf, titik, garis miring, atau tanda hubung).';
    } elseif ($values['nama_dosen'] === '' || strlen($values['nama_dosen']) > 150) {
        $error = 'Nama dosen wajib diisi dan maksimal 150 karakter.';
    } else {
        try {
            $update = $database->prepare(
                'UPDATE dosen SET nip = :nip, nama_dosen = :nama_dosen WHERE id = :id'
            );
            $update->execute([
                'id' => $id,
                'nip' => $values['nip'],
                'nama_dosen' => $values['nama_dosen'],
            ]);
            flashSuccess('Data dosen "' . $values['nama_dosen'] . '" berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'dosen.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000'
                ? 'NIP sudah digunakan.'
                : 'Data dosen belum dapat diubah.';
        }
    }
}

renderView('dosen/form', [
    'pageTitle' => 'Ubah Dosen',
    'formAction' => $app['base_url'] . 'dosen/edit.php?id=' . $id,
    'values' => $values,
    'error' => $error,
]);
