<div class="page-heading">
    <h1 class="h2 mb-1"><?= escapeHtml($pageTitle) ?></h1>
    <p class="mb-0">Atur mata kuliah, kelas, dan waktu pelaksanaan ujian.</p>
</div>
<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>
<?php if ($locked ?? false): ?><div class="alert alert-warning" role="alert">Jadwal ini sudah memiliki hasil generate dan tidak dapat diubah.</div><?php endif; ?>
<form class="page-panel" method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="mata_kuliah_id">Mata Kuliah</label>
    <select class="form-select" id="mata_kuliah_id" name="mata_kuliah_id" required<?= ($locked ?? false) ? ' disabled' : '' ?>>
        <option value="">Pilih mata kuliah</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= (int) $course['id'] ?>"<?= (string) $course['id'] === (string) $values['mata_kuliah_id'] ? ' selected' : '' ?>><?= escapeHtml($course['kode_mk'] . ' - ' . $course['nama_mk']) ?></option>
        <?php endforeach; ?>
    </select></div>
    <div class="col-md-6"><label class="form-label" for="kelas_id">Kelas</label>
    <select class="form-select" id="kelas_id" name="kelas_id" required<?= ($locked ?? false) ? ' disabled' : '' ?>>
        <option value="">Pilih kelas</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?= (int) $class['id'] ?>"<?= (string) $class['id'] === (string) $values['kelas_id'] ? ' selected' : '' ?>><?= escapeHtml($class['kode_kelas'] . ' - ' . $class['nama_kelas']) ?></option>
        <?php endforeach; ?>
    </select></div>
    <div class="col-md-4"><label class="form-label" for="tanggal_ujian">Tanggal Ujian</label><input class="form-control" id="tanggal_ujian" name="tanggal_ujian" type="date" value="<?= escapeHtml($values['tanggal_ujian']) ?>" required<?= ($locked ?? false) ? ' disabled' : '' ?>></div>
    <div class="col-md-4"><label class="form-label" for="sesi">Sesi</label><input class="form-control" id="sesi" name="sesi" type="text" maxlength="50" value="<?= escapeHtml($values['sesi'] ?? '') ?>"<?= ($locked ?? false) ? ' disabled' : '' ?>></div>
    <div class="col-md-4"><label class="form-label" for="keterangan">Keterangan</label><input class="form-control" id="keterangan" name="keterangan" type="text" maxlength="255" value="<?= escapeHtml($values['keterangan'] ?? '') ?>"<?= ($locked ?? false) ? ' disabled' : '' ?>></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-success" type="submit"<?= ($locked ?? false) ? ' disabled' : '' ?>>Simpan Perubahan</button><a class="btn btn-outline-secondary" href="<?= escapeHtml($app['base_url']) ?>schedules.php">Batal</a></div>
    </div>
</form>
