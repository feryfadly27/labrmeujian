<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <?php if ($isEdit): ?>
        <label for="username_display">Username</label>
        <input id="username_display" type="text" value="<?= escapeHtml($values['username']) ?>" disabled>
    <?php else: ?>
        <label for="username">Username</label>
        <input id="username" name="username" type="text" maxlength="50" value="<?= escapeHtml($values['username']) ?>" required autofocus>
    <?php endif; ?>
    <label for="nama_lengkap">Nama Lengkap</label>
    <input id="nama_lengkap" name="nama_lengkap" type="text" maxlength="150" value="<?= escapeHtml($values['nama_lengkap']) ?>" required>
    <label for="role">Role</label>
    <select id="role" name="role" required<?= ($isEdit && ($isSelf ?? false)) ? ' disabled' : '' ?>>
        <option value="admin"<?= $values['role'] === 'admin' ? ' selected' : '' ?>>Admin</option>
        <option value="petugas"<?= $values['role'] === 'petugas' ? ' selected' : '' ?>>Petugas</option>
    </select>
    <?php if ($isEdit && ($isSelf ?? false)): ?>
        <input type="hidden" name="role" value="<?= escapeHtml($values['role']) ?>">
        <p>Role akun Anda sendiri tidak dapat diubah dari sini.</p>
    <?php endif; ?>
    <label for="password"><?= $isEdit ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password' ?></label>
    <input id="password" name="password" type="password" minlength="8" autocomplete="new-password"<?= $isEdit ? '' : ' required' ?>>
    <button type="submit">Simpan</button>
    <a href="<?= escapeHtml($app['base_url']) ?>users.php">Batal</a>
</form>
