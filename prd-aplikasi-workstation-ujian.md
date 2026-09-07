# PRD Aplikasi Pengacak Nomor Workstation Ujian Lab

## Ringkasan Produk

Aplikasi ini adalah sistem web internal berbasis PHP native dan MySQL untuk mengelola pengacakan nomor urut atau workstation peserta ujian laboratorium per mata kuliah, tanggal ujian, dan kelas. Sistem dirancang menggunakan antarmuka admin sederhana bergaya AdminLTE, mendukung upload data referensi mahasiswa dan kelas, menyimpan hasil generate secara permanen, serta menyediakan keluaran dokumen dalam format PDF, Excel, dan DOCX.[cite:56][cite:58][cite:60]

Tujuan utama aplikasi adalah memastikan pembagian nomor workstation berlangsung cepat, adil, terdokumentasi, tidak duplikat pada kombinasi jadwal yang sama, dan mudah dicetak sebagai daftar hadir ujian. Sistem juga harus menolak generate baru bila kombinasi mata kuliah, tanggal ujian, dan kelas sudah pernah dibuat, kecuali dilakukan pada tanggal ujian yang berbeda atau melalui mekanisme pengelolaan data yang sah oleh admin.[cite:58]

## Latar Belakang

Di lingkungan laboratorium, pembagian nomor urut atau workstation ujian sering masih dilakukan secara manual, sehingga rawan terjadi duplikasi, perubahan daftar mendadak, dan kesalahan saat menyusun daftar hadir. Kondisi ini membuat proses administrasi ujian menjadi lambat dan menyulitkan ketika panitia membutuhkan daftar final yang konsisten antara proses generate, pelaksanaan ujian, dan dokumen cetak.

Sistem ini dibutuhkan untuk memusatkan data mahasiswa, kelas, mata kuliah, dan jadwal ujian dalam satu aplikasi sederhana. Dengan begitu, setiap hasil pengacakan dapat tersimpan permanen, dapat ditelusuri kembali, serta dapat diekspor menjadi dokumen operasional untuk kebutuhan pelaksanaan ujian di laboratorium.[cite:56][cite:58][cite:60]

## Tujuan Produk

Aplikasi ditujukan untuk mencapai sasaran berikut:

- Mempermudah admin laboratorium dalam membuat daftar workstation ujian per kelas.
- Menjamin satu kombinasi mata kuliah, tanggal ujian, dan kelas hanya memiliki satu daftar aktif.
- Menyediakan hasil generate yang tetap sama setelah disimpan.
- Mempercepat pembuatan daftar hadir ujian dalam format cetak.
- Memungkinkan ekspor hasil ke PDF, Excel, dan DOCX menggunakan library PHP yang umum dipakai.[cite:56][cite:58][cite:60]

## Ruang Lingkup MVP

Versi awal aplikasi mencakup fungsi inti yang benar-benar dibutuhkan untuk operasional harian laboratorium. Fitur di luar kebutuhan inti, seperti multi-lab, tanda tangan digital, atau persetujuan berlapis, tidak dimasukkan pada tahap pertama agar implementasi tetap sederhana dan cepat selesai.

Cakupan MVP meliputi:

- Login admin.
- Manajemen data kelas.
- Manajemen data mahasiswa.
- Manajemen data mata kuliah.
- Manajemen jadwal ujian.
- Upload data referensi mahasiswa dan kelas dari file spreadsheet.
- Generate daftar workstation berdasarkan kelas, mata kuliah, dan tanggal ujian.
- Validasi duplikasi generate.
- Penyimpanan hasil generate secara permanen.
- Tampilan detail hasil generate.
- Cetak atau ekspor hasil generate ke PDF, Excel, dan DOCX.[cite:56][cite:58][cite:60]

## Pengguna dan Peran

### Admin Laboratorium

Admin laboratorium adalah pengguna utama sistem. Admin bertanggung jawab mengelola master data, membuat jadwal ujian, melakukan generate workstation, memeriksa hasil generate, dan mengekspor dokumen daftar hadir.

### Koordinator atau Petugas Ujian

Pada tahap MVP, peran ini dapat menggunakan akun admin yang sama atau akun dengan hak baca terbatas bila nanti dibutuhkan. Fungsi utamanya adalah melihat hasil generate dan mencetak dokumen untuk pelaksanaan ujian.

## Masalah yang Diselesaikan

Aplikasi ini dirancang untuk mengatasi masalah operasional berikut:

- Pengacakan nomor workstation masih manual dan memakan waktu.
- Data mahasiswa per kelas belum tersusun rapi dalam satu sistem.
- Daftar ujian bisa tergenerate ganda untuk kombinasi jadwal yang sama.
- Hasil pengacakan dapat berubah jika tidak disimpan dengan benar.
- Daftar hadir ujian harus disusun ulang secara manual setiap kali ada pelaksanaan.
- Dokumen keluaran perlu tersedia dalam beberapa format agar sesuai kebutuhan administrasi.[cite:56][cite:58][cite:60]

## Aturan Bisnis

Aturan bisnis berikut menjadi dasar logika aplikasi:

1. Satu data generate ditentukan oleh kombinasi `mata kuliah + tanggal ujian + kelas`.
2. Jika kombinasi tersebut sudah ada, sistem wajib menolak generate baru dan menampilkan notifikasi bahwa daftar sudah tersedia.
3. Mata kuliah yang sama boleh digenerate kembali jika tanggal ujiannya berbeda.
4. Hasil generate yang sudah disimpan menjadi data tetap dan tidak berubah otomatis.
5. Perubahan pada hasil generate hanya dapat dilakukan oleh admin melalui aksi resmi seperti hapus atau generate ulang sesuai kebijakan sistem.
6. Data mahasiswa yang ikut generate diambil dari master mahasiswa berdasarkan kelas yang dipilih.
7. Output daftar hadir harus selalu mengacu pada hasil generate yang tersimpan, bukan pengacakan ulang saat proses cetak.
8. Sistem harus menyimpan waktu generate dan admin yang melakukan proses tersebut untuk kebutuhan audit internal.

## Alur Proses Utama

### Alur persiapan data

1. Admin login ke sistem.
2. Admin menambahkan atau mengimpor data kelas.
3. Admin menambahkan atau mengimpor data mahasiswa dan mengaitkannya ke kelas.
4. Admin menambahkan data mata kuliah.
5. Admin membuat jadwal ujian yang berisi mata kuliah, tanggal ujian, dan kelas sasaran.

### Alur generate workstation

1. Admin membuka menu generate workstation.
2. Admin memilih mata kuliah, tanggal ujian, dan kelas.
3. Sistem memeriksa apakah kombinasi tersebut sudah pernah digenerate.
4. Jika sudah ada, sistem menolak proses dan menampilkan notifikasi.
5. Jika belum ada, sistem mengambil daftar mahasiswa pada kelas terkait.
6. Sistem mengacak urutan mahasiswa.
7. Sistem menetapkan nomor urut atau nomor workstation.
8. Sistem menyimpan hasil generate ke database.
9. Sistem menampilkan hasil generate yang siap dicetak atau diekspor.

### Alur cetak atau ekspor

1. Admin membuka detail hasil generate.
2. Admin memilih jenis keluaran: PDF, Excel, atau DOCX.
3. Sistem membentuk dokumen sesuai template.
4. Sistem mengunduh dokumen yang berisi identitas ujian dan daftar peserta sesuai hasil generate tersimpan.[cite:56][cite:58][cite:60]

## Kebutuhan Fungsional

### 1. Autentikasi

- Sistem harus menyediakan halaman login admin.
- Sistem harus memvalidasi username dan password.
- Sistem harus menyediakan logout.
- Sistem harus membatasi akses menu hanya untuk pengguna yang sudah login.

### 2. Manajemen kelas

- Admin dapat menambah data kelas.
- Admin dapat mengubah data kelas.
- Admin dapat menghapus data kelas yang belum dipakai atau sesuai kebijakan.
- Admin dapat melihat daftar kelas.
- Admin dapat mencari kelas berdasarkan nama atau kode.

### 3. Manajemen mahasiswa

- Admin dapat menambah data mahasiswa secara manual.
- Admin dapat mengubah data mahasiswa.
- Admin dapat menghapus data mahasiswa.
- Admin dapat melihat daftar mahasiswa.
- Admin dapat memfilter mahasiswa berdasarkan kelas.
- Admin dapat mengimpor data mahasiswa dari file Excel.

### 4. Manajemen mata kuliah

- Admin dapat menambah mata kuliah.
- Admin dapat mengubah mata kuliah.
- Admin dapat menghapus mata kuliah.
- Admin dapat melihat daftar mata kuliah.

### 5. Manajemen jadwal ujian

- Admin dapat membuat jadwal ujian.
- Jadwal minimal memuat mata kuliah, tanggal ujian, kelas, dan opsional sesi atau keterangan.
- Admin dapat mengubah jadwal ujian.
- Admin dapat menghapus jadwal ujian yang belum memiliki generate aktif atau sesuai kebijakan.
- Admin dapat melihat daftar jadwal ujian.

### 6. Generate workstation

- Admin dapat memilih kombinasi mata kuliah, tanggal ujian, dan kelas untuk proses generate.
- Sistem harus memeriksa keberadaan generate sebelumnya pada kombinasi yang sama.
- Sistem harus menolak generate bila data sudah ada.
- Sistem harus menampilkan pesan notifikasi yang jelas saat generate ditolak.
- Sistem harus mengacak urutan mahasiswa dari kelas terpilih.
- Sistem harus menyimpan hasil generate ke tabel header dan detail.
- Sistem harus menampilkan hasil generate setelah proses selesai.

### 7. Penyimpanan hasil generate

- Sistem harus menyimpan data identitas generate, termasuk tanggal ujian, mata kuliah, kelas, waktu generate, dan pengguna pembuat.
- Sistem harus menyimpan detail peserta berikut nomor urut atau workstation.
- Sistem harus memastikan data hasil generate tidak berubah kecuali ada tindakan admin.

### 8. Detail dan pencarian hasil

- Admin dapat melihat daftar seluruh hasil generate.
- Admin dapat mencari hasil berdasarkan mata kuliah, tanggal ujian, atau kelas.
- Admin dapat membuka detail satu hasil generate.
- Admin dapat melihat peserta sesuai urutan hasil generate.

### 9. Ekspor dokumen

- Sistem harus dapat menghasilkan file PDF dari hasil generate.[cite:56]
- Sistem harus dapat menghasilkan file Excel dari hasil generate.[cite:58]
- Sistem harus dapat menghasilkan file DOCX dari hasil generate.[cite:60]
- Dokumen harus memuat minimal nama mata kuliah, tanggal ujian, kelas, dan tabel peserta.
- Dokumen harus menggunakan data yang sudah tersimpan, bukan generate ulang.

### 10. Notifikasi sistem

- Sistem harus menampilkan notifikasi sukses saat data berhasil disimpan.
- Sistem harus menampilkan notifikasi gagal saat validasi tidak terpenuhi.
- Sistem harus menampilkan notifikasi khusus ketika daftar untuk kombinasi yang sama sudah ada.

## Kebutuhan Nonfungsional

### 1. Teknologi

- Sistem dibangun menggunakan PHP native.
- Sistem menggunakan MySQL sebagai basis data, sesuai kebiasaan stack pengguna pada proyek sistem internal sebelumnya.[cite:2]
- Sistem menggunakan HTML, CSS, JavaScript, dan template admin sederhana seperti AdminLTE untuk antarmuka.
- Sistem dapat menggunakan Composer untuk mengelola library pihak ketiga seperti dompdf, PhpSpreadsheet, dan PHPWord.[cite:56][cite:58][cite:60]

### 2. Usability

- Antarmuka harus sederhana, familiar, dan mudah dipahami admin nonteknis.
- Form input harus ringkas dan tidak membingungkan.
- Navigasi utama harus tersedia di sidebar.
- Halaman daftar data harus menggunakan tabel yang mudah dipindai.

### 3. Kinerja

- Proses buka halaman master data harus responsif pada penggunaan internal normal.
- Generate untuk satu kelas normal harus selesai dalam waktu singkat pada jumlah peserta laboratorium umum.
- Ekspor dokumen harus tetap dapat dilakukan tanpa mengubah data sumber.

### 4. Keamanan

- Password pengguna harus disimpan dalam bentuk hash.
- Seluruh query database harus menggunakan prepared statement untuk mengurangi risiko SQL injection.
- Sistem harus melakukan validasi input pada sisi server.
- Upload file harus dibatasi pada format yang diizinkan.
- Session login harus dikelola dengan aman.

### 5. Reliabilitas data

- Hasil generate yang sudah tersimpan tidak boleh berubah karena proses tampilan atau ekspor.
- Validasi duplikasi harus dilakukan di level aplikasi dan dianjurkan juga di level database dengan unique constraint.
- Proses simpan generate harus menggunakan transaksi database agar data header dan detail konsisten.

### 6. Maintainability

- Meskipun tanpa framework, struktur folder harus dipisah antara config, modul, template, dan library.
- Kode harus dibuat modular berdasarkan fitur utama.
- Template dokumen ekspor harus dapat disesuaikan tanpa mengubah logika utama sistem.

## Use Case Utama

| Aktor | Use case | Deskripsi singkat |
|---|---|---|
| Admin | Login | Masuk ke sistem menggunakan akun yang valid |
| Admin | Kelola kelas | Tambah, ubah, hapus, dan lihat data kelas |
| Admin | Kelola mahasiswa | Tambah, ubah, hapus, impor, dan lihat mahasiswa |
| Admin | Kelola mata kuliah | Tambah, ubah, hapus, dan lihat mata kuliah |
| Admin | Kelola jadwal ujian | Menentukan mata kuliah, tanggal, dan kelas ujian |
| Admin | Generate workstation | Membuat daftar urut peserta ujian per kombinasi jadwal |
| Admin | Lihat hasil generate | Memeriksa daftar yang sudah dibuat |
| Admin | Ekspor dokumen | Menghasilkan PDF, Excel, atau DOCX |

## Desain Data Konseptual

### Entitas utama

- `users`
- `kelas`
- `mahasiswa`
- `mata_kuliah`
- `jadwal_ujian`
- `generate_workstation`
- `generate_workstation_detail`
- `import_log` opsional

### Relasi inti

- Satu kelas memiliki banyak mahasiswa.
- Satu mata kuliah memiliki banyak jadwal ujian.
- Satu jadwal ujian terkait ke satu kelas pada desain sederhana MVP, atau bisa dipisah lebih lanjut bila nanti satu jadwal melibatkan banyak kelas.
- Satu generate workstation memiliki banyak detail peserta.
- Satu mahasiswa dapat muncul di banyak hasil generate berbeda sesuai jadwal yang berbeda.

## Struktur Data yang Direkomendasikan

### Tabel `users`

- id
- username
- password_hash
- nama_lengkap
- role
- created_at

### Tabel `kelas`

- id
- kode_kelas
- nama_kelas
- created_at

### Tabel `mahasiswa`

- id
- nim
- nama_mahasiswa
- kelas_id
- angkatan
- created_at

### Tabel `mata_kuliah`

- id
- kode_mk
- nama_mk
- created_at

### Tabel `jadwal_ujian`

- id
- mata_kuliah_id
- kelas_id
- tanggal_ujian
- sesi
- keterangan
- created_at

### Tabel `generate_workstation`

- id
- jadwal_ujian_id
- mata_kuliah_id
- kelas_id
- tanggal_ujian
- generated_by
- generated_at
- status

Tabel ini perlu diberi aturan keunikan pada kombinasi `mata_kuliah_id`, `kelas_id`, dan `tanggal_ujian` agar duplikasi dapat dicegah secara kuat di basis data.[cite:2]

### Tabel `generate_workstation_detail`

- id
- generate_id
- mahasiswa_id
- nomor_urut
- workstation_no

## Validasi Sistem

Validasi minimal yang harus ada:

- Username dan password wajib diisi saat login.
- Data kelas tidak boleh kosong.
- NIM mahasiswa harus unik.
- Mata kuliah harus dipilih saat membuat jadwal.
- Tanggal ujian wajib dipilih.
- Kelas wajib dipilih saat generate.
- Generate ditolak bila kombinasi yang sama sudah ada.
- Ekspor hanya dapat dilakukan jika data generate tersedia.
- File impor harus sesuai ekstensi dan struktur kolom yang ditentukan.[cite:58]

## Aturan Pengacakan

Metode pengacakan dapat menggunakan proses shuffle pada array mahasiswa yang diambil berdasarkan kelas. Setelah urutan diperoleh, sistem menetapkan nomor urut mulai dari 1 sampai jumlah peserta dan menyimpannya ke tabel detail. Nomor workstation dapat dibuat sama dengan nomor urut pada MVP, lalu dikembangkan kemudian jika laboratorium memiliki skema nomor kursi sendiri.

Prinsip pentingnya bukan pada algoritma yang paling kompleks, tetapi pada konsistensi bahwa hasil shuffle hanya dilakukan sekali per kombinasi data dan hasilnya langsung disimpan permanen. Dengan cara ini, hasil cetak PDF, Excel, dan DOCX selalu identik dengan daftar yang tampil di aplikasi.[cite:56][cite:58][cite:60]

## Spesifikasi Output Dokumen

### PDF

Dokumen PDF digunakan untuk cetak cepat di ruang ujian. Library dompdf dikenal sebagai konverter HTML ke PDF untuk PHP, sehingga template daftar hadir dapat dibuat dari HTML tabel sederhana lalu dirender menjadi PDF.[cite:56][cite:57]

Isi minimal PDF:

- Judul daftar hadir ujian lab.
- Nama mata kuliah.
- Tanggal ujian.
- Kelas.
- Sesi jika ada.
- Tabel peserta berisi nomor urut, nomor workstation, NIM, nama mahasiswa, dan kolom tanda tangan.

### Excel

Dokumen Excel digunakan untuk pengolahan lanjutan atau arsip administratif. PhpSpreadsheet mendukung pembacaan dan penulisan berbagai format spreadsheet sehingga sesuai untuk kebutuhan impor dan ekspor hasil daftar peserta.[cite:58][cite:63]

Isi minimal Excel:

- Sheet daftar hadir.
- Header identitas ujian.
- Tabel peserta dengan kolom nomor, workstation, NIM, nama, dan keterangan tambahan bila diperlukan.

### DOCX

Dokumen DOCX digunakan bila institusi memerlukan format Word yang mudah diedit atau disesuaikan. PHPWord mendukung pembuatan dokumen pengolah kata dan format OOXML seperti `.docx`, sehingga cocok untuk template administrasi laboratorium.[cite:60][cite:62]

Isi minimal DOCX:

- Header institusi atau laboratorium.
- Identitas ujian.
- Tabel daftar hadir.
- Area tanda tangan jika diperlukan.

## Kebutuhan Antarmuka

Antarmuka yang disarankan adalah dashboard admin sederhana dengan pola sidebar, header, kartu ringkasan, form, dan tabel data. Pendekatan ini cocok untuk aplikasi administrasi internal karena lebih menekankan kejelasan alur kerja daripada estetika kompleks.[cite:19]

### Menu utama

- Dashboard
- Data Kelas
- Data Mahasiswa
- Data Mata Kuliah
- Jadwal Ujian
- Generate Workstation
- Hasil Generate
- Logout

### Halaman penting

#### Dashboard

Menampilkan ringkasan jumlah kelas, jumlah mahasiswa, jumlah mata kuliah, jumlah jadwal ujian, dan jumlah hasil generate.

#### Data mahasiswa

Menampilkan tabel mahasiswa dengan filter kelas dan tombol impor Excel.

#### Jadwal ujian

Menampilkan daftar jadwal dan tombol buat jadwal baru.

#### Generate workstation

Berisi form pemilihan mata kuliah, tanggal ujian, dan kelas, lalu tombol generate.

#### Detail hasil

Menampilkan identitas jadwal dan tabel urutan peserta serta tombol ekspor PDF, Excel, dan DOCX.

## Kebutuhan Impor Data

Sistem perlu menyediakan mekanisme impor data awal agar admin tidak menginput mahasiswa satu per satu. Untuk itu, file spreadsheet harus mengikuti template kolom yang disepakati, misalnya `nim`, `nama_mahasiswa`, `kode_kelas`, dan `angkatan`. Library spreadsheet PHP dapat dipakai untuk membaca file ini dan memasukkannya ke basis data setelah lolos validasi format dan isi.[cite:58][cite:63]

Aturan impor yang disarankan:

- Hanya menerima file `.xlsx` dan `.csv` bila dibutuhkan.
- Header kolom harus sesuai template.
- Baris duplikat NIM diberi peringatan atau dilewati sesuai kebijakan.
- Hasil impor menampilkan jumlah data berhasil dan gagal.

## Audit dan Log

Walau sistem sederhana, pencatatan aktivitas dasar tetap penting. Minimal, sistem perlu menyimpan siapa yang melakukan generate dan kapan proses tersebut dilakukan. Catatan ini membantu saat terjadi pertanyaan administratif mengenai asal-usul daftar yang digunakan pada hari ujian.

Log dasar yang disarankan:

- Waktu login terakhir.
- Waktu generate dibuat.
- Pengguna pembuat generate.
- Waktu ekspor dokumen bila diperlukan pada tahap lanjut.

## Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data mahasiswa salah kelas | Hasil generate salah | Sediakan filter, edit data, dan validasi impor |
| Generate ganda pada kombinasi sama | Daftar membingungkan | Validasi aplikasi dan unique constraint database |
| Template dokumen tidak seragam | Dokumen administrasi kurang rapi | Gunakan template ekspor baku per format |
| Upload file salah format | Impor gagal | Sediakan template file dan validasi header |
| Perubahan data tanpa kontrol | Hasil ujian tidak konsisten | Batasi aksi ubah dan simpan log admin |

## Batasan Produk

- Sistem tahap awal hanya untuk penggunaan internal laboratorium.
- Sistem tidak mencakup portal mahasiswa.
- Sistem belum mencakup multi-lab atau pembagian ruang kompleks.
- Sistem belum mencakup tanda tangan digital.
- Sistem belum mencakup integrasi dengan SIAKAD atau sistem kampus lain.

## Roadmap Pengembangan

### Fase 1: MVP

- Login admin
- CRUD kelas
- CRUD mahasiswa
- CRUD mata kuliah
- CRUD jadwal ujian
- Impor mahasiswa
- Generate workstation
- Validasi duplikasi
- Ekspor PDF, Excel, DOCX

### Fase 2: Operasional lanjut

- Riwayat generate ulang
- Filter per sesi atau ruang lab
- Template cetak institusi
- Hak akses admin dan petugas
- Log aktivitas lebih lengkap

### Fase 3: Integrasi

- Sinkronisasi data mahasiswa dari sistem akademik
- Tanda tangan elektronik
- Dashboard statistik penggunaan
- Multi-lab dan multi-ruang

## Kriteria Keberhasilan

Produk dianggap berhasil pada tahap awal jika:

- Admin dapat mengimpor data mahasiswa dan kelas tanpa kesulitan besar.
- Admin dapat membuat jadwal ujian per mata kuliah dan tanggal.
- Sistem dapat menolak generate duplikat pada kombinasi yang sama.
- Sistem dapat menghasilkan daftar peserta yang tetap sama setelah disimpan.
- Admin dapat mengekspor daftar hadir ke PDF, Excel, dan DOCX dengan isi yang konsisten.[cite:56][cite:58][cite:60]

## Rekomendasi Teknis Awal

Agar pengembangan PHP native tetap terstruktur, implementasi sebaiknya memakai pola modular berbasis folder per fitur, prepared statement untuk query database, transaksi saat menyimpan generate, dan Composer untuk dependency dokumen. Pilihan library yang paling relevan untuk kebutuhan keluaran adalah dompdf untuk PDF, PhpSpreadsheet untuk spreadsheet, dan PHPWord untuk dokumen Word karena ketiganya memang menyediakan dukungan pembuatan file yang dibutuhkan sistem ini.[cite:56][cite:58][cite:60]

Untuk tahap implementasi setelah PRD ini, artefak teknis yang paling dibutuhkan berikutnya adalah ERD, SQL schema, struktur folder PHP native, dan daftar halaman UI. Dokumen PRD ini menjadi dasar untuk menyusun desain basis data, backlog pengembangan, serta pembagian modul aplikasi.
