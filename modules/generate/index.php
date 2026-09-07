<?php

declare(strict_types=1);

require_once BASE_PATH . '/src/Generate/WorkstationGenerator.php';
requireRole('admin');

$error = null;
$values = ['jenis_ujian' => 'UAS', 'mata_kuliah_id' => '', 'kelas_id' => '', 'tanggal_ujian' => date('Y-m-d'), 'waktu' => '08:00 - 12:00', 'ruang' => 'LAB RME RMIK', 'sesi' => '1', 'kelompok' => '', 'program_studi' => 'RMIK', 'tahun_akademik' => '', 'semester' => '', 'pengawas' => '', 'pengajar' => '', 'catatan' => ''];

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (array_keys($values) as $key) { $values[$key] = trim((string) ($_POST[$key] ?? '')); }
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            $error = 'Permintaan tidak valid.';
        } else {
            try {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['tanggal_ujian']);
                $dateErrors = DateTimeImmutable::getLastErrors();
                if (filter_var($values['mata_kuliah_id'], FILTER_VALIDATE_INT) === false || filter_var($values['kelas_id'], FILTER_VALIDATE_INT) === false) {
                    throw new InvalidArgumentException('Mata kuliah dan kelas wajib dipilih.');
                }
                if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $date->format('Y-m-d') !== $values['tanggal_ujian']) {
                    throw new InvalidArgumentException('Tanggal ujian tidak valid.');
                }
                $splitLines = static fn (string $value): array => array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: [])));
                $scheduleInput = ['mata_kuliah_id' => $values['mata_kuliah_id'], 'kelas_id' => $values['kelas_id'], 'tanggal_ujian' => $values['tanggal_ujian'], 'sesi' => $values['sesi'], 'keterangan' => $values['catatan']];
                $metadata = ['jenis_ujian' => $values['jenis_ujian'], 'program_studi' => $values['program_studi'], 'periode_akademik' => $values['tahun_akademik'] . ($values['semester'] !== '' ? ' ' . $values['semester'] : ''), 'tahun_akademik' => $values['tahun_akademik'], 'semester' => $values['semester'], 'waktu' => $values['waktu'], 'ruang' => $values['ruang'], 'kelompok' => $values['kelompok'], 'kota' => 'Tasikmalaya', 'pengawas' => $splitLines($values['pengawas']), 'pengajar' => $splitLines($values['pengajar']), 'catatan' => $splitLines($values['catatan'])];
                $generateId = generateWorkstationFromInput($database, $scheduleInput, $metadata, (int) currentUser()['id']);
                flashSuccess('Workstation berhasil digenerate.');
                header('Location: results/detail.php?id=' . $generateId, true, 303);
                exit;
            } catch (DomainException | InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
                $error = $exception->getCode() === '23000' ? 'Kombinasi mata kuliah, kelas, dan tanggal tersebut sudah digunakan.' : 'Generate belum dapat diselesaikan.';
            }
        }
    }

    $courses = $database->query('SELECT id, kode_mk, nama_mk FROM mata_kuliah ORDER BY kode_mk')->fetchAll();
    $classes = $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll();
    $dosenList = $database->query('SELECT nip, nama_dosen FROM dosen ORDER BY nama_dosen')->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $courses = $courses ?? [];
    $classes = $classes ?? [];
    $dosenList = $dosenList ?? [];
    $error = 'Data jadwal belum dapat dimuat.';
}

renderView('generate/input', [
    'pageTitle' => 'Generate Workstation',
    'courses' => $courses,
    'classes' => $classes,
    'dosenList' => $dosenList,
    'values' => $values,
    'error' => $error,
]);
