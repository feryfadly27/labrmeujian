<div class="page-heading">
    <h1 class="h2 mb-1"><?= escapeHtml($pageTitle) ?></h1>
    <p class="mb-0">Lengkapi identitas dokumen untuk export berita acara dan daftar hadir.</p>
</div>
<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>
<form class="page-panel" method="post" action="<?= escapeHtml($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="program_studi">Program Studi</label><input class="form-control" id="program_studi" name="program_studi" maxlength="150" value="<?= escapeHtml($values['program_studi']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="periode_akademik">Periode Akademik</label><input class="form-control" id="periode_akademik" name="periode_akademik" maxlength="100" value="<?= escapeHtml($values['periode_akademik']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="tahun_akademik">Tahun Akademik</label><input class="form-control" id="tahun_akademik" name="tahun_akademik" maxlength="20" value="<?= escapeHtml($values['tahun_akademik']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="semester">Semester</label><input class="form-control" id="semester" name="semester" maxlength="30" value="<?= escapeHtml($values['semester']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="waktu">Waktu Ujian</label><input class="form-control" id="waktu" name="waktu" maxlength="50" placeholder="08:00 - 12:00" value="<?= escapeHtml($values['waktu']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="ruang">Ruang</label><input class="form-control" id="ruang" name="ruang" maxlength="100" value="<?= escapeHtml($values['ruang']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="kelompok">Kelompok</label><input class="form-control" id="kelompok" name="kelompok" maxlength="100" value="<?= escapeHtml($values['kelompok']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="kota">Kota</label><input class="form-control" id="kota" name="kota" maxlength="100" value="<?= escapeHtml($values['kota']) ?>"></div>
        <div class="col-md-6">
            <label class="form-label" for="pengawas">Pengawas <span class="text-secondary">(satu nama per baris)</span></label>
            <select class="form-select mb-2" id="pengawas_pilihan" data-target="pengawas">
                <option value="">+ Tambah dari Data Dosen...</option>
                <?php foreach ($dosenList as $dosen): ?>
                    <option value="<?= escapeHtml($dosen['nama_dosen']) ?>"><?= escapeHtml($dosen['nama_dosen']) ?> (<?= escapeHtml($dosen['nip']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <textarea class="form-control" id="pengawas" name="pengawas" rows="4" placeholder="Pilih dari daftar dosen di atas, atau ketik nama secara manual"><?= escapeHtml($values['pengawas']) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="pengajar">Pengajar <span class="text-secondary">(satu nama per baris)</span></label>
            <select class="form-select mb-2" id="pengajar_pilihan" data-target="pengajar">
                <option value="">+ Tambah dari Data Dosen...</option>
                <?php foreach ($dosenList as $dosen): ?>
                    <option value="<?= escapeHtml($dosen['nama_dosen']) ?>"><?= escapeHtml($dosen['nama_dosen']) ?> (<?= escapeHtml($dosen['nip']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <textarea class="form-control" id="pengajar" name="pengajar" rows="4" placeholder="Pilih dari daftar dosen di atas, atau ketik nama secara manual"><?= escapeHtml($values['pengajar']) ?></textarea>
        </div>
        <div class="col-12"><label class="form-label" for="catatan">Catatan <span class="text-secondary">(satu baris per catatan)</span></label><textarea class="form-control" id="catatan" name="catatan" rows="3"><?= escapeHtml($values['catatan']) ?></textarea></div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-success" type="submit">Simpan Metadata</button>
            <a class="btn btn-outline-secondary" href="<?= escapeHtml($app['base_url']) ?>results/detail.php?id=<?= (int) $generateId ?>">Batal</a>
        </div>
    </div>
</form>
