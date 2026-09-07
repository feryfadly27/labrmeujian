<?php

declare(strict_types=1);

function generateWorkstationResult(PDO $database, int $scheduleId, int $userId): int
{
    $database->beginTransaction();

    try {
        $scheduleStatement = $database->prepare(
            'SELECT jadwal_ujian.id, jadwal_ujian.mata_kuliah_id, jadwal_ujian.kelas_id,
                    jadwal_ujian.tanggal_ujian, kelas.kode_kelas
             FROM jadwal_ujian
             INNER JOIN kelas ON kelas.id = jadwal_ujian.kelas_id
             WHERE jadwal_ujian.id = :id
             FOR UPDATE'
        );
        $scheduleStatement->execute(['id' => $scheduleId]);
        $schedule = $scheduleStatement->fetch();
        if (!is_array($schedule)) {
            throw new InvalidArgumentException('Jadwal ujian tidak ditemukan.');
        }

        $existingStatement = $database->prepare('SELECT id FROM generate_workstation WHERE jadwal_ujian_id = :jadwal_id LIMIT 1');
        $existingStatement->execute(['jadwal_id' => $scheduleId]);
        if ($existingStatement->fetchColumn() !== false) {
            throw new DomainException('Jadwal ini sudah memiliki hasil generate.');
        }

        $studentStatement = $database->prepare(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama_mahasiswa, kelas.kode_kelas
             FROM mahasiswa
             INNER JOIN kelas ON kelas.id = mahasiswa.kelas_id
             WHERE mahasiswa.kelas_id = :kelas_id
             ORDER BY mahasiswa.id'
        );
        $studentStatement->execute(['kelas_id' => $schedule['kelas_id']]);
        $students = $studentStatement->fetchAll();
        if ($students === []) {
            throw new DomainException('Kelas pada jadwal ini belum memiliki mahasiswa.');
        }

        for ($index = count($students) - 1; $index > 0; $index--) {
            $randomIndex = random_int(0, $index);
            [$students[$index], $students[$randomIndex]] = [$students[$randomIndex], $students[$index]];
        }

        $generateStatement = $database->prepare(
            'INSERT INTO generate_workstation (jadwal_ujian_id, generated_by)
             VALUES (:jadwal_id, :generated_by)'
        );
        $generateStatement->execute(['jadwal_id' => $scheduleId, 'generated_by' => $userId]);
        $generateId = (int) $database->lastInsertId();

        $detailStatement = $database->prepare(
            'INSERT INTO generate_workstation_detail
             (generate_id, mahasiswa_id, nim_snapshot, nama_snapshot, kode_kelas_snapshot, nomor_urut, workstation_no)
             VALUES (:generate_id, :mahasiswa_id, :nim, :nama, :kode_kelas, :nomor_urut, :workstation_no)'
        );
        foreach ($students as $index => $student) {
            $number = $index + 1;
            $detailStatement->execute([
                'generate_id' => $generateId,
                'mahasiswa_id' => $student['id'],
                'nim' => $student['nim'],
                'nama' => $student['nama_mahasiswa'],
                'kode_kelas' => $student['kode_kelas'],
                'nomor_urut' => $number,
                'workstation_no' => $number,
            ]);
        }

        $database->commit();
        return $generateId;
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function generateWorkstationFromInput(PDO $database, array $input, array $metadata, int $userId): int
{
    $database->beginTransaction();

    try {
        $scheduleInsert = $database->prepare(
            'INSERT INTO jadwal_ujian (mata_kuliah_id, kelas_id, tanggal_ujian, sesi, keterangan)
             VALUES (:mata_kuliah_id, :kelas_id, :tanggal_ujian, :sesi, :keterangan)'
        );
        $scheduleInsert->execute([
            'mata_kuliah_id' => $input['mata_kuliah_id'],
            'kelas_id' => $input['kelas_id'],
            'tanggal_ujian' => $input['tanggal_ujian'],
            'sesi' => $input['sesi'] ?: null,
            'keterangan' => $input['keterangan'] ?: null,
        ]);
        $scheduleId = (int) $database->lastInsertId();

        $studentStatement = $database->prepare(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama_mahasiswa, kelas.kode_kelas
             FROM mahasiswa INNER JOIN kelas ON kelas.id = mahasiswa.kelas_id
             WHERE mahasiswa.kelas_id = :kelas_id ORDER BY mahasiswa.id'
        );
        $studentStatement->execute(['kelas_id' => $input['kelas_id']]);
        $students = $studentStatement->fetchAll();
        if ($students === []) {
            throw new DomainException('Kelas pada jadwal ini belum memiliki mahasiswa.');
        }

        for ($index = count($students) - 1; $index > 0; $index--) {
            $randomIndex = random_int(0, $index);
            [$students[$index], $students[$randomIndex]] = [$students[$randomIndex], $students[$index]];
        }

        $generate = $database->prepare('INSERT INTO generate_workstation (jadwal_ujian_id, generated_by) VALUES (:jadwal_id, :generated_by)');
        $generate->execute(['jadwal_id' => $scheduleId, 'generated_by' => $userId]);
        $generateId = (int) $database->lastInsertId();
        $detail = $database->prepare(
            'INSERT INTO generate_workstation_detail
             (generate_id, mahasiswa_id, nim_snapshot, nama_snapshot, kode_kelas_snapshot, nomor_urut, workstation_no)
             VALUES (:generate_id, :mahasiswa_id, :nim, :nama, :kode_kelas, :nomor_urut, :workstation_no)'
        );
        foreach ($students as $index => $student) {
            $number = $index + 1;
            $detail->execute(['generate_id' => $generateId, 'mahasiswa_id' => $student['id'], 'nim' => $student['nim'], 'nama' => $student['nama_mahasiswa'], 'kode_kelas' => $student['kode_kelas'], 'nomor_urut' => $number, 'workstation_no' => $number]);
        }

        $metadataInsert = $database->prepare(
            'INSERT INTO generate_export_metadata
             (generate_id, jenis_ujian, program_studi, periode_akademik, tahun_akademik, semester, waktu, ruang, kelompok, kota, pengawas_json, pengajar_json, catatan_json)
             VALUES (:generate_id, :jenis_ujian, :program_studi, :periode_akademik, :tahun_akademik, :semester, :waktu, :ruang, :kelompok, :kota, :pengawas_json, :pengajar_json, :catatan_json)'
        );
        $metadataInsert->execute([
            'generate_id' => $generateId,
            'jenis_ujian' => $metadata['jenis_ujian'],
            'program_studi' => $metadata['program_studi'],
            'periode_akademik' => $metadata['periode_akademik'],
            'tahun_akademik' => $metadata['tahun_akademik'],
            'semester' => $metadata['semester'],
            'waktu' => $metadata['waktu'],
            'ruang' => $metadata['ruang'],
            'kelompok' => $metadata['kelompok'],
            'kota' => $metadata['kota'],
            'pengawas_json' => json_encode($metadata['pengawas'], JSON_THROW_ON_ERROR),
            'pengajar_json' => json_encode($metadata['pengajar'], JSON_THROW_ON_ERROR),
            'catatan_json' => json_encode($metadata['catatan'], JSON_THROW_ON_ERROR),
        ]);

        logAction($database, $userId, 'generate_workstation', 'generate_workstation', $generateId, [
            'jadwal_ujian_id' => $scheduleId,
            'mata_kuliah_id' => (int) $input['mata_kuliah_id'],
            'kelas_id' => (int) $input['kelas_id'],
            'tanggal_ujian' => $input['tanggal_ujian'],
            'jumlah_peserta' => count($students),
        ]);

        $database->commit();
        return $generateId;
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}
