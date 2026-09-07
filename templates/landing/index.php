<div class="bulletin">
    <div class="bulletin-masthead">
        <div class="bulletin-masthead-id">
            <span class="bulletin-kicker">Program Studi Rekam Medis &amp; Informasi Kesehatan</span>
            <h1>Papan Jadwal Ujian<br>Laboratorium RME</h1>
        </div>
        <a class="bulletin-staff-link" href="login.php">Masuk sebagai Petugas &rarr;</a>
    </div>
    <div class="bulletin-rule"></div>
    <p class="bulletin-dek">Jadwal resmi penggunaan lab untuk ujian tertulis berbasis komputer. Saring menurut hari, kelas, atau angkatan — atau <a href="cari-workstation.php">telusuri nomor workstation Anda</a> begitu jadwal Anda tiba.</p>
</div>

<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>

<form class="bulletin-filter" method="get" action="index.php">
    <fieldset>
        <label for="hari">Hari</label>
        <select id="hari" name="hari">
            <option value="">Semua hari</option>
            <?php foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'] as $value => $label): ?>
                <option value="<?= escapeHtml($value) ?>"<?= strtolower($hari) === $value ? ' selected' : '' ?>><?= escapeHtml($label) ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
    <fieldset>
        <label for="kelas_id">Kelas</label>
        <select id="kelas_id" name="kelas_id">
            <option value="">Semua kelas</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= (int) $class['id'] ?>"<?= (string) $class['id'] === $kelasId ? ' selected' : '' ?>><?= escapeHtml($class['kode_kelas'] . ' - ' . $class['nama_kelas']) ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
    <fieldset>
        <label for="angkatan">Angkatan</label>
        <select id="angkatan" name="angkatan">
            <option value="">Semua angkatan</option>
            <?php foreach ($angkatanList as $year): ?>
                <option value="<?= (int) $year ?>"<?= (string) $year === $angkatan ? ' selected' : '' ?>><?= (int) $year ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
    <div class="bulletin-filter-actions">
        <button type="submit">Terapkan</button>
        <?php if ($hari !== '' || $kelasId !== '' || $angkatan !== ''): ?>
            <a href="index.php">Bersihkan</a>
        <?php endif; ?>
    </div>
</form>

<div class="bulletin-board">
    <div class="bulletin-board-head">
        <span class="col-when">Hari &amp; Tanggal</span>
        <span class="col-time">Pukul</span>
        <span class="col-subject">Mata Kuliah</span>
        <span class="col-class">Kelas</span>
        <span class="col-room">Ruang</span>
        <span class="col-status">Status</span>
    </div>
    <?php foreach ($schedules as $schedule): ?>
        <div class="bulletin-row">
            <span class="col-when"><?= escapeHtml(formatIndonesianDate($schedule['tanggal_ujian'])) ?></span>
            <span class="col-time"><?= escapeHtml($schedule['waktu'] ?? ($schedule['sesi'] ?? '&mdash;')) ?></span>
            <span class="col-subject">
                <span class="subject-code"><?= escapeHtml($schedule['kode_mk']) ?></span>
                <span class="subject-name"><?= escapeHtml($schedule['nama_mk']) ?></span>
            </span>
            <span class="col-class"><?= escapeHtml($schedule['kode_kelas']) ?></span>
            <span class="col-room"><?= escapeHtml($schedule['ruang'] ?? '&mdash;') ?></span>
            <span class="col-status">
                <?php if ($schedule['generate_id'] !== null): ?>
                    <span class="status-chip status-ready"><span class="status-dot"></span>Workstation siap</span>
                <?php else: ?>
                    <span class="status-chip status-pending"><span class="status-dot"></span>Belum digenerate</span>
                <?php endif; ?>
            </span>
        </div>
    <?php endforeach; ?>
    <?php if ($schedules === []): ?>
        <div class="bulletin-empty">Tidak ada jadwal ujian yang cocok dengan filter ini.</div>
    <?php endif; ?>
</div>
