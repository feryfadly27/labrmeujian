<?php

declare(strict_types=1);

requireRole('admin');

$values = ['nip' => '', 'nama_dosen' => ''];
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
            $database = createDatabaseConnection(databaseConfigFromEnvironment());
            $statement = $database->prepare(
                'INSERT INTO dosen (nip, nama_dosen) VALUES (:nip, :nama_dosen)'
            );
            $statement->execute($values);
            flashSuccess('Dosen "' . $values['nama_dosen'] . '" berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'dosen.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000'
                ? 'NIP sudah digunakan.'
                : 'Data dosen belum dapat disimpan.';
        }
    }
}

renderView('dosen/form', [
    'pageTitle' => 'Tambah Dosen',
    'formAction' => $app['base_url'] . 'dosen/create.php',
    'values' => $values,
    'error' => $error,
]);
