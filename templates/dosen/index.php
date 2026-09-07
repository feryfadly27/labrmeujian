<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="dosen.php">
    <label for="search">Cari NIP atau nama dosen</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <button type="submit">Cari</button>
    <a href="dosen/create.php">Tambah Dosen</a>
</form>
<table>
    <thead>
    <tr>
        <th>No.</th>
        <th>NIP</th>
        <th>Nama Dosen</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($dosenList as $index => $dosen): ?>
        <tr>
            <td><?= (int) ($index + 1 + ($page - 1) * $perPage) ?></td>
            <td><?= escapeHtml($dosen['nip']) ?></td>
            <td><?= escapeHtml($dosen['nama_dosen']) ?></td>
            <td>
                <a href="dosen/edit.php?id=<?= (int) $dosen['id'] ?>">Ubah</a>
                <form method="post" action="dosen/delete.php" style="display:inline" data-confirm="Hapus dosen &quot;<?= escapeHtml($dosen['nama_dosen']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $dosen['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($dosenList === []): ?><tr><td colspan="4">Belum ada data dosen.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalRows > 0): ?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center">
    <span>Menampilkan <?= (int) (($page - 1) * $perPage + 1) ?>-<?= (int) min($page * $perPage, $totalRows) ?> dari <?= (int) $totalRows ?> dosen</span>
    <ul class="pagination mb-0">
        <?php
        $pageQuery = static fn (int $targetPage): string => 'dosen.php?' . http_build_query([
            'search' => $search,
            'page' => $targetPage,
        ]);
        ?>
        <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
            <a class="page-link" href="<?= escapeHtml($pageQuery(max(1, $page - 1))) ?>">&laquo; Sebelumnya</a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item<?= $p === $page ? ' active' : '' ?>">
                <a class="page-link" href="<?= escapeHtml($pageQuery($p)) ?>"><?= (int) $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item<?= $page >= $totalPages ? ' disabled' : '' ?>">
            <a class="page-link" href="<?= escapeHtml($pageQuery(min($totalPages, $page + 1))) ?>">Berikutnya &raquo;</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
