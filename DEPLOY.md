# Deploy SIWAPAS

Panduan uji coba di **Railway + Supabase**, lalu garis besar pindah ke **Hostinger + MySQL**.

> **Penting: database dipakai bersama.** Railway, laptop developer, dan tim memakai database Supabase
> yang **sama**. Semua yang dilakukan di Railway (pesanan, edit konten, migrasi) langsung terlihat di
> lokal, dan sebaliknya. Ikuti aturan "Shared database" di `CLAUDE.md`: migrasi, seeder, dan perintah
> yang menghapus data hanya dijalankan setelah disetujui.

---

## 1. Gambaran arsitektur uji coba

| Bagian | Pilihan | Catatan |
|---|---|---|
| Build & runtime | `Dockerfile` (FrankenPHP, PHP 8.4) | Satu container: build aset, lalu menyajikan app di `$PORT`. |
| Database | Supabase PostgreSQL, **Session pooler** (IPv4), `sslmode=require` | Lihat bagian 3. |
| File | Supabase Storage (S3): bucket **publik** `media`, bucket **privat** `payment-proofs` | Bisa diganti lewat `.env`. |
| Scheduler & queue | Di container yang sama (`schedule:work`) | Paling hemat; lihat bagian 6. |
| SSR Inertia | **Mati** (`INERTIA_SSR_ENABLED=false`) | Lihat bagian 5. |

File yang dipakai Railway: `Dockerfile`, `docker/start.sh`, `docker/php.ini`, `.dockerignore`,
`railway.json` (healthcheck `/up`).

Urutan di container saat start (`docker/start.sh`):
1. `php artisan migrate --force`, **hanya jika** `RUN_MIGRATIONS=true` (default mati).
2. `php artisan optimize` dan `php artisan filament:optimize`.
3. `php artisan schedule:work` di latar belakang, jika `RUN_SCHEDULER` tidak `false`.
4. Server SSR, hanya jika `INERTIA_SSR_ENABLED=true`.
5. `frankenphp php-server --root public --listen :$PORT`.

Di tahap build image: `composer install --no-dev`, `npm ci`, dan `npm run build:ssr` (aset client
dan bundle SSR). Ekstensi PHP yang dipasang: `pdo_pgsql`, `pgsql`, `pdo_mysql`, `gd`, `exif`, `intl`,
`zip`, `bcmath`, `opcache`, `pcntl` (`mbstring`, `fileinfo`, `openssl` sudah bawaan image).

---

## 2. Langkah deploy Railway + Supabase

### 2.1 Siapkan Supabase
1. **Connection string**: Supabase Dashboard → *Connect* → **Session pooler**. Salin URI berbentuk
   `postgresql://postgres.<project-ref>:<password>@aws-0-<region>.pooler.supabase.com:5432/postgres`.
2. **Extension** `btree_gist` sudah aktif (dipakai constraint anti double booking). Jika di project
   baru gagal dibuat oleh migrasi: *Database → Extensions → btree_gist → Enable* (schema `public`).
3. **Storage**: *Storage → New bucket*
   - `media`, centang **Public bucket** (foto destinasi, poster, galeri, gambar beranda).
   - `payment-proofs`, **jangan** dicentang publik (bukti transfer).
4. **S3 access key**: *Project Settings → Storage → S3 Connection*. Catat **Endpoint**, **Region**,
   lalu *New access key* untuk **Access key ID** dan **Secret access key**.

### 2.2 Buat service di Railway
1. *New Project → Deploy from GitHub repo* → pilih repo ini (branch `main`).
2. Railway membaca `railway.json` dan membangun dari `Dockerfile`. Tidak perlu start command
   tambahan.
3. Isi variabel di tab **Variables** (bagian 2.3). Untuk `APP_KEY`, buat di lokal:
   `php artisan key:generate --show`, lalu salin hasilnya.
4. *Settings → Networking → Generate Domain*, lalu isi `APP_URL` dengan domain itu (`https://...`).
5. Deploy. Healthcheck memakai `/up`.
6. Admin pertama: lihat bagian 4. Karena database dipakai bersama, akun admin yang sudah ada di
   lokal juga bisa langsung login di Railway.

### 2.3 Environment variable

Isi semuanya di Railway (tanpa tanda kutip kecuali disebutkan). **Jangan** menaruh nilai rahasia di
repo.

**Aplikasi**

| Variabel | Nilai | Wajib |
|---|---|---|
| `APP_NAME` | `SIWAPAS` | ya |
| `APP_ENV` | `production` | ya |
| `APP_DEBUG` | `false` | ya |
| `APP_KEY` | hasil `php artisan key:generate --show` | ya |
| `APP_URL` | `https://<domain-railway>` | ya |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `id` / `en` | ya |
| `LOG_CHANNEL` | `stderr` (log tampil di Railway) | ya |
| `LOG_LEVEL` | `warning` | disarankan |
| `TRUSTED_PROXIES` | `*` (Railway berada di belakang proxy) | ya |

**Database**

| Variabel | Nilai | Wajib |
|---|---|---|
| `DB_CONNECTION` | `pgsql` | ya |
| `DB_URL` | URI Session pooler (bagian 2.1) | ya |
| `DB_SSLMODE` | `require` | ya |

**Sesi, cache, antrean**

| Variabel | Nilai | Wajib |
|---|---|---|
| `SESSION_DRIVER` | `database` | ya |
| `SESSION_SECURE_COOKIE` | `true` | ya |
| `CACHE_STORE` | `database` | ya |
| `QUEUE_CONNECTION` | `database` | ya |
| `QUEUE_VIA_SCHEDULER` | `true` | ya (bagian 6) |
| `RUN_SCHEDULER` | `true` | default `true` |
| `RUN_MIGRATIONS` | `false` | default `false` (bagian 7) |

**Penyimpanan file**

| Variabel | Nilai | Wajib |
|---|---|---|
| `FILESYSTEM_DISK` | `local` (unggahan sementara Filament) | ya |
| `MEDIA_DISK` | `supabase_public` | ya |
| `PAYMENT_PROOF_DISK` | `supabase_private` | ya |
| `SUPABASE_S3_KEY` | 84177075a83828faf49d393a31a50ea9 | ya |
| `SUPABASE_S3_SECRET` | ddadd0b3a2df723058f4013465c2e155ecff7b050838965c2334ddd6a89beb52 | ya |
| `SUPABASE_S3_REGION` | ap-northeast-1, mis. `ap-northeast-1` | ya |
| `SUPABASE_S3_ENDPOINT` | `https://ktrbzjseaqawfxwlmxjw.storage.supabase.co/storage/v1/s3` | ya |
| `SUPABASE_PUBLIC_BUCKET` | `media` | ya |
| `SUPABASE_PRIVATE_BUCKET` | `payment-proofs` | ya |
| `SUPABASE_PUBLIC_URL` | `https://<project-ref>.supabase.co/storage/v1/object/public/media` | ya |

**SSR dan akun**

| Variabel | Nilai | Wajib |
|---|---|---|
| `INERTIA_SSR_ENABLED` | `false` | ya (bagian 5) |
| `SIWAPAS_ALLOW_REGISTRATION` | `false` | disarankan (bagian 8) |
| `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` | data admin pertama | hanya saat membuat admin, lalu hapus |
| `SEED_SAMPLE_DATA` | jangan diisi | **jangan** diisi |

**Kontak (cadangan; nilai utama diatur di Filament → Pengaturan Situs)**

| Variabel | Keterangan |
|---|---|
| `SIWAPAS_CONTACT_WHATSAPP` | nomor WhatsApp pengelola, format `08xxxxxxxxxx`; dipakai jika kolom di Pengaturan Situs kosong |
| `SIWAPAS_CONTACT_PHONE`, `SIWAPAS_CONTACT_EMAIL`, `SIWAPAS_CONTACT_ADDRESS`, `SIWAPAS_CONTACT_HOURS`, `SIWAPAS_MAPS_URL`, `SIWAPAS_INSTAGRAM_URL`, `SIWAPAS_FACEBOOK_URL`, `SIWAPAS_YOUTUBE_URL`, `SIWAPAS_HERO_VIDEO_URL` | opsional |

**Email**: belum ada layanan email. Biarkan `MAIL_MAILER=log`; reset password lewat email tidak
akan terkirim sampai SMTP diisi.

---

## 3. Kenapa Session pooler, bukan direct connection atau Transaction mode

- **Direct connection** (`db.<ref>.supabase.co:5432`) hanya punya alamat **IPv6** (kecuali membeli
  add-on IPv4). Koneksi keluar dari Railway ke internet memakai IPv4, jadi direct connection gagal.
  **Session pooler** (`aws-0-<region>.pooler.supabase.com:5432`) bisa diakses lewat IPv4.
- **Transaction mode** (port `6543`) mengembalikan koneksi ke pool setelah setiap transaksi. Laravel
  (PDO PostgreSQL) memakai *prepared statement* bernama dan pengaturan per sesi. Di mode ini statement
  bisa hilang atau bentrok antar-permintaan (`prepared statement ... does not exist/already exists`),
  dan state sesi tidak terjamin. Session mode memberi satu koneksi utuh per permintaan, sesuai cara
  kerja Laravel.
- `sslmode=require` memastikan koneksi ke pooler terenkripsi (`DB_SSLMODE=require`).
- Batas koneksi Session pooler di paket gratis kecil (sekitar 15). FrankenPHP memakai sedikit thread,
  jadi aman untuk uji coba. Jika muncul error "too many connections", kurangi replika/thread atau
  naikkan paket Supabase.

---

## 4. Membuat akun admin pertama

Database sama dengan lokal, jadi admin yang sudah ada langsung bisa dipakai. Untuk membuat atau
menjadikan admin baru tanpa menjalankan seeder apa pun:

```bash
# dari laptop (memakai .env lokal yang menunjuk ke Supabase yang sama)
php artisan siwapas:create-admin admin@contoh.id --name="Pengelola Desa"
# password ditanyakan secara tersembunyi; aturan password production: min. 12 karakter,
# huruf besar-kecil, angka, simbol, dan tidak pernah bocor.
```

Lewat Railway CLI, perintahnya sama: `railway run php artisan siwapas:create-admin ...`. Jika
`ADMIN_EMAIL` dan `ADMIN_PASSWORD` diisi, perintah bisa dijalankan tanpa argumen. Hapus
`ADMIN_PASSWORD` dari variabel setelahnya. Login admin lewat `/admin`.

---

## 5. SSR Inertia: dimatikan untuk uji coba

| | SSR mati (default) | SSR nyala |
|---|---|---|
| Proses | hanya PHP | PHP + Node (`inertia:start-ssr`) di container yang sama |
| Memori/biaya | rendah | ± +150–250 MB RAM |
| Risiko | sedikit | proses Node bisa mati; Inertia otomatis kembali ke render di browser |
| SEO | Google tetap merender JavaScript; judul dan meta per halaman dipasang di browser | HTML lengkap langsung dari server |
| Preview link (WhatsApp/Facebook) | memakai **meta default situs** dari server (judul, deskripsi, gambar beranda) | meta per halaman |

Meta default ada di `resources/views/app.blade.php` (`<title>`, `description`, `og:*`, `twitter:card`)
dan diganti otomatis oleh `<Head>` tiap halaman di browser. Untuk menyalakan SSR nanti: set
`INERTIA_SSR_ENABLED=true`. Bundle SSR sudah ikut dibangun, dan `start.sh` akan menjalankan server SSR.

---

## 6. Scheduler dan queue

Yang dijadwalkan: `orders:expire` (tiap 5 menit, melepas slot pesanan yang lewat batas DP). Antrean
saat ini hampir tidak terpakai, tetapi tetap disiapkan.

| Opsi | Cara | Biaya |
|---|---|---|
| **A (default, paling hemat)** | `schedule:work` berjalan di container web (`RUN_SCHEDULER=true`), antrean dikuras scheduler tiap menit (`QUEUE_VIA_SCHEDULER=true`) | tidak ada service tambahan |
| B | Service Railway kedua dengan *Cron Schedule* `*/5 * * * *` dan start command `php artisan schedule:run`; set `RUN_SCHEDULER=false` di service web | tagihan kecil per menit jalan |
| C | Service worker `php artisan queue:work` yang selalu hidup + opsi A atau B untuk scheduler | paling mahal; perlu jika nanti ada notifikasi berat |

Opsi A cukup untuk uji coba. Kelemahannya: jika proses `schedule:work` berhenti, ia baru hidup lagi
saat container restart. `orders:expire` aman dijalankan berulang, dan `withoutOverlapping()` memakai
lock di cache database bersama, jadi tidak dobel walau ada lebih dari satu replika.

---

## 7. Migrasi database

`RUN_MIGRATIONS` **mati** secara default, karena migrasi di Railway langsung mengubah database
bersama. Alur yang disarankan saat ada migrasi baru:

1. `php artisan migrate --pretend` (dari laptop atau `railway run`), lalu tunjukkan SQL-nya.
2. Setelah disetujui: `php artisan migrate --force`, cukup **sekali** (DB-nya sama).
3. Deploy kode yang membutuhkan kolom baru **setelah** migrasi jalan.

Jika sengaja ingin migrasi otomatis saat deploy, set `RUN_MIGRATIONS=true` untuk deploy itu saja.

---

## 8. Keamanan production (checklist)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` terisi (unik, tidak dari lokal orang lain).
- [ ] `APP_URL` memakai `https`, `TRUSTED_PROXIES=*`, `SESSION_SECURE_COOKIE=true`. Tanpa
      `TRUSTED_PROXIES`, URL dan link bertanda tangan (bukti transfer) akan memakai `http`.
- [ ] **Pendaftaran publik mati**: `SIWAPAS_ALLOW_REGISTRATION=false`. Pengunjung memesan tanpa akun;
      rute `/register` hilang dan tautan "Daftar" di halaman login disembunyikan. Akun admin dibuat
      dengan `siwapas:create-admin`.
- [ ] Hanya role `admin` yang bisa masuk `/admin` (`User::canAccessPanel`).
- [ ] Rate limit: endpoint publik yang menulis data (reservasi, pendaftaran acara, kontak, unggah
      bukti) 10 permintaan/menit/IP; login dan 2FA dibatasi Fortify. `TRUSTED_PROXIES` harus benar
      agar IP pengunjung asli yang dihitung.
- [ ] Unggahan divalidasi: bukti transfer JPG/PNG/PDF maks. 5 MB; foto di Filament hanya gambar,
      maks. 5 MB. `docker/php.ini`: `upload_max_filesize=12M`.
- [ ] Bukti transfer di bucket **privat**, hanya bisa dibuka admin lewat link bertanda tangan 5 menit.
- [ ] Seeder data contoh **tidak bisa** jalan di production: `SampleDataSeeder` menolak jika
      `APP_ENV` bukan `local` atau database adalah Supabase. Jangan isi `SEED_SAMPLE_DATA`.
- [ ] `.env` tidak ikut image (`.dockerignore`) dan tidak di-commit.

---

## 9. Memindahkan file yang sudah ada ke Supabase Storage

File yang diunggah selama pengembangan ada di **disk lokal laptop yang mengunggahnya**
(`storage/app/public` untuk media, `storage/app/private` untuk bukti transfer). Railway tidak bisa
melihat file itu. Saat dokumen ini dibuat, Supabase mencatat 10 media (2 destinasi, 1 galeri,
4 poster, 1 gambar hero, 2 event) dan 2 bukti transfer, semuanya di disk lokal.

`public/images/landing/about.jpg` ada di repo, jadi ikut ter-deploy dan tidak perlu dipindah.

Di laptop yang menyimpan file tersebut, setelah variabel `SUPABASE_*` diisi di `.env` lokal:

```bash
php artisan siwapas:move-media --to=supabase_public       # dry run: daftar media + "ada di mesin ini?"
php artisan siwapas:move-proofs --to=supabase_private     # dry run untuk bukti transfer
```

Setelah daftarnya dicek dan **disetujui** (perintah ini mengubah kolom `disk` di database bersama):

```bash
php artisan siwapas:move-media --to=supabase_public --force
php artisan siwapas:move-proofs --to=supabase_private --force
```

- File asli tidak dihapus (cadangan). Baris yang filenya tidak ada di laptop itu dilewati dan
  dilaporkan, jadi jalankan juga di laptop anggota tim lain yang pernah mengunggah.
- Bisa dijalankan berulang; baris yang sudah pindah dilewati.
- Setelah dipindah, set juga `MEDIA_DISK=supabase_public` dan `PAYMENT_PROOF_DISK=supabase_private`
  di `.env` lokal, supaya unggahan baru dari lokal juga masuk bucket.
- Cek: buka beranda di Railway (gambar tampil) dan buka satu bukti transfer di Filament → Pembayaran.

---

## 10. Migrasi ke Hostinger (garis besar)

Target: shared hosting Hostinger, PHP 8.4, **MySQL**, tanpa Node dan tanpa proses yang selalu hidup.

1. **Siapkan Hostinger**
   - PHP 8.4 dengan ekstensi `pdo_mysql`, `gd`, `intl`, `zip`, `bcmath`, `exif`, `fileinfo`,
     `mbstring`.
   - Buat database MySQL dan user-nya.
   - Arahkan domain ke folder **`public`** proyek (atau jadikan `public_html` symlink ke
     `<proyek>/public`). Jangan pernah membuat root proyek bisa diakses publik.
2. **Kode dan aset**
   - Hostinger tidak punya Node: jalankan `npm ci && npm run build` **di laptop**, lalu unggah kode
     beserta `public/build`.
   - Di server: `composer install --no-dev --optimize-autoloader`.
3. **`.env` production**
   - `DB_CONNECTION=mysql` (host/port/nama/user/password MySQL)
   - `MEDIA_DISK=public`, `PAYMENT_PROOF_DISK=local`
   - `INERTIA_SSR_ENABLED=false`
   - `TRUSTED_PROXIES=` kosong (isi hanya jika memakai Cloudflare/proxy)
   - `QUEUE_VIA_SCHEDULER=true`, `SESSION_DRIVER=database`, `CACHE_STORE=database`
   - `SIWAPAS_ALLOW_REGISTRATION=false`, plus semua variabel aplikasi di bagian 2.3
   - Lalu `php artisan key:generate` (jika `APP_KEY` baru) dan `php artisan storage:link`.
4. **Skema database**: `php artisan migrate --force` di MySQL. Semua migrasi portabel. Constraint
   exclusion (000019) otomatis dilewati di MySQL; pencegahan double booking tetap berjalan di level
   aplikasi (transaksi + `lockForUpdate`).
5. **Data dari Supabase**: ekspor per tabel lalu impor ke MySQL, tanpa tabel `migrations`, `cache`,
   `sessions`, dan `jobs`. Gunakan alat transfer data, misalnya
   [pgloader](https://pgloader.io) (PostgreSQL → MySQL) atau fitur *Data Transfer* di DBeaver.
   Perhatikan:
   - kolom JSON (teks terjemahan id/en) harus tetap JSON yang valid;
   - boolean PostgreSQL (`t`/`f`) menjadi `1`/`0`;
   - semua timestamp tetap UTC;
   - setelah impor, samakan nilai `AUTO_INCREMENT` tiap tabel dengan id terbesar.
6. **File**: dengan variabel `SUPABASE_*` masih terisi di `.env` Hostinger, salin file dari bucket
   ke disk lokal lalu perbarui barisnya:
   ```bash
   php artisan siwapas:move-media --to=public --force
   php artisan siwapas:move-proofs --to=local --force
   ```
   Setelah itu variabel `SUPABASE_*` boleh dihapus.
7. **Cron** (hPanel → Advanced → Cron Jobs), tiap menit:
   ```
   * * * * * cd /home/<user>/<proyek> && php artisan schedule:run >> /dev/null 2>&1
   ```
   Ini menjalankan `orders:expire` dan menguras antrean (`QUEUE_VIA_SCHEDULER=true`).
8. **Optimasi**: `php artisan optimize && php artisan filament:optimize` setiap selesai deploy.
9. **Uji** sebelum DNS dipindah: login admin, reservasi + unggah bukti + verifikasi, kalender acara,
   dan dua reservasi bersamaan di tanggal yang sama (yang kedua harus ditolak).
