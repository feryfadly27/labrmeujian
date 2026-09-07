<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;

function importStudentsFromXlsx(PDO $database, string $temporaryPath, string $originalFilename, string $storedFilename, int $userId): array
{
    $requiredHeaders = ['nim', 'nama_mahasiswa', 'kode_kelas', 'angkatan'];
    $spreadsheet = IOFactory::load($temporaryPath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray(null, true, true, true);
    $header = array_shift($rows);
    $normalizedHeaders = [];

    foreach ($header as $column => $value) {
        $normalizedHeaders[strtolower(trim((string) $value))] = $column;
    }

    foreach ($requiredHeaders as $requiredHeader) {
        if (!isset($normalizedHeaders[$requiredHeader])) {
            throw new InvalidArgumentException('Header wajib tidak lengkap. Gunakan: nim, nama_mahasiswa, kode_kelas, angkatan.');
        }
    }

    $classStatement = $database->query('SELECT id, kode_kelas FROM kelas');
    $classMap = [];
    foreach ($classStatement->fetchAll() as $class) {
        $classMap[strtoupper($class['kode_kelas'])] = (int) $class['id'];
    }

    $existingNimStatement = $database->query('SELECT nim FROM mahasiswa');
    $knownNim = array_fill_keys($existingNimStatement->fetchAll(PDO::FETCH_COLUMN), true);
    $insert = $database->prepare(
        'INSERT INTO mahasiswa (nim, nama_mahasiswa, kelas_id, angkatan)
         VALUES (:nim, :nama_mahasiswa, :kelas_id, :angkatan)'
    );
    $result = ['total_rows' => 0, 'success_rows' => 0, 'failed_rows' => 0, 'errors' => []];
    $seenNim = [];

    $database->beginTransaction();
    try {
        foreach ($rows as $rowNumber => $row) {
            $excelRow = $rowNumber + 2;
            $hasContent = count(array_filter($row, static fn ($value): bool => trim((string) $value) !== '')) > 0;
            if (!$hasContent) {
                continue;
            }

            $result['total_rows']++;
            $nim = trim((string) ($row[$normalizedHeaders['nim']] ?? ''));
            $name = trim((string) ($row[$normalizedHeaders['nama_mahasiswa']] ?? ''));
            $classCode = strtoupper(trim((string) ($row[$normalizedHeaders['kode_kelas']] ?? '')));
            $generation = trim((string) ($row[$normalizedHeaders['angkatan']] ?? ''));
            $angkatan = filter_var($generation, FILTER_VALIDATE_INT);
            $error = null;

            if ($nim === '' || strlen($nim) > 40) {
                $error = 'NIM kosong atau terlalu panjang.';
            } elseif (isset($knownNim[$nim]) || isset($seenNim[$nim])) {
                $error = 'NIM sudah terdaftar atau duplikat dalam file.';
            } elseif ($name === '' || strlen($name) > 150) {
                $error = 'Nama mahasiswa kosong atau terlalu panjang.';
            } elseif (!isset($classMap[$classCode])) {
                $error = 'Kode kelas tidak ditemukan di master kelas.';
            } elseif ($angkatan === false || $angkatan < 1900 || $angkatan > 2200) {
                $error = 'Angkatan tidak valid.';
            }

            if ($error !== null) {
                $result['failed_rows']++;
                $result['errors'][] = ['row' => $excelRow, 'message' => $error];
                continue;
            }

            $insert->execute([
                'nim' => $nim,
                'nama_mahasiswa' => $name,
                'kelas_id' => $classMap[$classCode],
                'angkatan' => $angkatan,
            ]);
            $seenNim[$nim] = true;
            $result['success_rows']++;
        }

        $log = $database->prepare(
            'INSERT INTO import_log (imported_by, original_filename, stored_filename, file_format, total_rows, success_rows, failed_rows)
             VALUES (:imported_by, :original_filename, :stored_filename, "xlsx", :total_rows, :success_rows, :failed_rows)'
        );
        $log->execute([
            'imported_by' => $userId,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'total_rows' => $result['total_rows'],
            'success_rows' => $result['success_rows'],
            'failed_rows' => $result['failed_rows'],
        ]);
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }

    return $result;
}
