<div class="page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div><h1 class="h2 mb-1"><?= escapeHtml($pageTitle) ?></h1><p class="mb-0">Daftar peserta tersimpan sebagai snapshot hasil pengacakan.</p></div>
    <a class="btn btn-outline-secondary" href="<?= escapeHtml($app['base_url']) ?>results.php">Kembali</a>
</div>
<section class="page-panel mb-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
        <div><span class="badge text-bg-success mb-2">Hasil Tersimpan</span><h2 class="h5 mb-0"><?= escapeHtml($result['kode_mk'] . ' - ' . $result['nama_mk']) ?></h2></div>
        <div class="text-md-end text-secondary small">Dibuat <?= escapeHtml($result['generated_at']) ?></div>
    </div>
    <div class="row g-3 result-meta-grid">
        <div class="col-6 col-md-3"><span class="text-secondary small d-block">Kelas</span><strong><?= escapeHtml($result['kode_kelas']) ?></strong></div>
        <div class="col-6 col-md-3"><span class="text-secondary small d-block">Tanggal Ujian</span><strong><?= escapeHtml($result['tanggal_ujian']) ?></strong></div>
        <div class="col-6 col-md-3"><span class="text-secondary small d-block">Peserta</span><strong><?= number_format(count($details)) ?> mahasiswa</strong></div>
        <div class="col-6 col-md-3"><span class="text-secondary small d-block">Aksi</span><a href="<?= escapeHtml($app['base_url']) ?>results/metadata.php?id=<?= (int) $result['id'] ?>">Edit metadata</a></div>
    </div>
    <hr>
    <div class="d-flex flex-wrap gap-2">
        <span class="small fw-semibold me-2 align-self-center">Cetak:</span>
        <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="<?= escapeHtml($app['base_url']) ?>results/export.php?id=<?= (int) $result['id'] ?>&format=print">Print (tanpa unduh)</a>
        <span class="small fw-semibold me-2 ms-3 align-self-center">Ekspor:</span>
        <a class="btn btn-sm btn-danger" href="<?= escapeHtml($app['base_url']) ?>results/export.php?id=<?= (int) $result['id'] ?>&format=pdf">PDF</a>
        <a class="btn btn-sm btn-success" href="<?= escapeHtml($app['base_url']) ?>results/export.php?id=<?= (int) $result['id'] ?>&format=xlsx">Excel</a>
        <a class="btn btn-sm btn-primary" href="<?= escapeHtml($app['base_url']) ?>results/export.php?id=<?= (int) $result['id'] ?>&format=docx">DOCX</a>
    </div>
</section>
<section class="page-panel">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Daftar Workstation</h2><span class="text-secondary small">Diurutkan berdasarkan NIM</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Workstation PC</th><th>Tanda Tangan</th></tr></thead>
    <tbody>
    <?php foreach ($details as $index => $detail): ?>
        <tr>
            <td><?= (int) ($index + 1) ?></td>
            <td><?= escapeHtml($detail['nim_snapshot']) ?></td>
            <td><?= escapeHtml($detail['nama_snapshot']) ?></td>
            <td class="text-center"><span class="badge text-bg-info fs-6"><?= (int) $detail['workstation_no'] ?></span></td>
            <td></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
</section>
