<?php

declare(strict_types=1);

requireAuthentication();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid result ID');
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $resultStatement = $database->prepare(
        'SELECT generate_workstation.id, generate_workstation.generated_at,
                jadwal_ujian.tanggal_ujian, mata_kuliah.kode_mk, mata_kuliah.nama_mk, kelas.kode_kelas
         FROM generate_workstation
         INNER JOIN jadwal_ujian ON jadwal_ujian.id = generate_workstation.jadwal_ujian_id
         INNER JOIN mata_kuliah ON mata_kuliah.id = jadwal_ujian.mata_kuliah_id
         INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
         WHERE generate_workstation.id = :id'
    );
    $resultStatement->execute(['id' => $id]);
    $result = $resultStatement->fetch();
    if (!is_array($result)) {
        http_response_code(404);
        exit('Result not found');
    }

    $detailStatement = $database->prepare(
        'SELECT nomor_urut, workstation_no, nim_snapshot, nama_snapshot
         FROM generate_workstation_detail
         WHERE generate_id = :generate_id
         ORDER BY nim_snapshot'
    );
    $detailStatement->execute(['generate_id' => $id]);
    $details = $detailStatement->fetchAll();

    renderView('results/detail', [
        'pageTitle' => 'Detail Hasil Generate',
        'result' => $result,
        'details' => $details,
    ]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Hasil generate belum dapat dimuat.');
}
