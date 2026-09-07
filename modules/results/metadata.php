<?php

declare(strict_types=1);

requireRole('admin');
$generateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($generateId === false || $generateId === null || $generateId < 1) { http_response_code(400); exit('Invalid result ID'); }
$database = createDatabaseConnection(databaseConfigFromEnvironment());
$check = $database->prepare('SELECT id FROM generate_workstation WHERE id = :id');
$check->execute(['id' => $generateId]);
if ($check->fetchColumn() === false) { http_response_code(404); exit('Result not found'); }
$values = ['program_studi' => '', 'periode_akademik' => '', 'tahun_akademik' => '', 'semester' => '', 'waktu' => '', 'ruang' => '', 'kelompok' => '', 'kota' => 'Tasikmalaya', 'pengawas' => '', 'pengajar' => '', 'catatan' => ''];
$existing = $database->prepare(
    'SELECT program_studi, periode_akademik, tahun_akademik, semester, waktu, ruang, kelompok, kota,
            pengawas_json, pengajar_json, catatan_json
     FROM generate_export_metadata WHERE generate_id = :generate_id'
);
$existing->execute(['generate_id' => $generateId]);
$metadata = $existing->fetch();
if (is_array($metadata)) {
    foreach (['program_studi', 'periode_akademik', 'tahun_akademik', 'semester', 'waktu', 'ruang', 'kelompok', 'kota'] as $key) { $values[$key] = $metadata[$key]; }
    $values['pengawas'] = implode("\n", json_decode((string) $metadata['pengawas_json'], true) ?: []);
    $values['pengajar'] = implode("\n", json_decode((string) $metadata['pengajar_json'], true) ?: []);
    $values['catatan'] = implode("\n", json_decode((string) $metadata['catatan_json'], true) ?: []);
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($values) as $key) { $values[$key] = trim((string) ($_POST[$key] ?? '')); }
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); $error = 'Permintaan tidak valid.'; }
    else {
        $upsert = $database->prepare(
            'INSERT INTO generate_export_metadata (generate_id, program_studi, periode_akademik, tahun_akademik, semester, waktu, ruang, kelompok, kota, pengawas_json, pengajar_json, catatan_json)
             VALUES (:generate_id, :program_studi, :periode_akademik, :tahun_akademik, :semester, :waktu, :ruang, :kelompok, :kota, :pengawas_json, :pengajar_json, :catatan_json)
             ON DUPLICATE KEY UPDATE program_studi=VALUES(program_studi), periode_akademik=VALUES(periode_akademik), tahun_akademik=VALUES(tahun_akademik), semester=VALUES(semester), waktu=VALUES(waktu), ruang=VALUES(ruang), kelompok=VALUES(kelompok), kota=VALUES(kota), pengawas_json=VALUES(pengawas_json), pengajar_json=VALUES(pengajar_json), catatan_json=VALUES(catatan_json)'
        );
        $toJson = static fn (string $value): string => json_encode(array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: []))), JSON_THROW_ON_ERROR);
        try {
            $upsert->execute(['generate_id' => $generateId, 'program_studi' => $values['program_studi'], 'periode_akademik' => $values['periode_akademik'], 'tahun_akademik' => $values['tahun_akademik'], 'semester' => $values['semester'], 'waktu' => $values['waktu'], 'ruang' => $values['ruang'], 'kelompok' => $values['kelompok'], 'kota' => $values['kota'], 'pengawas_json' => $toJson($values['pengawas']), 'pengajar_json' => $toJson($values['pengajar']), 'catatan_json' => $toJson($values['catatan'])]);
            flashSuccess('Metadata dokumen berhasil disimpan.');
            header('Location: ' . $app['base_url'] . 'results/detail.php?id=' . $generateId, true, 303); exit;
        } catch (Throwable $exception) { error_log($exception->getMessage()); $error = 'Metadata belum dapat disimpan.'; }
    }
}
$dosenList = $database->query('SELECT nip, nama_dosen FROM dosen ORDER BY nama_dosen')->fetchAll();
renderView('results/metadata', ['pageTitle' => 'Metadata Dokumen Ujian', 'formAction' => $app['base_url'] . 'results/metadata.php?id=' . $generateId, 'generateId' => $generateId, 'values' => $values, 'dosenList' => $dosenList, 'error' => $error]);
