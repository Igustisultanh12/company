<div align="center">

# 🚀 PT Kreatif Sky Abadi — Company Profile & Sistem Internal

**Website company profile interaktif + panel Admin & Agen** untuk PT Kreatif Sky Abadi (Jember, Jawa Timur).
Dibangun dengan **Laravel 13**, **Inertia.js v3**, **Vue 3** & **Tailwind CSS v4**.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue-3.5-42b883?logo=vuedotjs&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia-v3-9553e9)
![Tailwind](https://img.shields.io/badge/Tailwind-v4-38bdf8?logo=tailwindcss&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white)
![aaPanel](https://img.shields.io/badge/Deploy-aaPanel-20a53a)

</div>

---

## 📑 Daftar Isi

1. [Tentang Perusahaan](#-tentang-perusahaan)
2. [Fitur Utama](#-fitur-utama)
3. [Role & Menu](#-role--menu)
4. [Tanda Tangan Elektronik (TTE)](#-tanda-tangan-elektronik-tte)
5. [Keamanan](#-keamanan)
6. [Teknologi](#-teknologi)
7. [Instalasi Lokal](#-instalasi-lokal-development)
8. [Deploy ke aaPanel (Langkah demi Langkah)](#-deploy-ke-aapanel-langkah-demi-langkah)
9. [Update Aplikasi](#-update-aplikasi-di-server)
10. [Setelah Deploy Pertama](#-checklist-setelah-deploy-pertama)
11. [Troubleshooting](#-troubleshooting)
12. [Struktur Project](#-struktur-project)
13. [Testing](#-testing)

---

## 🏢 Tentang Perusahaan

Data awal diambil dari dokumen legalitas perusahaan dan **dapat diubah** di *Admin › Pengaturan*.

| Item | Keterangan |
| --- | --- |
| Nama | **PT Kreatif Sky Abadi** (Perseroan Perorangan) |
| SK Kemenkum | AHU-A012478.AH.01.31.Tahun 2026 (1 September 2026) |
| NIB | 0109260072761 |
| Sertifikat Standar | 01092600727610001 — risiko Menengah Rendah |
| Lingkungan | SPPL — Terbit |
| KBLI | 62199 Pemrograman Komputer Lainnya · 63900 Portal Web & Informasi · 62900 Jasa TI & Komputer Lainnya |
| Alamat | Jl. Argopuro No.77 RT.001 RW.021 Krajan, Mayang, Jember, Jawa Timur 68182 |

> 🔒 **Privasi:** NIK, NPWP pribadi, dan tanggal lahir pemilik **tidak** dimasukkan ke kode/website.
> File PDF legalitas juga **tidak** di-commit ke repository publik — unggah sendiri melalui menu **Arsip Dokumen** (tingkat akses *Rahasia*).

---

## ✨ Fitur Utama

### 🌐 Website Publik (Company Profile)
- **UI/UX colorful & kekinian** (gaya *neo-brutalism* + gradien) dengan **mode gelap/terang**.
- **Animasi interaktif**: elemen muncul saat di-scroll (`v-reveal`), tombol **magnetic** mengikuti kursor, kartu **3D tilt** + kilau, efek **ripple** klik, **kursor kustom**, angka **counter**, **typewriter**, marquee, progress bar scroll, *smooth scrolling* (Lenis), garis proses yang memanjang mengikuti scroll, dan konfeti setelah request terkirim. Semua otomatis nonaktif untuk pengguna `prefers-reduced-motion` & perangkat sentuh.
- Halaman: Beranda, Tentang (visi-misi, tim, **legalitas**), Layanan (+detail), Harga & Paket, Portofolio, Blog, Kontak, FAQ.
- **Request Website** (formulir 4 langkah): jenis website, paket, fitur yang diinginkan, jumlah halaman, warna, referensi, budget, tenggat, domain/hosting, data kontak → mendapat **kode pesanan**.
- **Lacak Pesanan** publik (kode + email) — status, progres %, timeline update, dan **invoice online**.
- **Verifikasi Dokumen TTE** publik: tujuan QR Code + **cek keutuhan berkas PDF (SHA-256)**.
- SEO: meta & Open Graph dirender di server, `sitemap.xml`, `robots.txt`, manifest PWA.
- Tombol **WhatsApp** mengambang & **tautan referral agen** (`/request-website?ref=KODE`).

### 👑 Panel Admin
- **Dashboard**: statistik pesanan, pendapatan, piutang, grafik 6 bulan, distribusi status, TTE menunggu, tenggat tugas, log aktivitas, ringkasan **kepatuhan izin**.
- **Pesanan Website**: pipeline status, PIC, prioritas, harga penawaran, progres, catatan publik/internal, ekspor CSV, pesanan manual.
- **Invoice & Pembayaran**: nomor otomatis, item, diskon, pajak, pembayaran parsial, cetak/PDF, **tautan invoice bertanda tangan** untuk klien.
- **CMS Konten**: Layanan, Paket Harga, Portofolio, Blog (Markdown), Testimoni, FAQ, Tim, Pengumuman — dengan unggah gambar (otomatis dikonversi WebP & dibersihkan metadata).
- **Arsip Dokumen**: kategori, tingkat akses (Publik/Internal/Rahasia), nomor & tanggal dokumen, masa berlaku, tag, hash SHA-256, log unduhan.
- **Legalitas & Kepatuhan**: checklist kewajiban dari Sertifikat Standar & SPPL (laporan Data Industri tiap 6 bulan, sertifikat kompetensi SDM, struktur organisasi, SOP keamanan data) yang terhubung ke arsip.
- **TTE**: pengesahan dengan PIN, tolak/revisi, cabut, pengaturan penandatangan, warna QR, stempel perusahaan.
- **SDM**: Pengguna (Admin/Agen), Absensi (harian & rekap bulanan + ekspor CSV + koreksi), Cuti/Izin/Sakit, Laporan Harian.
- **Tugas (Kanban)** drag & drop + komentar, **Pengumuman**, **Pesan Masuk**, **Log Aktivitas & Keamanan**, **Pengaturan** lengkap.

### 🚀 Panel Agen (Karyawan)
- Dashboard pribadi + **absen masuk/pulang** (jam real-time, mode WFO/WFH/Lapangan, lokasi GPS & radius kantor opsional).
- Tugas Kanban, Laporan Harian (dengan mood 😊), Pesanan yang ditangani (update progres → terlihat klien), klien referral.
- Pengajuan **TTE**, Arsip Dokumen (sesuai hak akses), Cuti/Izin, Pengumuman, Notifikasi, Profil, 2FA.

---

## 👥 Role & Menu

| Menu | Admin | Agen |
| --- | :---: | :---: |
| Dashboard | ✅ (bisnis) | ✅ (pribadi) |
| Pesanan Website | ✅ semua | ✅ yang ditugaskan / referral |
| Invoice, CMS, Pesan Masuk, Pengguna, Pengaturan, Log | ✅ | ❌ |
| Arsip Dokumen | ✅ kelola semua | ✅ lihat Publik & Internal |
| TTE | ✅ sahkan / tolak / cabut | ✅ ajukan & revisi |
| Legalitas & Kepatuhan | ✅ | ❌ |
| Tugas (Kanban) | ✅ semua tim | ✅ milik sendiri |
| Absensi | ✅ rekap & koreksi | ✅ absen & riwayat |
| Cuti/Izin | ✅ setujui / tolak | ✅ ajukan |
| Laporan Harian | ✅ lihat semua | ✅ isi |

---

## ✍️ Tanda Tangan Elektronik (TTE)

Terinspirasi alur di SISINDEN, dengan peningkatan keamanan:

1. **Agen/Admin mengajukan** PDF → sistem membaca jumlah halaman di browser (pdf.js) → pengaju **menyeret & mengubah ukuran kotak QR** di halaman mana pun (bisa lebih dari satu, mis. paraf tiap halaman).
2. **Admin meninjau** (pratinjau hasil akhir, bisa menyesuaikan posisi) lalu **mengesahkan dengan PIN TTE 6 digit** (di-hash, dibatasi 5 percobaan / 15 menit).
3. Server menempelkan **QR Code vektor** (dibuat lokal — tidak memakai layanan QR pihak ketiga) + keterangan *"Ditandatangani secara elektronik oleh … / jabatan / waktu / kode"* dan **stempel** (opsional).
4. Hash **SHA-256** berkas hasil disimpan. QR mengarah ke `/verifikasi/{kode}`. Siapa pun dapat **mengunggah PDF** untuk memastikan berkas identik (tidak diubah).
5. Dokumen sah **tidak bisa dihapus**, hanya **dicabut** (halaman verifikasi menampilkan status *DICABUT* + alasan).

> PDF versi 1.5+ (object stream terkompresi) otomatis dinormalisasi bila server memiliki `qpdf` atau `ghostscript` (lihat langkah deploy).

---

## 🛡️ Keamanan

- Password hash **bcrypt**, aturan password kuat, **rate limit login** (5×/menit per email+IP) dengan pesan seragam (anti enumerasi).
- **2FA TOTP** (Google Authenticator/Authy) + **kode pemulihan** sekali pakai; opsi **wajib 2FA untuk Admin**.
- **Manajemen sesi**: lihat & akhiri sesi perangkat lain; akun nonaktif otomatis dikeluarkan.
- **Content-Security-Policy berbasis nonce**, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS (HTTPS).
- Proteksi CSRF, cookie sesi terenkripsi, `SameSite`, `Secure`.
- **Anti-spam** formulir publik: honeypot + waktu pengisian minimum + rate limit per IP.
- **Upload aman**: validasi MIME + magic bytes PDF, nama acak, berkas privat di `storage/app/private` dan hanya bisa diakses lewat controller yang memeriksa hak akses; gambar publik di-*encode* ulang (membuang EXIF & payload tersembunyi).
- Pencegahan **CSV/Formula injection** saat ekspor, Markdown di-*escape* (anti XSS), validasi whitelist (ikon, warna, URL Google Maps).
- Rute panel **tidak dibocorkan** ke pengunjung (Ziggy route group `guest`).
- **Audit log** lengkap: login/gagal login, 2FA, PIN salah, perubahan data, unduhan dokumen, TTE.
- Tautan invoice untuk klien memakai **signed URL** dengan masa berlaku.

---

## 🧰 Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+, Inertia Laravel v3, Ziggy |
| Frontend | Vue 3.5 (`<script setup>`), Inertia v3, Tailwind CSS v4, Vite 8, Lucide icons, Chart.js, pdf.js, Lenis |
| PDF & QR | setasign/FPDI + FPDF, chillerlan/php-qrcode |
| 2FA | pragmarx/google2fa |
| Database | MySQL 8 / MariaDB 10.6+ (SQLite untuk development & test) |

---

## 💻 Instalasi Lokal (Development)

Prasyarat: PHP 8.3+ (ext: `gd`, `mbstring`, `intl`, `zip`, `fileinfo`, `pdo_mysql`/`pdo_sqlite`), Composer 2, Node.js 20/22.

```bash
git clone https://github.com/Igustisultanh12/company.git
cd company
composer install
cp .env.example .env
# untuk lokal: APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost:8000,
#              DB_CONNECTION=sqlite, SESSION_SECURE_COOKIE=false, SEED_DEMO_AGENT=true
php artisan key:generate
touch database/database.sqlite      # bila memakai SQLite
php artisan migrate --seed          # password admin (& agen demo) tampil di terminal
php artisan storage:link
npm install
npm run dev                         # jalankan bersamaan dengan: php artisan serve
```

Buka `http://localhost:8000` — login tim di `/masuk`.

---

## ☁️ Deploy ke aaPanel (Langkah demi Langkah)

### 1. Siapkan software di aaPanel
1. **App Store** → install **Nginx**, **MySQL 8** (atau MariaDB 10.6+), **PHP 8.3** (atau lebih baru).
2. **PHP 8.3 › Install extensions**: `fileinfo`, `opcache`, `intl`, `exif` (pastikan `gd`, `mbstring`, `zip`, `pdo_mysql` aktif).
3. **PHP 8.3 › Disabled functions** → **hapus** dari daftar: `putenv`, `proc_open`, `proc_get_status`, `symlink`.
   Hapus juga `exec` bila ingin dukungan PDF 1.5+ untuk TTE (normalisasi via qpdf/ghostscript).
4. **PHP 8.3 › Configuration**: `upload_max_filesize = 64M`, `post_max_size = 64M`, `memory_limit = 256M`, `max_execution_time = 120`, `expose_php = Off`.
5. **App Store** → install **Node.js version manager** (pilih Node 22 LTS) agar perintah `npm` tersedia di terminal.
6. Composer: jalankan di **Terminal** aaPanel (jika belum ada):
   ```bash
   curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
   ```
7. (Disarankan untuk TTE) install alat PDF:
   ```bash
   apt install -y qpdf ghostscript      # Debian/Ubuntu
   # yum install -y qpdf ghostscript    # CentOS/Alma/Rocky
   ```

### 2. Buat database & website
1. **Databases › Add database** → catat nama, user, password.
2. **Website › Add site** → isi domain, PHP 8.3, **tanpa** membuat database baru (sudah dibuat).

### 3. Ambil kode dari GitHub
```bash
cd /www/wwwroot/domainanda.com
chattr -i .user.ini 2>/dev/null; rm -f index.html 404.html .htaccess .user.ini
git clone https://github.com/Igustisultanh12/company.git .
```
> `git clone` membutuhkan folder kosong, karena itu file bawaan aaPanel dihapus dulu (`.user.ini` dikunci aaPanel sehingga perlu `chattr -i`). aaPanel akan membuat ulang `.user.ini` saat Running directory diatur — itu normal.

### 4. Atur website di aaPanel
1. **Website › (domain) › Site directory** → **Running directory: `/public`** → Save.
   *(Biarkan "Anti-XSS attack (open_basedir)" aktif — sudah mencakup folder project.)*
2. **URL rewrite** → pilih template **`laravel5`**, atau isi manual:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
3. **Config** (Nginx) — tambahan keamanan & cache aset (opsional, di dalam blok `server`):
   ```nginx
   location ~ /\.(?!well-known).* { deny all; }
   location ~* ^/build/.*\.(js|css|woff2?|svg|png|webp)$ { expires 1y; add_header Cache-Control "public, immutable"; access_log off; }
   client_max_body_size 64m;
   ```

### 5. Konfigurasi & install aplikasi
```bash
cd /www/wwwroot/domainanda.com
cp .env.example .env
nano .env    # isi APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD, MAIL_*, SEED_ADMIN_EMAIL

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed --force      # ⚠️ catat password ADMIN yang tampil
php artisan storage:link

npm install
npm run build

php artisan optimize
chown -R www:www /www/wwwroot/domainanda.com
chmod -R 775 storage bootstrap/cache
```

### 6. SSL / HTTPS
**Website › (domain) › SSL › Let's Encrypt** → terbitkan sertifikat → aktifkan **Force HTTPS**.
Pastikan `.env` berisi `APP_URL=https://...` dan `SESSION_SECURE_COOKIE=true`.

> Belum memakai HTTPS? Sementara set `SESSION_SECURE_COOKIE=false`, kalau tidak login akan gagal (error 419).

### 7. (Opsional) Cron
Aplikasi tidak membutuhkan queue worker. Bila ingin menambahkan tugas terjadwal di masa depan, buat **Cron › Shell script** tiap menit:
```bash
cd /www/wwwroot/domainanda.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Update Aplikasi di Server

Setiap ada perubahan di branch `main`:
```bash
cd /www/wwwroot/domainanda.com
bash deploy.sh
```
`deploy.sh` menjalankan: maintenance mode → `git pull` → `composer install` → `npm install/ci` & `npm run build` → `migrate` → `optimize` → perbaikan izin folder → online kembali.

Bisa juga memakai fitur **Git deployment / WebHook** aaPanel yang menjalankan `bash deploy.sh` setelah pull.

---

## ✅ Checklist Setelah Deploy Pertama

1. Login di `https://domainanda.com/masuk` dengan akun admin dari seeder (atau buat dengan `php artisan admin:create email@domain.com`).
2. **Profil › Keamanan**: ganti password & **aktifkan 2FA**.
3. **Pengaturan TTE**: buat **PIN TTE**, cek nama/jabatan penandatangan, unggah **stempel PNG transparan** (opsional).
4. **Pengaturan**: isi **WhatsApp**, telepon, sosial media, URL embed Google Maps, **rekening bank invoice**, statistik beranda.
5. **Konten**: sesuaikan **harga paket** (harga awal hanya contoh), layanan, FAQ, tim, portofolio. Portofolio *SISINDEN* sengaja disimpan sebagai **draf** — aktifkan bila informasi klien boleh dipublikasikan.
6. **Arsip Dokumen**: unggah SK AHU, Sertifikat Standar/NIB & SPPL ke kategori *Legalitas Perusahaan* dengan akses **Rahasia**, lalu tandai kewajiban di menu **Legalitas & Kepatuhan**.
7. **Pengguna**: buat akun **Agen** untuk karyawan (kredensial ditampilkan sekali untuk dibagikan).
8. Pastikan `APP_DEBUG=false` (cek di *Pengaturan › Sistem*).

---

## 🧯 Troubleshooting

| Gejala | Solusi |
| --- | --- |
| **500 Server Error** | `chmod -R 775 storage bootstrap/cache && chown -R www:www .` · cek `storage/logs/laravel-*.log` |
| Halaman putih / `Vite manifest not found` | Jalankan `npm install && npm run build` |
| **419 Page Expired** saat login | `APP_URL` harus sama persis dengan domain (http/https); bila belum HTTPS set `SESSION_SECURE_COOKIE=false`; jalankan `php artisan optimize:clear` |
| `putenv() / proc_open() has been disabled` | Hapus fungsi tersebut dari *Disabled functions* PHP di aaPanel |
| `storage:link` gagal | Hapus `symlink` dari *Disabled functions* lalu ulangi |
| Klik menu/link error `HttpNetworkError` / `violates Content Security Policy` (pakai Cloudflare Tunnel/proxy) | Pastikan sudah `git pull` versi terbaru (proxy dipercaya otomatis), set `APP_URL=https://domain-anda`, lalu `php artisan optimize:clear && php artisan optimize` |
| Gambar CMS tidak muncul | Jalankan `php artisan storage:link`; pastikan `APP_URL` benar |
| TTE: *"PDF menggunakan kompresi versi baru"* | Install `qpdf`/`ghostscript` & aktifkan `exec`, atau simpan ulang PDF sebagai PDF 1.4 |
| Email reset password tidak terkirim | Isi `MAIL_*` (Gmail: gunakan *App Password*) |
| Lupa password admin | `php artisan admin:create email-admin@domain.com` (mereset password) |
| Perubahan `.env` tidak terbaca | `php artisan optimize:clear && php artisan optimize` |

---

## 🗂️ Struktur Project

```
app/
├── Http/Controllers/
│   ├── Site/      # website publik (halaman, request website, lacak, verifikasi TTE, sitemap)
│   ├── Auth/      # login, 2FA, reset password
│   ├── Panel/     # modul bersama: profil, 2FA, notifikasi, tugas, arsip, TTE
│   ├── Admin/     # dashboard, pesanan, invoice, CMS, pengguna, absensi, cuti, legalitas, pengaturan
│   └── Agent/     # dashboard, absensi, cuti, laporan harian, pesanan
├── Http/Middleware/  # SecurityHeaders (CSP), EnsureRole, EnsureAccountActive, EnsureTwoFactorForAdmins
├── Services/TteService.php   # penempelan QR TTE ke PDF (FPDI) + hash
└── Support/       # Catalog, CmsRegistry, Uploads, TwoFactor, SpamGuard, Markdown, InvoiceView
config/company.php # data awal perusahaan & daftar kewajiban perizinan
resources/js/
├── Pages/         # Site/*, Auth/*, Admin/*, Agent/*, Panel/*
├── Layouts/       # SiteLayout, PanelLayout, AuthLayout
├── Components/    # ui/*, site/*, panel/* (PdfPlacer, Kanban, grafik…)
├── directives/    # v-reveal, v-magnetic, v-tilt, v-counter, v-ripple
└── composables/   # toast, konfirmasi, tema, filter, flash, smooth scroll
deploy.sh          # skrip update untuk aaPanel
```

---

## 🧪 Testing

```bash
php artisan test
```
Mencakup: halaman publik & SEO, header keamanan/CSP, formulir kontak & anti-spam, alur request website + lacak + referral, login/rate limit/2FA/kode pemulihan, hak akses role, CMS + upload gambar, pesanan & invoice + pembayaran, tautan invoice bertanda tangan, manajemen pengguna, pengaturan, **alur TTE lengkap** (ajukan → PIN → sahkan → verifikasi → cek berkas → cabut), kontrol akses arsip, absensi + radius GPS, cuti, kanban & komentar, laporan harian, notifikasi.

Workflow GitHub Actions (`.github/workflows/ci.yml`) menjalankan tes & build frontend setiap push ke `main`.

---

<div align="center">

© 2026 **PT Kreatif Sky Abadi** · Jember, Jawa Timur · Dibuat dengan ♥

</div>
