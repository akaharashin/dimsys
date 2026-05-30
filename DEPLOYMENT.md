# Panduan Deploy DIMSYS ke VPS

Panduan langkah-demi-langkah untuk men-deploy **DIMSYS** (Laravel 13) ke VPS
agar bisa diakses multi-device dari semua wilayah, dengan upload video bukti
hingga 100MB.

> Catatan: nilai contoh memakai domain `domain-anda.com`, user `dimsys`,
> path `/var/www/dimsys`. Sesuaikan dengan server Anda.

---

## 1. Requirement Server

| Komponen | Versi / Catatan |
|---|---|
| OS | Ubuntu 22.04 LTS (atau setara) |
| PHP | **8.3** atau lebih baru (`php -v`) |
| Web server | Nginx (disarankan) atau Apache |
| Database | MySQL 8 / MariaDB 10.6+ |
| Composer | 2.x |
| Node.js | 18+ (hanya untuk build aset; bisa build lokal lalu upload `public/build`) |
| Git | untuk clone & update |

### Ekstensi PHP WAJIB
```bash
sudo apt install php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-gd php8.3-zip php8.3-curl php8.3-bcmath php8.3-intl \
  php8.3-fileinfo php8.3-exif
```
Wajib aktif: **gd** (dengan dukungan WebP — untuk resize foto), **zip** & **xml**
(untuk export Excel / maatwebsite-excel), **mbstring**, **fileinfo**, **exif**,
**bcmath**, **pdo_mysql**, **curl**, **openssl**, **tokenizer**.

Cek cepat:
```bash
php -m | grep -iE 'gd|zip|xml|mbstring|fileinfo|exif|bcmath|pdo_mysql'
php -r "var_dump(function_exists('imagecreatefromwebp'));" # harus true
```

> FFmpeg TIDAK diperlukan — DIMSYS menyimpan video apa adanya tanpa konversi.

---

## 2. Ambil Project & Build

```bash
cd /var/www
git clone <URL-REPO> dimsys
cd dimsys

# Dependency PHP (production)
composer install --no-dev --optimize-autoloader

# Build aset frontend (CSS/JS Tailwind)
npm install
npm run build
```
> Alternatif: jalankan `npm run build` di komputer lokal, lalu upload folder
> `public/build` ke server (agar tidak perlu Node di VPS).

---

## 3. Konfigurasi `.env` Production

```bash
cp .env.example .env
nano .env
```
Nilai yang **WAJIB** diisi/diubah untuk production:

| Variabel | Nilai |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://domain-anda.com` (sesuai domain, pakai HTTPS) |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `LOG_LEVEL` | `error` |
| `DB_DATABASE` | nama database |
| `DB_USERNAME` | **user khusus**, JANGAN `root` |
| `DB_PASSWORD` | password kuat |
| `MEDIA_DISK` | `media` (disk privat bukti) |
| `QUEUE_CONNECTION` | `sync` |
| `QUEUE_CONVERSIONS_BY_DEFAULT` | `false` |
| `MAIL_*` | hanya jika ingin reset password via email (opsional) |

Buat user database khusus (contoh MySQL):
```sql
CREATE DATABASE dimsys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dimsys_user'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_DISINI';
GRANT ALL PRIVILEGES ON dimsys.* TO 'dimsys_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 4. Generate App Key
```bash
php artisan key:generate
```

---

## 5. Migrasi Database

```bash
# Buat semua tabel (struktur terbaru: talangan, enum koreksi, media, dll)
php artisan migrate --force
```

Seed data **master + role + user** (WAJIB agar bisa login):
```bash
php artisan db:seed --force
```
`DatabaseSeeder` hanya mengisi: Wilayah, Supplier, Produk, Outlet, Role/Permission,
User, Rekening. **Aman untuk production.**

> ⚠️ **JANGAN** jalankan seeder data demo Mei di production:
> `DataRealMeiSeeder`, `DistribusiRealMeiSeeder`, `LaporanHarianRealMeiSeeder`,
> `KasHarianRealMeiSeeder`. Itu hanya untuk demo/testing dan tidak idempotent
> (menjalankannya >1× menggandakan data). Seeder tersebut TIDAK dipanggil oleh
> `DatabaseSeeder`, jadi tidak akan ikut jalan kecuali dipanggil manual.

> Setelah login pertama, **segera ganti password** semua user default lewat
> menu **Master User**.

---

## 6. Storage Link (bukti foto/video & aset publik)
```bash
php artisan storage:link
```
Membuat symlink `public/storage` → `storage/app/public`. Di VPS (ada SSH) ini aman.

> Media bukti STO & Pindah Stok disimpan di disk **privat** (`storage/app/media`)
> dan hanya bisa diakses lewat route `media.show` yang sudah dicek auth + wilayah —
> bukan lewat URL publik.

---

## 7. Permission Folder
```bash
sudo chown -R www-data:www-data /var/www/dimsys
sudo find /var/www/dimsys -type f -exec chmod 644 {} \;
sudo find /var/www/dimsys -type d -exec chmod 755 {} \;
# Folder yang harus writable oleh web server:
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## 8. Setting Upload (agar video 100MB lolos)

### a. `php.ini` (PHP-FPM, mis. `/etc/php/8.3/fpm/php.ini`)
```ini
upload_max_filesize = 110M
post_max_size = 120M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
```
Restart: `sudo systemctl restart php8.3-fpm`

### b. Nginx (server block)
```nginx
server {
    client_max_body_size 120M;
    # ... konfigurasi lain ...
}
```
Reload: `sudo nginx -t && sudo systemctl reload nginx`

### c. Apache (jika pakai Apache, di vhost atau .htaccess)
```apache
LimitRequestBody 125829120   # 120MB dalam byte
```

> Validasi aplikasi: video maks **100MB** (`max:102400`), buffer Spatie 110MB.
> Karena itu php.ini diset sedikit di atas (110M/120M) agar pesan validasi
> Bahasa Indonesia yang muncul, bukan error server.

---

## 9. Optimasi Production (cache config/route/view)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Setiap kali `.env` atau route berubah, ulangi perintah ini (atau
> `php artisan optimize`). Untuk clear: `php artisan optimize:clear`.

---

## 10. Web Server Document Root

Arahkan document root ke folder **`public/`** (BUKAN root project).

Contoh Nginx:
```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/dimsys/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    client_max_body_size 120M;
    location ~ /\.(?!well-known).* { deny all; }   # blokir file .env, .git, dll
}
```
Lalu aktifkan **HTTPS** (Let's Encrypt):
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com
```

---

## ✅ Checklist Keamanan Sebelum Go-Live

- [ ] `APP_ENV=production` & `APP_DEBUG=false`
- [ ] `APP_URL` pakai domain + **HTTPS** aktif
- [ ] DB user **bukan root**, password kuat
- [ ] File `.env` **tidak** bisa diakses publik (document root = `public/`, dan rule deny `.env`)
- [ ] Folder `.git` tidak terekspos
- [ ] Password semua user default sudah diganti via Master User
- [ ] `storage/` & `bootstrap/cache` writable; selain itu tidak over-permissive
- [ ] `MEDIA_DISK=media` (bukti tidak publik)
- [ ] Registrasi publik nonaktif (sudah dimatikan di kode — user dibuat via Master User)

---

## 🔄 Cara Update (setelah ada perubahan kode)
```bash
cd /var/www/dimsys
php artisan down                       # mode maintenance (opsional)
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force            # jika ada migrasi baru
php artisan optimize:clear
php artisan config:cache route:cache view:cache
php artisan up
```

## ⏪ Rollback Cepat
```bash
git log --oneline -5                   # lihat commit
git checkout <commit-stabil-sebelumnya>
composer install --no-dev --optimize-autoloader
npm run build
php artisan optimize:clear
# Jika migrasi terakhir bermasalah & bisa di-rollback:
php artisan migrate:rollback --step=1 --force
php artisan config:cache route:cache view:cache
```
> Selalu **backup database** sebelum update besar:
> `mysqldump -u dimsys_user -p dimsys > backup_$(date +%F).sql`
> JANGAN gunakan `migrate:fresh` / `migrate:refresh` di production (menghapus semua data).

---

## Ringkasan Perintah Deploy (urut)
```bash
git clone <repo> dimsys && cd dimsys
composer install --no-dev --optimize-autoloader
npm install && npm run build
cp .env.example .env && nano .env      # isi nilai production
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force            # master + user saja
php artisan storage:link
sudo chown -R www-data:www-data . && sudo chmod -R ug+rwx storage bootstrap/cache
php artisan config:cache route:cache view:cache
# + setting php.ini/nginx upload + HTTPS
```
