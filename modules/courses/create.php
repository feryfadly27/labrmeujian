<?php

declare(strict_types=1);

requireRole('admin');
$values = ['kode_mk' => '', 'nama_mk' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['kode_mk'] = strtoupper(trim((string) ($_POST['kode_mk'] ?? '')));
    $values['nama_mk'] = trim((string) ($_POST['nama_mk'] ?? ''));

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!preg_match('/^[A-Z0-9._-]{1,30}$/', $values['kode_mk'])) {
        $error = 'Kode mata kuliah hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif ($values['nama_mk'] === '' || strlen($values['nama_mk']) > 150) {
        $error = 'Nama mata kuliah wajib diisi dan maksimal 150 karakter.';
    } else {
        try {
            $database = createDatabaseConnection(databaseConfigFromEnvironment());
            $statement = $database->prepare('INSERT INTO mata_kuliah (kode_mk, nama_mk) VALUES (:kode_mk, :nama_mk)');
            $statement->execute($values);
            flashSuccess('Mata kuliah "' . $values['kode_mk'] . '" berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'courses.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000' ? 'Kode mata kuliah sudah digunakan.' : 'Data mata kuliah belum dapat disimpan.';
        }
    }
}

renderView('courses/form', [
    'pageTitle' => 'Tambah Mata Kuliah',
    'formAction' => $app['base_url'] . 'courses/create.php',
    'values' => $values,
    'error' => $error,
]);
