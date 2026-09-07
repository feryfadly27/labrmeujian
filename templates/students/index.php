<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<form method="get" action="students.php" id="studentFilterForm">
    <label for="search">Cari NIM atau nama</label>
    <input id="search" name="search" type="search" value="<?= escapeHtml($search) ?>">
    <label for="kelas_id">Kelas</label>
    <select id="kelas_id" name="kelas_id" data-auto-submit>
        <option value="">Semua kelas</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?= (int) $class['id'] ?>"<?= (string) $class['id'] === $kelasId ? ' selected' : '' ?>><?= escapeHtml($class['kode_kelas'] . ' - ' . $class['nama_kelas']) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="angkatan">Angkatan</label>
    <select id="angkatan" name="angkatan" data-auto-submit>
        <option value="">Semua angkatan</option>
        <?php foreach ($angkatanList as $year): ?>
            <option value="<?= (int) $year ?>"<?= (string) $year === $angkatan ? ' selected' : '' ?>><?= (int) $year ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="sort" value="<?= escapeHtml($sort) ?>">
    <input type="hidden" name="dir" value="<?= escapeHtml($dir) ?>">
    <button type="submit">Cari</button>
    <a href="students/create.php">Tambah Mahasiswa</a>
    <a href="students/import.php">Import Excel</a>
    <a href="students/template.php">Download Template Excel</a>
</form>
<?php
$sortLink = static function (string $column, string $label) use ($search, $kelasId, $angkatan, $sort, $dir): string {
    $nextDir = $sort === $column && $dir === 'asc' ? 'desc' : 'asc';
    $indicator = $sort === $column ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
    $query = http_build_query([
        'search' => $search,
        'kelas_id' => $kelasId,
        'angkatan' => $angkatan,
        'sort' => $column,
        'dir' => $nextDir,
    ]);
    return '<a href="students.php?' . $query . '">' . escapeHtml($label) . $indicator . '</a>';
};
?>
<table>
    <thead>
    <tr>
        <th>No.</th>
        <th><?= $sortLink('nim', 'NIM') ?></th>
        <th><?= $sortLink('nama_mahasiswa', 'Nama') ?></th>
        <th><?= $sortLink('kode_kelas', 'Kelas') ?></th>
        <th><?= $sortLink('angkatan', 'Angkatan') ?></th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($students as $index => $student): ?>
        <tr>
            <td><?= (int) ($index + 1 + ($page - 1) * $perPage) ?></td>
            <td><?= escapeHtml($student['nim']) ?></td>
            <td><?= escapeHtml($student['nama_mahasiswa']) ?></td>
            <td><?= escapeHtml($student['kode_kelas']) ?></td>
            <td><?= escapeHtml((string) ($student['angkatan'] ?? '-')) ?></td>
            <td>
                <a href="students/edit.php?id=<?= (int) $student['id'] ?>">Ubah</a>
                <form method="post" action="students/delete.php" style="display:inline" data-confirm="Hapus mahasiswa &quot;<?= escapeHtml($student['nama_mahasiswa']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($students === []): ?><tr><td colspan="6">Belum ada data mahasiswa.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalRows > 0): ?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center">
    <span>Menampilkan <?= (int) (($page - 1) * $perPage + 1) ?>-<?= (int) min($page * $perPage, $totalRows) ?> dari <?= (int) $totalRows ?> data</span>
    <ul class="pagination mb-0">
        <?php
        $pageQuery = static fn (int $targetPage): string => 'students.php?' . http_build_query([
            'search' => $search,
            'kelas_id' => $kelasId,
            'angkatan' => $angkatan,
            'sort' => $sort,
            'dir' => $dir,
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
