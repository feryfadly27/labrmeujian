<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="schedules.php">
    <label for="search">Cari mata kuliah, kelas, atau tanggal</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <button type="submit">Cari</button>
    <a href="schedules/create.php">Tambah Jadwal</a>
</form>
<table>
    <thead><tr><th>Mata Kuliah</th><th>Kelas</th><th>Tanggal</th><th>Sesi</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($schedules as $schedule): ?>
        <tr>
            <td><?= escapeHtml($schedule['kode_mk'] . ' - ' . $schedule['nama_mk']) ?></td>
            <td><?= escapeHtml($schedule['kode_kelas']) ?></td>
            <td><?= escapeHtml($schedule['tanggal_ujian']) ?></td>
            <td><?= escapeHtml($schedule['sesi'] ?? '-') ?></td>
            <td>
                <a href="schedules/edit.php?id=<?= (int) $schedule['id'] ?>">Ubah</a>
                <form method="post" action="schedules/delete.php" style="display:inline" data-confirm="Hapus jadwal &quot;<?= escapeHtml($schedule['kode_mk'] . ' - ' . $schedule['kode_kelas']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $schedule['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($schedules === []): ?><tr><td colspan="5">Belum ada jadwal ujian.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalRows > 0): ?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center">
    <span>Menampilkan <?= (int) (($page - 1) * $perPage + 1) ?>-<?= (int) min($page * $perPage, $totalRows) ?> dari <?= (int) $totalRows ?> jadwal</span>
    <ul class="pagination mb-0">
        <?php $pageQuery = static fn (int $targetPage): string => 'schedules.php?' . http_build_query(['search' => $search, 'page' => $targetPage]); ?>
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
