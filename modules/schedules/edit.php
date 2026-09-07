<?php

declare(strict_types=1);

require_once __DIR__ . '/form_data.php';
requireRole('admin');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) { http_response_code(400); exit('Invalid schedule ID'); }
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$options = loadScheduleFormOptions($database);
$find = $database->prepare('SELECT id, mata_kuliah_id, kelas_id, tanggal_ujian, sesi, keterangan FROM jadwal_ujian WHERE id = :id');
$find->execute(['id' => $id]);
$values = $find->fetch();
if (!is_array($values)) { http_response_code(404); exit('Schedule not found'); }
$generatedCheck = $database->prepare('SELECT id FROM generate_workstation WHERE jadwal_ujian_id = :jadwal_id LIMIT 1');
$generatedCheck->execute(['jadwal_id' => $id]);
$locked = $generatedCheck->fetchColumn() !== false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        http_response_code(409);
        $error = 'Jadwal yang sudah memiliki hasil generate tidak dapat diubah.';
    }
    foreach ($values as $key => $unused) { if ($key !== 'id') { $values[$key] = trim((string) ($_POST[$key] ?? '')); } }
    if ($error === null && !verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); $error = 'Permintaan tidak valid.'; }
    elseif ($error === null) { $error = validateScheduleValues($values); }
    if ($error === null) {
        try {
            $update = $database->prepare('UPDATE jadwal_ujian SET mata_kuliah_id = :mata_kuliah_id, kelas_id = :kelas_id, tanggal_ujian = :tanggal_ujian, sesi = :sesi, keterangan = :keterangan WHERE id = :id');
            $update->execute(['id' => $id, 'mata_kuliah_id' => $values['mata_kuliah_id'], 'kelas_id' => $values['kelas_id'], 'tanggal_ujian' => $values['tanggal_ujian'], 'sesi' => $values['sesi'] ?: null, 'keterangan' => $values['keterangan'] ?: null]);
            flashSuccess('Jadwal ujian berhasil diperbarui.');
            header('Location: ' . $app['base_url'] . 'schedules.php', true, 303); exit;
        } catch (PDOException $exception) { error_log($exception->getMessage()); $error = $exception->getCode() === '23000' ? 'Jadwal dengan kombinasi tersebut sudah ada.' : 'Jadwal belum dapat diubah.'; }
    }
}
renderView('schedules/form', ['pageTitle' => 'Ubah Jadwal Ujian', 'formAction' => $app['base_url'] . 'schedules/edit.php?id=' . $id, 'values' => $values, 'courses' => $options['courses'], 'classes' => $options['classes'], 'error' => $error, 'locked' => $locked]);
