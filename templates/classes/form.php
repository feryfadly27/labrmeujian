<h1><?= escapeHtml($pageTitle) ?></h1>

<?php if ($error !== null): ?>
    <p role="alert"><?= escapeHtml($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="kode_kelas">Kode Kelas</label>
    <input id="kode_kelas" name="kode_kelas" type="text" maxlength="30" value="<?= escapeHtml($values['kode_kelas']) ?>" required>
    <label for="nama_kelas">Nama Kelas</label>
    <input id="nama_kelas" name="nama_kelas" type="text" maxlength="100" value="<?= escapeHtml($values['nama_kelas']) ?>" required>
    <button type="submit">Simpan</button>
    <a href="<?= escapeHtml($app['base_url']) ?>classes.php">Batal</a>
</form>
