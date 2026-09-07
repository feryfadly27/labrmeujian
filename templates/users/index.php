<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="users.php">
    <label for="search">Cari username atau nama</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <button type="submit">Cari</button>
    <a href="users/create.php">Tambah User</a>
</form>
<table>
    <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= escapeHtml($user['username']) ?></td>
            <td><?= escapeHtml($user['nama_lengkap']) ?></td>
            <td><?= $user['role'] === 'admin' ? 'Admin' : 'Petugas' ?></td>
            <td><?= $user['last_login_at'] !== null ? escapeHtml((new DateTimeImmutable($user['last_login_at']))->format('d/m/Y H:i')) : 'Belum pernah login' ?></td>
            <td>
                <a href="users/edit.php?id=<?= (int) $user['id'] ?>">Ubah</a>
                <?php if ((int) $user['id'] !== (int) currentUser()['id']): ?>
                    <form method="post" action="users/delete.php" style="display:inline" data-confirm="Hapus user &quot;<?= escapeHtml($user['nama_lengkap']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                        <button type="submit">Hapus</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($users === []): ?><tr><td colspan="5">Belum ada data user.</td></tr><?php endif; ?>
    </tbody>
</table>
