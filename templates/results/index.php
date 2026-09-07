<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<table>
    <thead><tr><th>Mata Kuliah</th><th>Kelas</th><th>Tanggal</th><th>Dibuat</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($results as $result): ?>
        <tr>
            <td><?= escapeHtml($result['kode_mk'] . ' - ' . $result['nama_mk']) ?></td>
            <td><?= escapeHtml($result['kode_kelas']) ?></td>
            <td><?= escapeHtml($result['tanggal_ujian']) ?></td>
            <td><?= escapeHtml($result['generated_at']) ?></td>
            <td><a href="results/detail.php?id=<?= (int) $result['id'] ?>">Lihat Detail</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($results === []): ?><tr><td colspan="5">Belum ada hasil generate.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalRows > 0): ?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center">
    <span>Menampilkan <?= (int) (($page - 1) * $perPage + 1) ?>-<?= (int) min($page * $perPage, $totalRows) ?> dari <?= (int) $totalRows ?> hasil</span>
    <ul class="pagination mb-0">
        <?php $pageQuery = static fn (int $targetPage): string => 'results.php?' . http_build_query(['page' => $targetPage]); ?>
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
