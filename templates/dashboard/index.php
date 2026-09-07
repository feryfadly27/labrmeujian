<div class="page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <h1 class="h2 mb-1"><?= escapeHtml($pageTitle) ?></h1>
        <p class="mb-0">Ringkasan pengelolaan ujian laboratorium.</p>
    </div>
    <a class="btn btn-success" href="generate.php">Generate Workstation</a>
</div>

<?php if ($error !== null): ?>
    <div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <?php foreach ($statistics as $statistic): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="dashboard-stat h-100">
                <div class="dashboard-stat-label"><?= escapeHtml($statistic['label']) ?></div>
                <div class="dashboard-stat-value"><?= number_format((int) $statistic['value']) ?></div>
                <a href="<?= escapeHtml($statistic['url']) ?>" class="dashboard-stat-link">Lihat data <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <section class="page-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Jadwal Ujian Terbaru</h2>
                <a href="schedules.php" class="small text-decoration-none">Semua jadwal</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Mata Kuliah</th><th>Kelas</th><th>Tanggal</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentSchedules as $schedule): ?>
                        <tr>
                            <td><?= escapeHtml($schedule['kode_mk'] . ' - ' . $schedule['nama_mk']) ?></td>
                            <td><?= escapeHtml($schedule['kode_kelas']) ?></td>
                            <td><?= escapeHtml($schedule['tanggal_ujian']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($recentSchedules === []): ?><tr><td colspan="3" class="empty-state">Belum ada jadwal ujian.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5">
        <section class="page-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Hasil Generate Terbaru</h2>
                <a href="results.php" class="small text-decoration-none">Semua hasil</a>
            </div>
            <div class="list-group list-group-flush">
            <?php foreach ($recentResults as $result): ?>
                <a class="list-group-item list-group-item-action px-0" href="results/detail.php?id=<?= (int) $result['id'] ?>">
                    <div class="fw-semibold"><?= escapeHtml($result['kode_mk'] . ' - ' . $result['nama_mk']) ?></div>
                    <small class="text-secondary"><?= escapeHtml($result['kode_kelas'] . ' &middot; ' . $result['generated_at']) ?></small>
                </a>
            <?php endforeach; ?>
            <?php if ($recentResults === []): ?><div class="empty-state">Belum ada hasil generate.</div><?php endif; ?>
            </div>
        </section>
    </div>
</div>
