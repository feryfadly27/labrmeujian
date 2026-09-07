<?php

declare(strict_types=1);

requireRole('admin');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Mahasiswa');
$headers = ['nim', 'nama_mahasiswa', 'kode_kelas', 'angkatan'];
foreach ($headers as $column => $header) {
    $cell = chr(65 + $column) . '1';
    $sheet->setCellValue($cell, $header);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}
$sheet->freezePane('A2');
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(32);
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(12);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template-mahasiswa.xlsx"');
header('Cache-Control: no-store, no-cache, must-revalidate');
$writer->save('php://output');
exit;
