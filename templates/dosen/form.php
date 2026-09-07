<h1><?= escapeHtml($pageTitle) ?></h1>

<?php if ($error !== null): ?>
    <p role="alert"><?= escapeHtml($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="nip">NIP</label>
    <input id="nip" name="nip" type="text" maxlength="40" value="<?= escapeHtml($values['nip']) ?>" required>
    <label for="nama_dosen">Nama Dosen</label>
    <input id="nama_dosen" name="nama_dosen" type="text" maxlength="150" value="<?= escapeHtml($values['nama_dosen']) ?>" required>
    <button type="submit">Simpan</button>
    <a href="<?= escapeHtml($app['base_url']) ?>dosen.php">Batal</a>
</form>
