# Panduan Deploy ke VPS (Production)

Target stack: **Ubuntu 22.04/24.04 LTS + Nginx + PHP-FPM 8.1+ + MariaDB**.
Aplikasi ini PHP native tanpa framework — tidak ada build step, tidak ada `.env` loader otomatis, jadi beberapa langkah manual di bawah wajib diikuti persis.

Ganti setiap placeholder `<...>` sesuai kondisi VPS Anda.

---

## 0. Prasyarat

- VPS dengan akses root/sudo, domain (atau subdomain) sudah diarahkan (DNS A record) ke IP VPS.
- PHP minimal **8.1** (dompdf mensyaratkan `>=8.1,<8.6`). PHP 8.4 (versi yang dipakai saat development) juga aman.
- Ekstensi PHP wajib: `dom`, `mbstring`, `gd`, `json`, `zip`, `xml`, `xmlwriter`, `fileinfo`, `zlib/zip`, `curl`, `pdo_mysql`. Opsional: `intl`, `gmagick`/`imagick`.

---

## 1. Update sistem & install paket dasar

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl unzip git software-properties-common
```

## 2. Install PHP 8.3 (atau versi 8.1–8.5) + ekstensi

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
  php8.3-zip php8.3-gd php8.3-curl php8.3-mysql php8.3-intl php8.3-bcmath

php -v
```

Cek `php-fpm` aktif:

```bash
sudo systemctl enable --now php8.3-fpm
sudo systemctl status php8.3-fpm
```

## 3. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## 4. Install & amankan MariaDB

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

> Catatan: `GRANT` sengaja dibatasi ke DML saja (bukan `ALL PRIVILEGES`) — user aplikasi tidak butuh `CREATE`/`DROP`/`ALTER` setelah schema di-load. Migrasi skema dilakukan manual pakai user root/admin terpisah (lihat langkah 6).

Generate password kuat kalau perlu:
```bash
openssl rand -hex 24
```

## 5. Deploy source code

Ambil kode ke server (via `git clone` jika sudah ada remote repo, atau `scp`/`rsync` dari lokal karena project ini belum berupa git repo):

```bash
sudo mkdir -p /var/www/jadwal-lab
sudo chown $USER:$USER /var/www/jadwal-lab
# opsi A: rsync dari mesin lokal (jalankan dari lokal, bukan di VPS)
#   rsync -avz --exclude=vendor --exclude=.env --exclude=storage/uploads/*.xlsx \
#     /Users/feryfadly/Documents/Podman/jadwal-labrme/ <user>@<vps-ip>:/var/www/jadwal-lab/
# opsi B: git clone (jika repo sudah di-push ke GitHub/GitLab)
#   git clone <repo-url> /var/www/jadwal-lab
```

Install dependency PHP (mode production, tanpa dev-deps — di project ini tidak ada `require-dev` jadi flag ini sekadar jaga-jaga):

```bash
cd /var/www/jadwal-lab
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
```

## 6. Load schema database

```bash
mysql -u root -p jadwal_lab < database/schema.sql
mysql -u root -p jadwal_lab < database/migrations/001_generate_export_metadata.sql
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

## 7. Buat file `.env` production

Aplikasi ini **tidak** punya `.env` loader — nilainya harus di-export ke environment PHP-FPM (bukan sekadar file `.env` yang dibaca sendiri oleh app). Buat file konfigurasi env khusus untuk PHP-FPM pool:

```bash
sudo tee /etc/php/8.3/fpm/pool.d/jadwal-lab-env.conf > /dev/null <<'EOF'
env[DB_HOST] = 127.0.0.1
env[DB_PORT] = 3306
env[DB_DATABASE] = jadwal_lab
env[DB_USERNAME] = jadwal_app
env[DB_PASSWORD] = <password-user-jadwal_app>
EOF
```

> Alternatif yang lebih rapi: simpan sebagai file `.env` biasa di root project (mengikuti format `.env.example`), lalu tambahkan baris `env[...] = ${...}` di pool config yang membaca dari situ — namun cara paling sederhana dan pasti berfungsi adalah menaruh `env[...]` langsung di pool `.conf` seperti di atas, karena PHP-FPM sendiri tidak otomatis membaca file `.env`.

Pastikan `clear_env` **tidak** aktif atau di-set `no` agar `env[]` di atas benar-benar diteruskan ke proses PHP — tambahkan/edit di pool config yang sama:

```ini
clear_env = no
```

Restart PHP-FPM setelah perubahan:

```bash
sudo systemctl restart php8.3-fpm
```

## 8. Set permission direktori

```bash
sudo chown -R www-data:www-data /var/www/jadwal-lab
sudo find /var/www/jadwal-lab -type d -exec chmod 755 {} \;
sudo find /var/www/jadwal-lab -type f -exec chmod 644 {} \;
sudo chmod -R 770 /var/www/jadwal-lab/storage/uploads
```

`storage/uploads/` perlu writable oleh `www-data` (proses import Excel menulis ke sana) tapi tetap **tidak boleh** bisa diakses langsung lewat URL — lihat konfigurasi Nginx di langkah 9.

## 9. Konfigurasi Nginx

Document root **harus** menunjuk ke `public/`, bukan root project — supaya `src/`, `config/`, `database/`, `storage/` tidak bisa diakses langsung dari browser.

```bash
sudo apt install -y nginx
sudo tee /etc/nginx/sites-available/jadwal-lab.conf > /dev/null <<'EOF'
server {
    listen 80;
    server_name <domain-anda.com>;
    root /var/www/jadwal-lab/public;
    index index.php;

    access_log /var/log/nginx/jadwal-lab.access.log;
    error_log  /var/log/nginx/jadwal-lab.error.log;

    # Blokir akses langsung ke folder di luar public/
    location ~ ^/(config|database|src|storage|templates|vendor|bootstrap\.php|composer\.(json|lock)|\.env) {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;
    }

    client_max_body_size 20M;
}
EOF

sudo ln -s /etc/nginx/sites-available/jadwal-lab.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Catatan penting: karena document root sudah `public/`, folder `storage/`, `src/`, `config/`, dsb secara default **sudah tidak bisa diakses** dari luar (di luar `public/`). Blok `location ~ ^/(config|database|...)` di atas sekadar lapis pertahanan tambahan — utamanya berguna kalau suatu saat document root salah diarahkan ke root project.

`client_max_body_size 20M` diperlukan karena fitur import mahasiswa via XLSX bisa berukuran cukup besar; sesuaikan dengan `upload_max_filesize` & `post_max_size` di `php.ini` (langkah 11).

## 10. Pasang HTTPS (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d <domain-anda.com>
```

Certbot otomatis menambahkan blok `listen 443 ssl` dan redirect HTTP→HTTPS. Setelah HTTPS aktif, [src/Auth/Session.php](src/Auth/Session.php) otomatis mendeteksi `$_SERVER['HTTPS']` dan mengaktifkan cookie `secure` — tidak perlu ubah kode.

Pastikan auto-renewal aktif:

```bash
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
```

## 11. Hardening `php.ini` untuk production

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

## 12. Firewall

```bash
sudo apt install -y ufw
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

MariaDB hanya listen di `127.0.0.1` secara default (`bind-address` di `/etc/mysql/mariadb.conf.d/50-server.cnf`) — pastikan **tidak** dibuka ke publik; tidak perlu rule ufw untuk port 3306.

## 13. Verifikasi deployment

```bash
curl -I https://<domain-anda.com>/         # harus redirect ke login.php
curl -I https://<domain-anda.com>/login.php # harus 200
```

Buka browser → login pakai user admin yang dibuat di langkah 6 → cek dashboard, data mahasiswa (termasuk pagination/sort yang baru ditambahkan), generate workstation, dan export PDF/XLSX/DOCX satu per satu.

Cek log kalau ada error:

```bash
sudo tail -f /var/log/nginx/jadwal-lab.error.log
sudo tail -f /var/log/php8.3-fpm-jadwal-lab.log
```

## 14. Backup rutin (rekomendasi, belum ada di kode)

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

1. [ ] PHP 8.1–8.5 + ekstensi wajib terpasang, `php -m` dicek
2. [ ] `composer install --no-dev --optimize-autoloader` sukses tanpa error
3. [ ] MariaDB: DB + user `jadwal_app` dibuat dengan privilege terbatas (bukan root)
4. [ ] `database/schema.sql` + `database/migrations/*.sql` di-load berurutan
5. [ ] User admin pertama dibuat manual via `password_hash()` + INSERT SQL
6. [ ] `env[DB_*]` di pool PHP-FPM terisi, `clear_env = no`, PHP-FPM di-restart
7. [ ] Ownership `www-data:www-data`, `storage/uploads` writable (770) tapi tidak public
8. [ ] Nginx document root = `public/` (bukan root project), blok akses ke folder sensitif
9. [ ] HTTPS aktif via certbot, auto-renew terverifikasi
10. [ ] `display_errors = Off` di php.ini production
11. [ ] Firewall aktif, port DB tidak terbuka ke publik
12. [ ] Login admin, generate, dan ketiga format export (PDF/XLSX/DOCX) dites manual setelah deploy
13. [ ] Cron backup DB + storage/uploads terpasang

## Known issue yang perlu diperhatikan setelah live

- **Export tidak dibatasi role** — [modules/exports/index.php](modules/exports/index.php) hanya `requireAuthentication()`, bukan `requireRole('admin')` seperti generate. User role `petugas` bisa export hasil generate ID berapa pun. Pertimbangkan menambahkan pembatasan role sebelum go-live jika ini jadi concern bisnis.
- Tidak ada rate-limit di level web server (Nginx) untuk endpoint login — rate limit yang ada murni session-based di kode PHP ([src/Auth/RateLimiter.php](src/Auth/RateLimiter.php)) dan reset setiap sesi/cookie baru. Pertimbangkan tambahan `limit_req` di Nginx untuk mitigasi brute-force yang lebih kuat.
