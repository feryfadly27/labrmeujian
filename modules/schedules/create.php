<?php

declare(strict_types=1);

require_once __DIR__ . '/form_data.php';
requireRole('admin');
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$options = loadScheduleFormOptions($database);
$values = ['mata_kuliah_id' => '', 'kelas_id' => '', 'tanggal_ujian' => '', 'sesi' => '', 'keterangan' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $unused) { $values[$key] = trim((string) ($_POST[$key] ?? '')); }
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); $error = 'Permintaan tidak valid.'; }
    else { $error = validateScheduleValues($values); }
    if ($error === null) {
        try {
            $statement = $database->prepare('INSERT INTO jadwal_ujian (mata_kuliah_id, kelas_id, tanggal_ujian, sesi, keterangan) VALUES (:mata_kuliah_id, :kelas_id, :tanggal_ujian, :sesi, :keterangan)');
            $statement->execute($values);
            flashSuccess('Jadwal ujian berhasil ditambahkan.');
            header('Location: ' . $app['base_url'] . 'schedules.php', true, 303); exit;
        } catch (PDOException $exception) { error_log($exception->getMessage()); $error = $exception->getCode() === '23000' ? 'Jadwal dengan mata kuliah, kelas, dan tanggal tersebut sudah ada.' : 'Jadwal belum dapat disimpan.'; }
    }
}
renderView('schedules/form', ['pageTitle' => 'Tambah Jadwal Ujian', 'formAction' => $app['base_url'] . 'schedules/create.php', 'values' => $values, 'courses' => $options['courses'], 'classes' => $options['classes'], 'error' => $error]);
