<?php

declare(strict_types=1);

requireRole('admin');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) { http_response_code(400); exit('Invalid course ID'); }
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$find = $database->prepare('SELECT id, kode_mk, nama_mk FROM mata_kuliah WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();
if (!is_array($values)) { http_response_code(404); exit('Course not found'); }
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['kode_mk'] = strtoupper(trim((string) ($_POST['kode_mk'] ?? '')));
    $values['nama_mk'] = trim((string) ($_POST['nama_mk'] ?? ''));
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); $error = 'Permintaan tidak valid.'; }
    elseif (!preg_match('/^[A-Z0-9._-]{1,30}$/', $values['kode_mk'])) { $error = 'Kode mata kuliah tidak valid.'; }
    elseif ($values['nama_mk'] === '' || strlen($values['nama_mk']) > 150) { $error = 'Nama mata kuliah wajib diisi dan maksimal 150 karakter.'; }
    else {
        try {
            $update = $database->prepare('UPDATE mata_kuliah SET kode_mk = :kode_mk, nama_mk = :nama_mk WHERE id = :id');
            $update->execute(['id' => $id, 'kode_mk' => $values['kode_mk'], 'nama_mk' => $values['nama_mk']]);
            flashSuccess('Mata kuliah "' . $values['kode_mk'] . '" berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'courses.php', true, 303); exit;
        } catch (PDOException $exception) { error_log($exception->getMessage()); $error = $exception->getCode() === '23000' ? 'Kode mata kuliah sudah digunakan.' : 'Data mata kuliah belum dapat diubah.'; }
    }
}

renderView('courses/form', [
    'pageTitle' => 'Ubah Mata Kuliah',
    'formAction' => $app['base_url'] . 'courses/edit.php?id=' . $id,
    'values' => $values,
    'error' => $error,
]);
