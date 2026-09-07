<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="courses.php">
    <label for="search">Cari kode atau nama mata kuliah</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <button type="submit">Cari</button>
    <a href="courses/create.php">Tambah Mata Kuliah</a>
</form>
<table>
    <thead><tr><th>Kode</th><th>Nama Mata Kuliah</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= escapeHtml($course['kode_mk']) ?></td>
            <td><?= escapeHtml($course['nama_mk']) ?></td>
            <td>
                <a href="courses/edit.php?id=<?= (int) $course['id'] ?>">Ubah</a>
                <form method="post" action="courses/delete.php" style="display:inline" data-confirm="Hapus mata kuliah &quot;<?= escapeHtml($course['kode_mk']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $course['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($courses === []): ?><tr><td colspan="3">Belum ada data mata kuliah.</td></tr><?php endif; ?>
    </tbody>
</table>
