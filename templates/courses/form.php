<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="kode_mk">Kode Mata Kuliah</label>
    <input id="kode_mk" name="kode_mk" type="text" maxlength="30" value="<?= escapeHtml($values['kode_mk']) ?>" required>
    <label for="nama_mk">Nama Mata Kuliah</label>
    <input id="nama_mk" name="nama_mk" type="text" maxlength="150" value="<?= escapeHtml($values['nama_mk']) ?>" required>
    <button type="submit">Simpan</button>
    <a href="<?= escapeHtml($app['base_url']) ?>courses.php">Batal</a>
</form>
