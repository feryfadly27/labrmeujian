<h1><?= escapeHtml($pageTitle) ?></h1>

<?php if ($error !== null): ?>
    <p role="alert"><?= escapeHtml($error) ?></p>
<?php endif; ?>

<form method="get" action="classes.php">
    <label for="search">Cari kode atau nama kelas</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <button type="submit">Cari</button>
    <a href="classes/create.php">Tambah Kelas</a>
</form>

<table>
    <thead>
    <tr>
        <th>Kode</th>
        <th>Nama Kelas</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($classes as $class): ?>
        <tr>
            <td><?= escapeHtml($class['kode_kelas']) ?></td>
            <td><?= escapeHtml($class['nama_kelas']) ?></td>
            <td>
                <a href="classes/edit.php?id=<?= (int) $class['id'] ?>">Ubah</a>
                <form method="post" action="classes/delete.php" style="display:inline" data-confirm="Hapus kelas &quot;<?= escapeHtml($class['kode_kelas']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $class['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($classes === []): ?>
        <tr><td colspan="3">Belum ada data kelas.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
