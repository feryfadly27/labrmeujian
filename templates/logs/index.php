<?php
$actionLabels = [
    'generate_workstation' => 'Generate Workstation',
    'create_user' => 'Tambah User',
    'update_user' => 'Ubah User',
    'delete_user' => 'Hapus User',
];
$actionLabel = static fn (string $action): string => $actionLabels[$action] ?? $action;
?>
<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="logs.php">
    <label for="action">Jenis Aksi</label>
    <select id="action" name="action">
        <option value="">Semua aksi</option>
        <?php foreach ($actionOptions as $option): ?>
            <option value="<?= escapeHtml($option) ?>"<?= $option === $action ? ' selected' : '' ?>><?= escapeHtml($actionLabel($option)) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Terapkan</button>
    <?php if ($action !== ''): ?><a href="logs.php">Reset</a><?php endif; ?>
</form>
<table>
    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Detail</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= escapeHtml((new DateTimeImmutable($log['created_at']))->format('d/m/Y H:i:s')) ?></td>
            <td><?= escapeHtml($log['nama_lengkap']) ?> <small>(<?= escapeHtml($log['username']) ?>)</small></td>
            <td><?= escapeHtml($actionLabel($log['action'])) ?></td>
            <td>
                <?php
                $metadata = $log['metadata'] !== null ? json_decode((string) $log['metadata'], true) : null;
                ?>
                <?php if (is_array($metadata) && $metadata !== []): ?>
                    <?php foreach ($metadata as $key => $value): ?>
                        <div><small><?= escapeHtml((string) $key) ?>: <?= escapeHtml(is_scalar($value) ? (string) $value : json_encode($value)) ?></small></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= escapeHtml($log['entity_type']) ?><?= $log['entity_id'] !== null ? ' #' . (int) $log['entity_id'] : '' ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($logs === []): ?><tr><td colspan="4">Belum ada aktivitas tercatat.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalRows > 0): ?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center">
    <span>Menampilkan <?= (int) (($page - 1) * $perPage + 1) ?>-<?= (int) min($page * $perPage, $totalRows) ?> dari <?= (int) $totalRows ?> aktivitas</span>
    <ul class="pagination mb-0">
        <?php
        $pageQuery = static fn (int $targetPage): string => 'logs.php?' . http_build_query(['action' => $action, 'page' => $targetPage]);
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
