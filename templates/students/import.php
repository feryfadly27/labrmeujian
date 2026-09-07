<h1><?= escapeHtml($pageTitle) ?></h1>
<p>Upload hanya menerima file Excel <code>.xlsx</code> dengan header: <code>nim</code>, <code>nama_mahasiswa</code>, <code>kode_kelas</code>, <code>angkatan</code>.</p>
<p><a href="<?= escapeHtml($app['base_url']) ?>students/template.php">Download template Excel</a></p>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post" action="<?= escapeHtml($formAction) ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="student_file">File Excel (.xlsx)</label>
    <input id="student_file" name="student_file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
    <button type="submit">Import Excel</button>
    <a href="<?= escapeHtml($app['base_url']) ?>students.php">Batal</a>
</form>
<?php if ($result !== null): ?>
    <h2>Hasil Import</h2>
    <ul>
        <li>Total baris: <?= (int) $result['total_rows'] ?></li>
        <li>Berhasil: <?= (int) $result['success_rows'] ?></li>
        <li>Gagal: <?= (int) $result['failed_rows'] ?></li>
    </ul>
    <?php if ($result['errors'] !== []): ?>
        <h3>Baris gagal</h3>
        <ul>
        <?php foreach ($result['errors'] as $rowError): ?>
            <li>Baris <?= (int) $rowError['row'] ?>: <?= escapeHtml($rowError['message']) ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
