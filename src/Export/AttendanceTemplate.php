<?php

declare(strict_types=1);

function attendanceDate(array $data): string
{
    return formatIndonesianDate($data['result']['tanggal_ujian']);
}

function renderAttendanceHtml(array $data): string
{
    $result = $data['result'];
    $metadata = $data['metadata'];
    $examType = $metadata['jenis_ujian'] ?? 'UAS';
    $details = $data['details'];
    $esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $date = $esc(attendanceDate($data));
    $subject = $esc($result['kode_mk'] . ' - ' . $result['nama_mk']);
    $class = $esc($result['kode_kelas']);
    $supervisors = array_map($esc, $metadata['pengawas']);
    $teachers = array_map($esc, $metadata['pengajar']);
    $notes = array_map($esc, $metadata['catatan']);
    $headerPath = BASE_PATH . '/storage/templates/reference-header.png';
    $headerImage = is_file($headerPath)
        ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($headerPath))
        : '';
    ob_start();
    ?>
<!doctype html><html><head><meta charset="utf-8"><style>
@page { size: A4 portrait; margin: 18mm 14mm 16mm; }
body { font-family: DejaVu Sans, sans-serif; color: #17324d; font-size: 10pt; }
.page-break { page-break-before: always; }
.header { text-align: center; font-weight: bold; font-size: 15pt; color: #17324d; margin: 7px 0 14px; }
.subheader { text-align: center; font-size: 10pt; color: #52677b; margin-bottom: 18px; }
.meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.meta td { padding: 5px 6px; vertical-align: top; border-bottom: 1px solid #dce5eb; }
.meta .label { font-weight: bold; width: 22%; }
table.attendance { width: 100%; border-collapse: collapse; }
table.attendance th, table.attendance td { border: 1px solid #aebdca; padding: 6px 5px; }
table.attendance th { text-align: center; background: #e8f5f2; color: #17324d; }
table.attendance .number { width: 7%; text-align: center; }
table.attendance .nim { width: 20%; }
table.attendance .workstation { width: 14%; text-align: center; font-weight: bold; }
table.attendance .signature { width: 20%; height: 25px; }
.institution-header { display: block; width: 100%; height: auto; margin: 0 0 8px; }
.signature-block { margin-top: 28px; width: 100%; table-layout: fixed; text-align: center; }
.signature-block td { height: 65px; vertical-align: bottom; }
.lines { line-height: 1.8; }
table.participant-count { border-collapse: collapse; }
table.participant-count td { padding: 1px 0; }
table.participant-count .participant-label { width: 34mm; }
table.participant-count .participant-value { padding-left: 4px; }
</style></head><body>
<section>
<?php if ($headerImage !== ''): ?><img class="institution-header" src="<?= $headerImage ?>" alt="Header institusi">
<?php endif; ?>
<div class="header">BERITA ACARA <?= $esc($examType) ?></div>
<div class="subheader">Kami pengawas <?= $esc($examType) ?> pada Program Studi <?= $esc($metadata['program_studi']) ?></div>
<table class="meta">
<tr><td class="label">1. Mata Ujian</td><td><?= $subject ?></td></tr>
<tr><td class="label">2. Pada Hari, Tanggal</td><td><?= $date ?></td></tr>
<tr><td class="label">3. Waktu</td><td><?= $esc($metadata['waktu']) ?></td></tr>
<tr><td class="label">4. Ruang</td><td><?= $esc($metadata['ruang']) ?></td></tr>
<tr><td class="label">5. Tahun Akademik</td><td><?= $esc($metadata['tahun_akademik']) ?></td></tr>
<tr><td class="label">6. Semester</td><td><?= $esc($metadata['semester']) ?></td></tr>
<tr><td class="label">7. Peserta</td><td>
<table class="participant-count">
<tr><td class="participant-label">Jumlah</td><td class="participant-value">: <?= count($details) ?> orang</td></tr>
<tr><td class="participant-label">Hadir</td><td class="participant-value">: ............ orang</td></tr>
<tr><td class="participant-label">Tidak Hadir</td><td class="participant-value">: ............ orang</td></tr>
</table>
</td></tr>
</table>
<p>Hal-hal yang perlu dilaporkan selama UAS berlangsung:</p><div class="lines">1 .................................................................<br>2 .................................................................<br>3 .................................................................</div>
<p>Demikian Berita Acara ini kami buat dengan sebenarnya untuk diketahui dan dipergunakan sepenuhnya.</p>
<?php $supervisorSlotCount = max(2, count($supervisors)); ?>
<table class="signature-block"><tr><td colspan="<?= $supervisorSlotCount ?>"><?= $esc($metadata['kota']) ?>, <?= $date ?><br><br>PENGAWAS</td></tr><tr><?php foreach (array_pad($supervisors, $supervisorSlotCount, '') as $person): ?><td><?php if ($person !== ''): ?><b>(<?= $person ?>)</b><br><?php endif; ?>........................................</td><?php endforeach; ?></tr></table>
</section>
<section class="page-break">
<?php if ($headerImage !== ''): ?><img class="institution-header" src="<?= $headerImage ?>" alt="Header institusi">
<?php endif; ?>
<div class="header">DAFTAR HADIR <?= $esc($examType) ?> (<?= $esc($examType) ?>)</div>
<div class="subheader">Program Studi: <?= $esc($metadata['program_studi']) ?></div>
<table class="meta"><tr><td class="label">Mata Kuliah</td><td><?= $subject ?></td><td class="label">Nama Kelas</td><td><?= $class ?></td></tr><tr><td class="label">Periode Akademik</td><td><?= $esc($metadata['periode_akademik']) ?></td><td class="label">Kelompok</td><td><?= $esc($metadata['kelompok']) ?></td></tr><tr><td class="label">Jadwal</td><td><?= $date . ', ' . $esc($metadata['waktu']) ?></td><td class="label">Ruang</td><td><?= $esc($metadata['ruang']) ?></td></tr></table>
<table class="attendance"><thead><tr><th class="number">NO</th><th class="nim">NIM</th><th>NAMA</th><th class="workstation">WORKSTATION PC</th><th class="signature">TANDA TANGAN</th></tr></thead><tbody><?php foreach ($details as $index => $detail): ?><tr><td class="number"><?= (int) ($index + 1) ?></td><td class="nim"><?= $esc($detail['nim_snapshot']) ?></td><td><?= $esc($detail['nama_snapshot']) ?></td><td class="workstation"><?= (int) $detail['workstation_no'] ?></td><td class="signature"></td></tr><?php endforeach; ?></tbody></table>
<?php if ($notes !== []): ?><p><b>Catatan:</b></p><div class="lines"><?php foreach ($notes as $index => $note): ?><?= $index + 1 ?>. <?= $note ?><br><?php endforeach; ?></div><?php endif; ?>
<?php $teacherSlotCount = max(2, count($teachers)); ?>
<table class="signature-block"><tr><?php foreach (array_pad($teachers, $teacherSlotCount, '') as $person): ?><td><?php if ($person !== ''): ?>Pengajar<br><br><br><b><?= $person ?></b><?php endif; ?></td><?php endforeach; ?></tr></table>
</section></body></html>
<?php
    return (string) ob_get_clean();
}

/**
 * Versi untuk ditampilkan langsung di tab browser lalu dicetak lewat dialog
 * print bawaan (Ctrl+P) — bukan diunduh sebagai file. Reuse markup yang sama
 * dengan renderAttendanceHtml(), hanya menambah tombol non-cetak dan pemicu
 * dialog print otomatis saat halaman selesai dimuat.
 */
function renderPrintableAttendanceHtml(array $data): string
{
    $html = renderAttendanceHtml($data);
    $nonce = htmlspecialchars(cspNonce(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $printBar = <<<HTML
<div class="print-toolbar no-print">
    <button type="button" class="btn-print" id="printButton">Cetak</button>
    <button type="button" class="btn-close-print" id="closeButton">Tutup</button>
</div>
<style>
.print-toolbar {
    position: fixed;
    top: 12px;
    right: 12px;
    display: flex;
    gap: .5rem;
    z-index: 999;
}
.print-toolbar button {
    display: inline-block;
    padding: .25rem .5rem;
    border-radius: .375rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: .875rem;
    font-weight: 400;
    line-height: 1.5;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .1);
}
.btn-print {
    border: 1px solid #0d6efd;
    background: #0d6efd;
    color: #fff;
}
.btn-close-print {
    border: 1px solid #6c757d;
    background: #fff;
    color: #6c757d;
}
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
<script nonce="{$nonce}">
document.getElementById('printButton').addEventListener('click', function () { window.print(); });
document.getElementById('closeButton').addEventListener('click', function () { window.close(); });
window.addEventListener('load', function () { window.print(); });
</script>
HTML;

    return str_replace('</body>', $printBar . '</body>', $html);
}
