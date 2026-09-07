<?php

$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$summary = $workbook->getActiveSheet();
$summary->setTitle('Berita Acara');
$result = $data['result'];
$metadata = $data['metadata'];
$examType = $metadata['jenis_ujian'] ?? 'UAS';
$excelValue = static function (mixed $value): mixed {
    if (is_string($value) && preg_match('/^[=+\-@]/', $value) === 1) {
        return "'" . $value;
    }
    return $value;
};
$summaryRows = [
    ['BERITA ACARA ' . $examType],
    ['Program Studi', $metadata['program_studi']],
    ['Mata Ujian', $result['kode_mk'] . ' - ' . $result['nama_mk']],
    ['Tanggal Ujian', attendanceDate($data)],
    ['Ruang', $metadata['ruang']],
    ['Tahun Akademik', $metadata['tahun_akademik']],
    ['Semester', $metadata['semester']],
    ['Waktu', $metadata['waktu']],
    ['Jumlah Peserta', count($data['details'])],
];
$cellAddress = static fn (int $columnIndex, int $rowIndex): string => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $rowIndex;
foreach ($summaryRows as $rowIndex => $row) { foreach ($row as $columnIndex => $value) { $summary->setCellValue($cellAddress($columnIndex + 1, $rowIndex + 1), $excelValue($value)); } }
$summary->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$summary->getColumnDimension('A')->setWidth(24);
$summary->getColumnDimension('B')->setWidth(50);
$headerDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$headerDrawing->setName('Header Institusi');
$headerDrawing->setDescription('Header institusi dari template referensi');
$headerDrawing->setPath(BASE_PATH . '/storage/templates/reference-header.png');
$headerDrawing->setHeight(95);
$headerDrawing->setCoordinates('A10');
$headerDrawing->setWorksheet($summary);

$sheet = $workbook->createSheet();
$sheet->setTitle('Daftar Hadir');
$rows = [['NO', 'NIM', 'NAMA', 'WORKSTATION PC', 'TANDA TANGAN']];
foreach ($data['details'] as $index => $detail) { $rows[] = [$index + 1, (string) $detail['nim_snapshot'], (string) $detail['nama_snapshot'], (int) $detail['workstation_no'], '']; }
foreach ($rows as $rowIndex => $row) { foreach ($row as $columnIndex => $value) { $sheet->setCellValue($cellAddress($columnIndex + 1, $rowIndex + 1), $excelValue($value)); } }
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E' . count($rows))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
foreach (['A' => 8, 'B' => 24, 'C' => 38, 'D' => 18, 'E' => 24] as $column => $width) { $sheet->getColumnDimension($column)->setWidth($width); }
$sheet->freezePane('A2');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="daftar-hadir-' . (int) $generateId . '.xlsx"');
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook))->save('php://output');
