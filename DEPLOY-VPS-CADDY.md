# Panduan Deploy ke VPS dengan Caddy (Production)

Target stack: **Ubuntu 22.04/24.04 LTS + Caddy 2 + PHP-FPM 8.1+ + MariaDB**.
Aplikasi ini PHP native tanpa framework — tidak ada build step, tidak ada `.env` loader otomatis, jadi beberapa langkah manual di bawah wajib diikuti persis.

Kenapa Caddy: HTTPS otomatis (Let's Encrypt terintegrasi, tanpa certbot terpisah), config jauh lebih ringkas dari Nginx, auto-renew sertifikat tanpa cron tambahan.

Ganti setiap placeholder `<...>` sesuai kondisi VPS Anda.

---

## 0. Prasyarat

- VPS dengan akses root/sudo, domain (atau subdomain) sudah diarahkan (DNS A record) ke IP VPS — **wajib** untuk Caddy karena ia butuh domain valid untuk terbitkan sertifikat HTTPS otomatis (tanpa domain, Caddy tetap bisa jalan HTTP-only, tapi kehilangan fitur utamanya).
- PHP minimal **8.1** (dompdf mensyaratkan `>=8.1,<8.6`).
- Ekstensi PHP wajib: `dom`, `mbstring`, `gd`, `json`, `zip`, `xml`, `xmlwriter`, `fileinfo`, `zlib/zip`, `curl`, `pdo_mysql`. Opsional: `intl`, `gmagick`/`imagick`.
- Port 80 dan 443 harus bebas (Caddy pakai keduanya — 80 untuk validasi ACME HTTP-01 dan redirect, 443 untuk HTTPS).

---

## 1. Update sistem & install paket dasar

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl unzip git debian-keyring debian-archive-keyring apt-transport-https software-properties-common
```

## 2. Install Caddy

```bash
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update
sudo apt install -y caddy

caddy version
sudo systemctl status caddy
```

Caddy sudah otomatis `enable`d dan berjalan sebagai service systemd (`caddy.service`), memuat config dari `/etc/caddy/Caddyfile`.

## 3. Install PHP 8.3 (atau versi 8.1–8.5) + ekstensi

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
  php8.3-zip php8.3-gd php8.3-curl php8.3-mysql php8.3-intl php8.3-bcmath

php -v
sudo systemctl enable --now php8.3-fpm
sudo systemctl status php8.3-fpm
```

Caddy tidak punya modul PHP bawaan (beda dari Nginx yang butuh `fastcgi_pass` manual, Caddy pakai directive `php_fastcgi` yang lebih ringkas — tetap butuh PHP-FPM berjalan terpisah).

**Penting — permission socket**: Caddy berjalan sebagai user `caddy`, sedangkan PHP-FPM pool default berjalan sebagai `www-data` dan socket-nya (`/run/php/php8.3-fpm.sock`) biasanya hanya bisa diakses oleh `www-data`. Tanpa penyesuaian, Caddy akan gagal connect ke socket (error `permission denied` di log). Tambahkan user `caddy` ke grup `www-data`:

```bash
sudo usermod -aG www-data caddy
sudo systemctl restart caddy
```

## 4. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## 5. Install & amankan MariaDB

```bash
sudo apt install -y mariadb-server
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
```

Ikuti wizard: set root password, hapus anonymous user, disable remote root login, hapus test database — jawab `Y` di semua.

Buat database dan user khusus aplikasi (**jangan pakai root** untuk koneksi aplikasi):

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE jadwal_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'jadwal_app'@'localhost' IDENTIFIED BY '<password-kuat-acak>';
GRANT SELECT, INSERT, UPDATE, DELETE ON jadwal_lab.* TO 'jadwal_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> `GRANT` sengaja dibatasi ke DML saja (bukan `ALL PRIVILEGES`) — user aplikasi tidak butuh `CREATE`/`DROP`/`ALTER` setelah schema di-load. Migrasi skema pakai user root/admin terpisah (langkah 7).

Generate password kuat kalau perlu:
```bash
openssl rand -hex 24
```

## 6. Deploy source code

```bash
sudo mkdir -p /var/www/jadwal-lab
sudo chown $USER:$USER /var/www/jadwal-lab
# opsi A: rsync dari mesin lokal (jalankan dari lokal, bukan di VPS)
#   rsync -avz --exclude=vendor --exclude=.env --exclude=storage/uploads/*.xlsx \
#     /Users/feryfadly/Documents/Podman/jadwal-labrme/ <user>@<vps-ip>:/var/www/jadwal-lab/
# opsi B: git clone (jika repo sudah di-push ke GitHub/GitLab)
#   git clone <repo-url> /var/www/jadwal-lab
```

Install dependency PHP (mode production):

```bash
cd /var/www/jadwal-lab
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
```

## 7. Load schema database

Jalankan **berurutan**, tiga migrasi harus dijalankan semua (bukan cuma yang pertama):

```bash
mysql -u root -p jadwal_lab < database/schema.sql
mysql -u root -p jadwal_lab < database/migrations/001_generate_export_metadata.sql
mysql -u root -p jadwal_lab < database/migrations/002_security_performance_hardening.sql
mysql -u root -p jadwal_lab < database/migrations/003_dosen.sql
```

Migrasi 002 menambahkan index performa (`mahasiswa.angkatan`, `generate_workstation_detail.nim_snapshot`, `audit_log.action`) dan tabel `rate_limit_attempt` (rate limit login/pencarian NIM publik — **wajib** ada, aplikasi akan error kalau tabel ini tidak ditemukan saat user login/mencari NIM). Migrasi 003 menambahkan tabel `dosen` (referensi Pengawas/Pengajar).

(Opsional) Isi data referensi dosen dari seed yang tersedia:

```bash
mysql -u root -p jadwal_lab < database/seeds/dosen_seed.sql
```

Buat user admin pertama (tabel `users` tidak ada seeder, isi manual):

```bash
php -r '
$hash = password_hash("<password-admin-kuat>", PASSWORD_DEFAULT);
echo $hash . PHP_EOL;
'
```

```sql
INSERT INTO users (username, password_hash, nama_lengkap, role)
VALUES ('admin', '<hash-hasil-perintah-di-atas>', 'Administrator', 'admin');
```

## 8. Konfigurasi environment untuk PHP-FPM

Aplikasi ini **tidak** punya `.env` loader — nilainya harus di-export ke environment PHP-FPM langsung (bukan sekadar file `.env` yang dibaca sendiri oleh app):

```bash
sudo tee /etc/php/8.3/fpm/pool.d/jadwal-lab-env.conf > /dev/null <<'EOF'
env[DB_HOST] = 127.0.0.1
env[DB_PORT] = 3306
env[DB_DATABASE] = jadwal_lab
env[DB_USERNAME] = jadwal_app
env[DB_PASSWORD] = <password-user-jadwal_app>
EOF
```

Pastikan `clear_env` **tidak** aktif (`no`) agar `env[]` di atas benar-benar diteruskan ke proses PHP — cek/tambahkan di pool config yang sama (`/etc/php/8.3/fpm/pool.d/www.conf`, biasanya sudah `no` secara default di distribusi Ubuntu, tapi pastikan):

```ini
clear_env = no
```

Restart PHP-FPM setelah perubahan:

```bash
sudo systemctl restart php8.3-fpm
```

## 9. Set permission direktori

```bash
sudo chown -R www-data:www-data /var/www/jadwal-lab
sudo find /var/www/jadwal-lab -type d -exec chmod 755 {} \;
sudo find /var/www/jadwal-lab -type f -exec chmod 644 {} \;
sudo chmod -R 770 /var/www/jadwal-lab/storage/uploads
```

`storage/uploads/` perlu writable oleh `www-data` (proses import Excel menulis ke sana) tapi tetap **tidak boleh** bisa diakses langsung lewat URL — ditangani otomatis di langkah 10 karena document root Caddy diarahkan ke `public/` saja.

## 10. Konfigurasi Caddyfile

Document root **harus** menunjuk ke `public/`, bukan root project — supaya `src/`, `config/`, `database/`, `storage/`, `vendor/` tidak bisa diakses langsung dari browser. Beda dari Nginx, Caddy tidak butuh blok `location` manual untuk PHP — cukup satu directive `php_fastcgi`.

Siapkan dulu direktori log (Caddy jalan sebagai user `caddy`, bukan root):

```bash
sudo mkdir -p /var/log/caddy
sudo chown caddy:caddy /var/log/caddy
sudo chmod 750 /var/log/caddy
```

```bash
sudo tee /etc/caddy/Caddyfile > /dev/null <<'EOF'
<domain-anda.com> {
    root * /var/www/jadwal-lab/public
    encode zstd gzip

    php_fastcgi unix//run/php/php8.3-fpm.sock {
        env HTTPS on
    }
    file_server

    # Header keamanan tambahan di level web server (aplikasi sudah kirim
    # CSP/X-Frame-Options/X-Content-Type-Options sendiri lewat PHP — ini
    # sekadar lapis pertahanan tambahan, tidak duplikat/konflik).
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
    }

    # Blokir akses langsung ke folder/file sensitif di luar public/ —
    # sebenarnya sudah tidak bisa diakses karena document root = public/,
    # ini cuma lapis pertahanan tambahan kalau root pernah salah diarahkan.
    @blocked path /config/* /database/* /src/* /storage/* /templates/* /vendor/* /bootstrap.php /composer.json /composer.lock /.env
    respond @blocked 404

    log {
        output file /var/log/caddy/jadwal-lab-access.log {
            roll_size 100mb
            roll_keep 10
            roll_keep_for 720h
        }
        format json
    }
}
EOF

sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

**Penting — `env HTTPS on` di dalam blok `php_fastcgi`**: ini wajib. Tanpa baris ini, PHP-FPM di belakang Caddy **tidak tahu** koneksinya sudah HTTPS (Caddy men-terminasi TLS lalu meneruskan plain FastCGI ke PHP-FPM), sehingga [src/Auth/Session.php](src/Auth/Session.php) tidak akan mengaktifkan cookie `secure`, dan [src/Support/SecurityHeaders.php](src/Support/SecurityHeaders.php) tidak akan mengirim header `Strict-Transport-Security` dari sisi aplikasi. Baris `env HTTPS on` inilah yang membuat `$_SERVER['HTTPS']` terisi `on` di PHP.

Ganti `<domain-anda.com>` dengan domain asli — begitu `systemctl reload caddy` dijalankan, **Caddy otomatis menerbitkan dan memperpanjang sertifikat Let's Encrypt sendiri**, tidak perlu certbot atau cron manual seperti di Nginx.

`client_max_body_size` ala Nginx tidak diperlukan di Caddy — defaultnya tidak membatasi ukuran body (batas efektif datang dari `upload_max_filesize`/`post_max_size` di `php.ini`, langkah 12).

## 11. Verifikasi HTTPS otomatis

```bash
sudo systemctl status caddy
curl -I https://<domain-anda.com>/login.php
```

Harus langsung dapat `HTTP/2 200` dengan sertifikat valid tanpa langkah tambahan. Kalau gagal, cek log:

```bash
sudo journalctl -u caddy --no-pager -n 50
```

Penyebab umum: DNS belum propagasi, port 80/443 diblokir firewall (langkah 13), atau ada web server lain (Apache/Nginx) yang masih menempel di port 80/443.

## 12. Hardening `php.ini` untuk production

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php8.3-fpm-jadwal-lab.log
expose_php = Off
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
memory_limit = 256M
```

`display_errors = Off` penting — kode aplikasi sudah pakai `error_log()` + pesan generik ke user, jangan sampai PHP sendiri membocorkan stack trace mentah kalau ada fatal error di luar `try/catch`.

```bash
sudo systemctl restart php8.3-fpm
```

## 13. Firewall

```bash
sudo apt install -y ufw
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

MariaDB hanya listen di `127.0.0.1` secara default (`bind-address` di `/etc/mysql/mariadb.conf.d/50-server.cnf`) — pastikan **tidak** dibuka ke publik; tidak perlu rule ufw untuk port 3306.

## 14. Verifikasi deployment

```bash
curl -I https://<domain-anda.com>/         # harus 200 (landing page publik, bukan redirect ke login)
curl -I https://<domain-anda.com>/login.php # harus 200
```

Buka browser lalu cek berurutan:
1. Landing page publik (`/`) tampil tanpa perlu login (jadwal ujian + link cari nomor workstation).
2. Login pakai user admin yang dibuat di langkah 7.
3. Dashboard, Data Mahasiswa (cek pagination, sort, filter kelas & angkatan), Data Dosen.
4. Generate workstation baru, lalu cek Hasil Generate → export PDF/XLSX/DOCX dan tombol Print (tanpa unduh) satu per satu.
5. Buka DevTools → cek cookie session punya flag `Secure` (kalau tidak, `env HTTPS on` di langkah 10 belum benar).

Cek log kalau ada error:

```bash
sudo tail -f /var/log/caddy/jadwal-lab-access.log
sudo tail -f /var/log/php8.3-fpm-jadwal-lab.log
sudo journalctl -u caddy -f
```

## 15. Backup rutin (rekomendasi, belum ada di kode)

Tidak ada mekanisme backup otomatis di dalam aplikasi. Minimal setup cron harian:

```bash
sudo mkdir -p /var/backups/jadwal-lab
sudo tee /etc/cron.daily/jadwal-lab-backup > /dev/null <<'EOF'
#!/bin/sh
TIMESTAMP=$(date +%F)
mysqldump -u root jadwal_lab | gzip > /var/backups/jadwal-lab/db-$TIMESTAMP.sql.gz
tar czf /var/backups/jadwal-lab/uploads-$TIMESTAMP.tar.gz /var/www/jadwal-lab/storage/uploads
find /var/backups/jadwal-lab -type f -mtime +14 -delete
EOF
sudo chmod +x /etc/cron.daily/jadwal-lab-backup
```

Sesuaikan retensi (`-mtime +14` = simpan 14 hari) dan idealnya salin backup ke storage terpisah (S3-compatible / VPS lain).

---

## Checklist ringkas untuk AI/operator yang menjalankan ulang deploy ini

1. [ ] Caddy terpasang & aktif (`systemctl status caddy`), PHP 8.1–8.5 + ekstensi wajib terpasang
2. [ ] User `caddy` masuk grup `www-data` (`usermod -aG www-data caddy`) — tanpa ini Caddy gagal connect ke socket PHP-FPM
3. [ ] `composer install --no-dev --optimize-autoloader` sukses tanpa error
4. [ ] MariaDB: DB + user `jadwal_app` dibuat dengan privilege terbatas (bukan root)
5. [ ] Ketiga migrasi (`001`, `002`, `003`) dijalankan berurutan setelah `schema.sql` — **tabel `rate_limit_attempt` dari migrasi 002 wajib ada**, tanpa itu login/cari NIM akan error
6. [ ] User admin pertama dibuat manual via `password_hash()` + INSERT SQL
7. [ ] `env[DB_*]` di pool PHP-FPM terisi, `clear_env = no`, PHP-FPM di-restart
8. [ ] Ownership `www-data:www-data`, `storage/uploads` writable (770) tapi tidak public
9. [ ] Caddyfile: `root` = `public/` (bukan root project), directive `php_fastcgi` dengan **`env HTTPS on`** di dalamnya
10. [ ] HTTPS otomatis aktif (tidak perlu certbot manual — cek `curl -I https://...` langsung 200)
11. [ ] `display_errors = Off` di php.ini production
12. [ ] Firewall aktif (port 80, 443, SSH saja), port DB tidak terbuka ke publik
13. [ ] Cookie session punya flag `Secure` setelah login (cek DevTools) — bukti `env HTTPS on` bekerja
14. [ ] Login admin, landing page publik, Data Dosen, generate, ketiga format export (PDF/XLSX/DOCX), dan tombol Print dites manual setelah deploy
15. [ ] Cron backup DB + storage/uploads terpasang

## Perbedaan penting dari deploy Nginx

- Tidak perlu certbot/cron renewal terpisah — Caddy urus sendiri.
- Tidak ada blok `location` manual per jenis file — satu directive `php_fastcgi` sudah cukup.
- Wajib tambahkan `env HTTPS on` secara eksplisit di dalam `php_fastcgi` — beda dari Nginx yang biasanya sudah otomatis lewat `fastcgi_param HTTPS on` di snippet default sebagian distro, di Caddy ini harus ditulis manual atau session cookie tidak akan `secure`.
- Caddy jalan sebagai user `caddy` (bukan `www-data` seperti Nginx), sehingga perlu penyesuaian tambahan agar bisa mengakses socket PHP-FPM (langkah 3) — Nginx yang jalan sebagai `www-data` tidak punya masalah ini karena user-nya sama dengan pool PHP-FPM default.

## Known issue yang perlu diperhatikan setelah live

- **Export tidak dibatasi role** — [modules/exports/index.php](modules/exports/index.php) hanya `requireAuthentication()`, bukan `requireRole('admin')` seperti generate. User role `petugas` bisa export/print hasil generate ID berapa pun (perilaku ini disengaja per keputusan sebelumnya — petugas memang bertugas mencetak dokumen ujian).
- Rate limit login & pencarian NIM publik sudah berbasis database ([src/Auth/RateLimiter.php](src/Auth/RateLimiter.php), tabel `rate_limit_attempt`) — bukan lagi session-based, jadi tidak bisa dilewati dengan menghapus cookie. Tetap pertimbangkan `rate_limit` di level Caddy (`request_body` + plugin, atau `fail2ban` yang membaca log Caddy) sebagai lapis pertahanan tambahan untuk brute-force volumetrik di level jaringan.
