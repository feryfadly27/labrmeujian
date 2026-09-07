<h1><?= escapeHtml($pageTitle) ?></h1>
<?php if ($error !== null): ?><p role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
<?php if ($success !== null): ?><p role="status"><?= escapeHtml($success) ?></p><?php endif; ?>
<form method="post" action="generate.php">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <label for="jadwal_id">Jadwal Ujian</label>
    <select id="jadwal_id" name="jadwal_id" required>
        <option value="">Pilih jadwal</option>
        <?php foreach ($schedules as $schedule): ?>
            <option value="<?= (int) $schedule['id'] ?>">
                <?= escapeHtml($schedule['kode_mk'] . ' - ' . $schedule['nama_mk'] . ' | ' . $schedule['kode_kelas'] . ' | ' . $schedule['tanggal_ujian']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Generate Workstation</button>
</form>
<?php if ($schedules === []): ?><p>Tidak ada jadwal yang siap digenerate.</p><?php endif; ?>
