<?php

declare(strict_types=1);

function loadScheduleFormOptions(PDO $database): array
{
    return [
        'courses' => $database->query('SELECT id, kode_mk, nama_mk FROM mata_kuliah ORDER BY kode_mk')->fetchAll(),
        'classes' => $database->query('SELECT id, kode_kelas, nama_kelas FROM kelas ORDER BY kode_kelas')->fetchAll(),
    ];
}

function validateScheduleValues(array $values): ?string
{
    if (filter_var($values['mata_kuliah_id'], FILTER_VALIDATE_INT) === false
        || filter_var($values['kelas_id'], FILTER_VALIDATE_INT) === false) {
        return 'Mata kuliah dan kelas wajib dipilih.';
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['tanggal_ujian']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $date->format('Y-m-d') !== $values['tanggal_ujian']) {
        return 'Tanggal ujian tidak valid.';
    }

    if (strlen($values['sesi']) > 50 || strlen($values['keterangan']) > 255) {
        return 'Sesi atau keterangan melebihi batas karakter.';
    }

    return null;
}
