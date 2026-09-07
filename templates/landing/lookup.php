<div class="bulletin bulletin-compact">
    <div class="bulletin-masthead">
        <div class="bulletin-masthead-id">
            <span class="bulletin-kicker">Program Studi Rekam Medis &amp; Informasi Kesehatan</span>
            <h1>Telusur Nomor Workstation</h1>
        </div>
    </div>
    <div class="bulletin-rule"></div>
    <p class="bulletin-dek">Masukkan NIM untuk melihat nomor workstation Anda. Nomor baru tayang mulai pukul 07.00 pada hari ujian berlangsung. <a href="index.php">&larr; Kembali ke papan jadwal</a>.</p>
</div>

<form class="bulletin-filter bulletin-lookup-form" method="get" action="cari-workstation.php">
    <fieldset class="lookup-field">
        <label for="nim">Nomor Induk Mahasiswa</label>
        <input id="nim" name="nim" type="text" maxlength="40" value="<?= escapeHtml($nim) ?>" placeholder="mis. P20637026009" required autofocus>
    </fieldset>
    <div class="bulletin-filter-actions">
        <button type="submit">Telusuri</button>
    </div>
</form>

<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>

<?php if ($searched && $results !== []): ?>
<p class="lookup-hint">Ditemukan atas nama <strong><?= escapeHtml($results[0]['nama_snapshot']) ?></strong> &middot; NIM <span class="mono"><?= escapeHtml($nim) ?></span></p>
<div class="ticket-stack">
    <?php foreach ($results as $result): ?>
        <div class="ticket">
            <div class="ticket-main">
                <div class="ticket-subject">
                    <span class="subject-code"><?= escapeHtml($result['kode_mk']) ?></span>
                    <span class="subject-name"><?= escapeHtml($result['nama_mk']) ?></span>
                </div>
                <dl class="ticket-meta">
                    <div><dt>Hari &amp; tanggal</dt><dd><?= escapeHtml(formatIndonesianDate($result['tanggal_ujian'])) ?></dd></div>
                    <div><dt>Pukul</dt><dd><?= escapeHtml($result['waktu'] ?? '&mdash;') ?></dd></div>
                    <div><dt>Ruang</dt><dd><?= escapeHtml($result['ruang'] ?? '&mdash;') ?></dd></div>
                    <div><dt>Kelas</dt><dd><?= escapeHtml($result['kode_kelas_snapshot']) ?></dd></div>
                </dl>
            </div>
            <div class="ticket-stub">
                <span class="ticket-stub-label">Workstation</span>
                <span class="ticket-stub-no"><?= (int) $result['workstation_no'] ?></span>
                <span class="ticket-stub-sub">urutan ke-<?= (int) $result['nomor_urut'] ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
