<?php

declare(strict_types=1);

requireRole('admin');

$values = ['kode_kelas' => '', 'nama_kelas' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['kode_kelas'] = strtoupper(trim((string) ($_POST['kode_kelas'] ?? '')));
    $values['nama_kelas'] = trim((string) ($_POST['nama_kelas'] ?? ''));

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!preg_match('/^[A-Z0-9._-]{1,30}$/', $values['kode_kelas'])) {
        $error = 'Kode kelas hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif ($values['nama_kelas'] === '' || strlen($values['nama_kelas']) > 100) {
        $error = 'Nama kelas wajib diisi dan maksimal 100 karakter.';
    } else {
        try {
            $database = createDatabaseConnection(databaseConfigFromEnvironment());
            $statement = $database->prepare(
                'INSERT INTO kelas (kode_kelas, nama_kelas) VALUES (:kode_kelas, :nama_kelas)'
            );
            $statement->execute($values);
            flashSuccess('Kelas "' . $values['kode_kelas'] . '" berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'classes.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000'
                ? 'Kode kelas sudah digunakan.'
                : 'Data kelas belum dapat disimpan.';
        }
    }
}

renderView('classes/form', [
    'pageTitle' => 'Tambah Kelas',
    'formAction' => $app['base_url'] . 'classes/create.php',
    'values' => $values,
    'error' => $error,
]);
