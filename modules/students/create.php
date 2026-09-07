<?php

declare(strict_types=1);

requireRole('admin');
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$classes = $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll();
$values = ['nim' => '', 'nama_mahasiswa' => '', 'kelas_id' => '', 'angkatan' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nim'] = trim((string) ($_POST['nim'] ?? ''));
    $values['nama_mahasiswa'] = trim((string) ($_POST['nama_mahasiswa'] ?? ''));
    $values['kelas_id'] = (string) ($_POST['kelas_id'] ?? '');
    $values['angkatan'] = trim((string) ($_POST['angkatan'] ?? ''));
    $angkatan = $values['angkatan'] === '' ? null : filter_var($values['angkatan'], FILTER_VALIDATE_INT);

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403); $error = 'Permintaan tidak valid.';
    } elseif ($values['nim'] === '' || strlen($values['nim']) > 40 || $values['nama_mahasiswa'] === '' || strlen($values['nama_mahasiswa']) > 150) {
        $error = 'NIM dan nama mahasiswa wajib diisi dengan format yang benar.';
    } elseif (filter_var($values['kelas_id'], FILTER_VALIDATE_INT) === false || $angkatan === false || ($angkatan !== null && ($angkatan < 1900 || $angkatan > 2200))) {
        $error = 'Kelas atau angkatan tidak valid.';
    } else {
        try {
            $statement = $database->prepare('INSERT INTO mahasiswa (nim, nama_mahasiswa, kelas_id, angkatan) VALUES (:nim, :nama_mahasiswa, :kelas_id, :angkatan)');
            $statement->execute(['nim' => $values['nim'], 'nama_mahasiswa' => $values['nama_mahasiswa'], 'kelas_id' => $values['kelas_id'], 'angkatan' => $angkatan]);
            flashSuccess('Mahasiswa "' . $values['nama_mahasiswa'] . '" berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'students.php', true, 303); exit;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === '23000' ? 'NIM sudah digunakan atau kelas tidak tersedia.' : 'Data mahasiswa belum dapat disimpan.';
        }
    }
}

renderView('students/form', ['pageTitle' => 'Tambah Mahasiswa', 'formAction' => $app['base_url'] . 'students/create.php', 'values' => $values, 'classes' => $classes, 'error' => $error]);
