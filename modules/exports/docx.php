<?php

$document = new \PhpOffice\PhpWord\PhpWord();
$document->setDefaultFontName('Arial');
$document->setDefaultFontSize(10);
$section = $document->addSection(['marginTop' => 900, 'marginBottom' => 900, 'marginLeft' => 900, 'marginRight' => 900]);
$result = $data['result'];
$metadata = $data['metadata'];
$examType = $metadata['jenis_ujian'] ?? 'UAS';
$headerPath = BASE_PATH . '/storage/templates/reference-header.png';
if (is_file($headerPath)) { $section->addImage($headerPath, ['width' => 540, 'alignment' => 'center', 'spaceAfter' => 120]); }
$section->addText('BERITA ACARA ' . $examType, ['bold' => true, 'size' => 15], ['alignment' => 'center', 'spaceAfter' => 240]);
$section->addText('Program Studi: ' . $metadata['program_studi'], ['color' => '52677B'], ['alignment' => 'center', 'spaceAfter' => 180]);
$summary = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
foreach ([['Mata Ujian', $result['kode_mk'] . ' - ' . $result['nama_mk']], ['Tanggal', attendanceDate($data)], ['Waktu', $metadata['waktu']], ['Ruang', $metadata['ruang']], ['Tahun Akademik', $metadata['tahun_akademik']], ['Semester', $metadata['semester']], ['Jumlah Peserta', (string) count($data['details'])]] as $row) { $summary->addRow(); $summary->addCell(2200)->addText($row[0], ['bold' => true]); $summary->addCell(7000)->addText($row[1]); }
$section->addTextBreak(1);
$section->addPageBreak();
$section->addImage($headerPath, ['width' => 540, 'alignment' => 'center', 'spaceAfter' => 120]);
$section->addText('DAFTAR HADIR ' . $examType . ' (' . $examType . ')', ['bold' => true, 'size' => 15], ['alignment' => 'center', 'spaceAfter' => 240]);
$table = $section->addTable(['borderSize' => 6, 'borderColor' => 'AEBDCA', 'cellMargin' => 70]);
$table->addRow(); foreach (['NO', 'NIM', 'NAMA', 'WORKSTATION PC', 'TANDA TANGAN'] as $heading) { $table->addCell(1900, ['bgColor' => 'E8F5F2'])->addText($heading, ['bold' => true, 'color' => '17324D']); }
foreach ($data['details'] as $index => $detail) { $table->addRow(); $table->addCell(900)->addText((string) ($index + 1)); $table->addCell(2000)->addText($detail['nim_snapshot']); $table->addCell(4200)->addText($detail['nama_snapshot']); $table->addCell(1700)->addText((string) $detail['workstation_no'], ['bold' => true], ['alignment' => 'center']); $table->addCell(1700)->addText(''); }
$temporaryPath = tempnam(sys_get_temp_dir(), 'jadwal-docx-');
(new \PhpOffice\PhpWord\Writer\Word2007($document))->save($temporaryPath);
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="daftar-hadir-' . (int) $generateId . '.docx"');
readfile($temporaryPath);
unlink($temporaryPath);
