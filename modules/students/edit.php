<?php

declare(strict_types=1);

requireRole('admin');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) { http_response_code(400); exit('Invalid student ID'); }
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$classes = $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll();
$find = $database->prepare('SELECT id, nim, nama_mahasiswa, kelas_id, angkatan FROM mahasiswa WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();
if (!is_array($values)) { http_response_code(404); exit('Student not found'); }
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nim'] = trim((string) ($_POST['nim'] ?? ''));
    $values['nama_mahasiswa'] = trim((string) ($_POST['nama_mahasiswa'] ?? ''));
    $values['kelas_id'] = (string) ($_POST['kelas_id'] ?? '');
    $values['angkatan'] = trim((string) ($_POST['angkatan'] ?? ''));
    $angkatan = $values['angkatan'] === '' ? null : filter_var($values['angkatan'], FILTER_VALIDATE_INT);
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); $error = 'Permintaan tidak valid.'; }
    elseif ($values['nim'] === '' || strlen($values['nim']) > 40 || $values['nama_mahasiswa'] === '' || strlen($values['nama_mahasiswa']) > 150) { $error = 'NIM dan nama mahasiswa wajib diisi dengan format yang benar.'; }
    elseif (filter_var($values['kelas_id'], FILTER_VALIDATE_INT) === false || $angkatan === false || ($angkatan !== null && ($angkatan < 1900 || $angkatan > 2200))) { $error = 'Kelas atau angkatan tidak valid.'; }
    else {
        try {
            $update = $database->prepare('UPDATE mahasiswa SET nim = :nim, nama_mahasiswa = :nama_mahasiswa, kelas_id = :kelas_id, angkatan = :angkatan WHERE id = :id');
            $update->execute(['id' => $id, 'nim' => $values['nim'], 'nama_mahasiswa' => $values['nama_mahasiswa'], 'kelas_id' => $values['kelas_id'], 'angkatan' => $angkatan]);
            flashSuccess('Mahasiswa "' . $values['nama_mahasiswa'] . '" berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'students.php', true, 303); exit;
        } catch (PDOException $exception) { error_log($exception->getMessage()); $error = $exception->getCode() === '23000' ? 'NIM sudah digunakan atau kelas tidak tersedia.' : 'Data mahasiswa belum dapat diubah.'; }
    }
}
renderView('students/form', ['pageTitle' => 'Ubah Mahasiswa', 'formAction' => $app['base_url'] . 'students/edit.php?id=' . $id, 'values' => $values, 'classes' => $classes, 'error' => $error]);
