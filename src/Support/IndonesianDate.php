<?php

declare(strict_types=1);

function indonesianDayName(string $date): string
{
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    $englishDay = (new DateTimeImmutable($date))->format('l');

    return $days[$englishDay] ?? $englishDay;
}

function indonesianMonthName(string $date): string
{
    $months = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
    ];

    $englishMonth = (new DateTimeImmutable($date))->format('F');

    return $months[$englishMonth] ?? $englishMonth;
}

function formatIndonesianDate(string $date): string
{
    $day = indonesianDayName($date);
    $dateTime = new DateTimeImmutable($date);
    $month = indonesianMonthName($date);

    return sprintf('%s, %d %s %s', $day, (int) $dateTime->format('j'), $month, $dateTime->format('Y'));
}
