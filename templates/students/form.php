<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="nim">NIM</label>
    <input id="nim" name="nim" type="text" maxlength="40" value="<?= escapeHtml($values['nim']) ?>" required>
    <label for="nama_mahasiswa">Nama Mahasiswa</label>
    <input id="nama_mahasiswa" name="nama_mahasiswa" type="text" maxlength="150" value="<?= escapeHtml($values['nama_mahasiswa']) ?>" required>
    <label for="kelas_id">Kelas</label>
    <select id="kelas_id" name="kelas_id" required>
        <option value="">Pilih kelas</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?= (int) $class['id'] ?>"<?= (string) $class['id'] === (string) $values['kelas_id'] ? ' selected' : '' ?>><?= escapeHtml($class['kode_kelas'] . ' - ' . $class['nama_kelas']) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="angkatan">Angkatan</label>
    <input id="angkatan" name="angkatan" type="number" min="1900" max="2200" value="<?= escapeHtml((string) ($values['angkatan'] ?? '')) ?>">
    <button type="submit">Simpan</button>
    <a href="<?= escapeHtml($app['base_url']) ?>students.php">Batal</a>
</form>
