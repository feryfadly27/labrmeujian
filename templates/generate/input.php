<div class="page-heading">
    <h1 class="h2 mb-1"><?= escapeHtml($pageTitle) ?></h1>
    <p class="mb-0">Isi identitas ujian terlebih dahulu, lalu sistem langsung membuat workstation.</p>
</div>
<?php if ($error !== null): ?><div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>
<form class="page-panel" method="post" action="generate.php">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
    <h2 class="h5 mb-3">Identitas Ujian</h2>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="jenis_ujian">Jenis Ujian</label><select class="form-select" id="jenis_ujian" name="jenis_ujian" required><?php foreach (['UAS', 'UTS', 'KUIS', 'REMEDIAL'] as $examType): ?><option value="<?= $examType ?>"<?= $examType === $values['jenis_ujian'] ? ' selected' : '' ?>><?= $examType ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label" for="mata_kuliah_id">Mata Kuliah</label><select class="form-select" id="mata_kuliah_id" name="mata_kuliah_id" required><option value="">Pilih mata kuliah</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['id'] ?>"<?= (string) $course['id'] === (string) $values['mata_kuliah_id'] ? ' selected' : '' ?>><?= escapeHtml($course['kode_mk'] . ' - ' . $course['nama_mk']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label" for="kelas_id">Kelas</label><select class="form-select" id="kelas_id" name="kelas_id" required><option value="">Pilih kelas</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"<?= (string) $class['id'] === (string) $values['kelas_id'] ? ' selected' : '' ?>><?= escapeHtml($class['kode_kelas'] . ' - ' . $class['nama_kelas']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label" for="tanggal_ujian">Tanggal Ujian</label><input class="form-control" id="tanggal_ujian" name="tanggal_ujian" type="date" value="<?= escapeHtml($values['tanggal_ujian']) ?>" required></div>
        <div class="col-md-4"><label class="form-label" for="waktu">Waktu</label><input class="form-control" id="waktu" name="waktu" maxlength="50" placeholder="08:00 - 12:00" value="<?= escapeHtml($values['waktu']) ?>"></div>
        <div class="col-md-4"><label class="form-label" for="ruang">Ruang</label><input class="form-control" id="ruang" name="ruang" maxlength="100" value="<?= escapeHtml($values['ruang']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="sesi">Sesi</label><input class="form-control" id="sesi" name="sesi" maxlength="50" value="<?= escapeHtml($values['sesi']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="kelompok">Kelompok</label><input class="form-control" id="kelompok" name="kelompok" maxlength="100" value="<?= escapeHtml($values['kelompok']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="program_studi">Program Studi</label><input class="form-control" id="program_studi" name="program_studi" maxlength="150" value="<?= escapeHtml($values['program_studi']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="tahun_akademik">Tahun Akademik</label><input class="form-control" id="tahun_akademik" name="tahun_akademik" maxlength="20" placeholder="2026/2027" value="<?= escapeHtml($values['tahun_akademik']) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="semester">Semester</label><select class="form-select" id="semester" name="semester"><option value="">Pilih semester</option><?php for ($semester = 1; $semester <= 6; $semester++): ?><option value="<?= $semester ?>"<?= (string) $semester === (string) $values['semester'] ? ' selected' : '' ?>><?= $semester ?></option><?php endfor; ?></select></div>
        <div class="col-md-6">
            <label class="form-label" for="pengawas">Pengawas <span class="text-secondary">(satu nama per baris)</span></label>
            <select class="form-select mb-2" id="pengawas_pilihan" data-target="pengawas">
                <option value="">+ Tambah dari Data Dosen...</option>
                <?php foreach ($dosenList as $dosen): ?>
                    <option value="<?= escapeHtml($dosen['nama_dosen']) ?>"><?= escapeHtml($dosen['nama_dosen']) ?> (<?= escapeHtml($dosen['nip']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <textarea class="form-control" id="pengawas" name="pengawas" rows="3" placeholder="Pilih dari daftar dosen di atas, atau ketik nama secara manual"><?= escapeHtml($values['pengawas']) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="pengajar">Pengajar <span class="text-secondary">(satu nama per baris)</span></label>
            <select class="form-select mb-2" id="pengajar_pilihan" data-target="pengajar">
                <option value="">+ Tambah dari Data Dosen...</option>
                <?php foreach ($dosenList as $dosen): ?>
                    <option value="<?= escapeHtml($dosen['nama_dosen']) ?>"><?= escapeHtml($dosen['nama_dosen']) ?> (<?= escapeHtml($dosen['nip']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <textarea class="form-control" id="pengajar" name="pengajar" rows="3" placeholder="Pilih dari daftar dosen di atas, atau ketik nama secara manual"><?= escapeHtml($values['pengajar']) ?></textarea>
        </div>
        <div class="col-12"><label class="form-label" for="catatan">Catatan</label><textarea class="form-control" id="catatan" name="catatan" rows="2"><?= escapeHtml($values['catatan']) ?></textarea></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-success" type="submit">Generate Sekarang</button><a class="btn btn-outline-secondary" href="schedules.php">Kembali</a></div>
    </div>
</form>
