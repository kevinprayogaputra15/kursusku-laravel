# UAS Project Akhir — Halaman Login KursusKu

Dibuat tanpa paket tambahan (tanpa Breeze/Jetstream), sehingga setiap baris kode
bisa Anda jelaskan saat demo.

## Ketentuan UAS → fitur

| Ketentuan dosen | Diwujudkan oleh |
| --- | --- |
| User dan password hash | tabel `users`, cast `'hashed'` pada model `User`, `AdminUserSeeder` |
| Fitur hiding password | tombol Tampilkan/Sembunyikan (`auth/_password-field` + `_toggle-script`) |
| Remember password | checkbox "Ingat saya" → `Auth::attempt($credentials, $remember)` |
| Forgot password | `/forgot-password` → email berisi tautan → `/reset-password/{token}` |
| Halaman profil saya / dashboard | `/dashboard` (`DashboardController`, `dashboard.blade.php`) |
| Tombol logout / login | navbar: tamu = "Masuk", sudah login = "Keluar (nama)" |
| Tabel user/admin di phpMyAdmin | tabel `users` (migration bawaan Laravel) |
| Semua fitur navigasi tetap bisa diakses | route Course/Enrollment tidak diubah |

## Cara memakai paket ini (timpa langsung)

1. Hentikan `composer run dev` / `php artisan serve`.
2. Ekstrak `kursusku-uas-login.zip` ke folder proyek (yang berisi file `artisan`) → **Replace**.
3. Di terminal **dalam folder proyek**:

```
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan optimize:clear
php artisan test --filter=AuthTest
```

4. Buka `http://kursusku-laravel.test/login`
   - Email: `admin@kursusku.test`
   - Password: `Admin12345`

Prasyarat: `layouts/app.blade.php` sudah memiliki `@stack('scripts')` (dari paket
pertemuan 13). Tampilan tombol berwarna mengikuti paket Tampilan v2; tanpa itu fiturnya
tetap jalan, hanya gayanya lebih polos.

## Daftar file dalam zip

| File | Status |
| --- | --- |
| `app/Http/Controllers/Auth/LoginController.php` | baru |
| `app/Http/Controllers/Auth/PasswordResetController.php` | baru |
| `app/Http/Controllers/DashboardController.php` | baru |
| `app/Notifications/ResetPasswordNotification.php` | baru (email reset berbahasa Indonesia) |
| `app/Models/User.php` | timpa (tambah `sendPasswordResetNotification`) |
| `routes/web.php` | timpa |
| `database/seeders/AdminUserSeeder.php` | baru |
| `resources/views/auth/*` (login, forgot-password, reset-password, 2 partial) | baru |
| `resources/views/dashboard.blade.php` | baru |
| `resources/views/partials/navbar.blade.php` | timpa |
| `tests/Feature/AuthTest.php` | baru |
| `evidence/uas/*` | baru |

## Langkah pembuatan (urutan yang bisa dijelaskan saat demo)

### Langkah 0 — Persiapan
```
cd C:\laragon\www\kursusku-laravel
php artisan route:list
git add .
git commit -m "chore: checkpoint before uas login"
```
Laragon **Start All**. Pastikan `/courses` dan `/enrollments` masih terbuka.

### Langkah 1 — Tabel `users`
Migration `0001_01_01_000000_create_users_table.php` sudah membuat:

| Tabel | Fungsi |
| --- | --- |
| `users` | akun (name, email unik, password, remember_token) |
| `password_reset_tokens` | token reset password |
| `sessions` | sesi login (SESSION_DRIVER=database) |

```
php artisan migrate:status
```
Jika ada yang Pending → `php artisan migrate`. Cek di phpMyAdmin: database
`kursusku-laravel` → tabel `users`. **Jangan** `migrate:fresh`.

### Langkah 2 — Password di-hash
Di `app/Models/User.php` sudah ada:

```php
protected function casts(): array
{
    return [
        'password' => 'hashed',   // otomatis di-hash (bcrypt) saat disimpan
    ];
}
```
Artinya `User::create(['password' => 'Admin12345'])` tersimpan sebagai `$2y$12$...`.
Login membandingkan input dengan hash memakai `Hash::check`
(dilakukan `Auth::attempt`). Password asli tidak pernah dapat dibaca kembali.

### Langkah 3 — Akun admin (seeder)
`database/seeders/AdminUserSeeder.php` membuat `admin@kursusku.test`.

```
php artisan db:seed --class=AdminUserSeeder
```
Buka phpMyAdmin → `users` → kolom `password` harus berawalan `$2y$`
(screenshot untuk bukti).

### Langkah 4 — Route (`routes/web.php`)

| Method | URL | Nama | Fungsi |
| --- | --- | --- | --- |
| GET | `/login` | `login` | form login |
| POST | `/login` | `login.store` | proses login (throttle 10/menit) |
| POST | `/logout` | `logout` | keluar |
| GET/POST | `/forgot-password` | `password.request` / `password.email` | minta tautan reset |
| GET | `/reset-password/{token}` | `password.reset` | form password baru |
| POST | `/reset-password` | `password.update` | simpan password baru |
| GET | `/dashboard` | `dashboard` | profil/dashboard (wajib login) |

Nama `login` dan `password.reset` wajib: Laravel memakainya untuk mengalihkan tamu ke
halaman login dan untuk membuat tautan di email reset.

### Langkah 5 — Controller login
`app/Http/Controllers/Auth/LoginController.php`

- `create()` menampilkan form (jika sudah login → dashboard).
- `store()` memvalidasi, lalu `Auth::attempt($credentials, $request->boolean('remember'))`.
  Berhasil → `session()->regenerate()` (cegah session fixation) → dashboard.
  Gagal → pesan "Email atau password salah." dan email tetap terisi.
- `destroy()` → `Auth::logout()`, `invalidate()`, `regenerateToken()`.

### Langkah 6 — View login + hiding password
- `resources/views/auth/login.blade.php`
- `auth/_password-field.blade.php`: input password + tombol "Tampilkan".
- `auth/_toggle-script.blade.php`: JavaScript yang mengganti `type="password"` ↔ `type="text"`.

### Langkah 7 — Remember me
Checkbox `name="remember"`. Jika dicentang, Laravel menyimpan `remember_token` di
tabel `users` dan membuat cookie `remember_web_...` sehingga pengguna tetap masuk
walau browser ditutup. Bukti: DevTools → Application → Cookies.

### Langkah 8 — Forgot password
1. `/forgot-password` → `PasswordResetController@email` → `Password::sendResetLink()`.
2. Konfigurasi default proyek: `MAIL_MAILER=log`, sehingga "email" **ditulis ke
   `storage/logs/laravel.log`** (bukan dikirim sungguhan). Buka file itu, cari
   `reset-password/`, salin URL-nya ke browser.
3. Form password baru → `Password::reset()` → password di-hash ulang → kembali ke login.
4. Email berbahasa Indonesia dibuat oleh `app/Notifications/ResetPasswordNotification.php`
   (dipanggil dari `User::sendPasswordResetNotification()`). Tautan hanya berlaku 60 menit dan
   sekali pakai; memakainya lagi menampilkan "Tautan reset tidak valid atau sudah kedaluwarsa."

Ingin email sungguhan? Isi `.env` (contoh Mailtrap/SMTP lain), lalu
`php artisan config:clear`:

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=isi-dari-mailtrap
MAIL_PASSWORD=isi-dari-mailtrap
MAIL_FROM_ADDRESS="admin@kursusku.test"
```
Jangan commit `.env` ke GitHub (sudah di `.gitignore`).

### Langkah 9 — Dashboard / profil saya
`DashboardController@index` (route memakai middleware `auth`): menampilkan nama, email,
tanggal akun, ringkasan data, dan 5 pendaftaran terbaru. Tamu yang membuka `/dashboard`
otomatis diarahkan ke `/login`.

### Langkah 10 — Tombol login/logout di navbar
`resources/views/partials/navbar.blade.php` memakai `@auth ... @else ... @endauth`:
tamu melihat **Masuk**, pengguna login melihat **Dashboard** dan **Keluar (nama)**.
Logout memakai form `POST` + `@csrf`, bukan link biasa.

### Langkah 11 — Navigasi tetap berfungsi
Route Course dan Enrollment tidak diubah, jadi Beranda, Daftar Kursus, Pendaftaran,
dan Laporan Enrollment tetap bisa dibuka.

*Opsional — mewajibkan login untuk mengelola data:* bungkus route dengan
`Route::middleware('auth')->group(function () { ... });`. Jika dilakukan, tes
`CourseCrudTest`, `EnrollmentRelationTest`, dan `CourseSearchTest` perlu
`$this->actingAs(User::factory()->create())` agar tetap lulus. Kalau ketentuan dosen
"semua navigasi tetap bisa diakses" dibaca sebagai "tanpa login", biarkan seperti
paket ini.

## Pengujian

Otomatis:
```
php artisan test --filter=AuthTest
php artisan test
```
Manual: isi `evidence/uas/test-matrix.md` dan ambil screenshot. Tes otomatis memakai
SQLite in-memory, sehingga database MySQL aman.

## Upload ke GitHub

```
git status
git add .
git commit -m "feat: uas login (hash, show/hide password, remember me, forgot password, dashboard)"
git push origin main
```
Jika `main` ditolak, cek nama branch dengan `git branch`. Pastikan `.env` tidak ikut
terunggah, lalu buka repo di browser dan cek commit terbaru muncul.

## Troubleshooting

| Gejala | Penyebab / perbaikan |
| --- | --- |
| `Route [login] not defined` | `routes/web.php` belum tertimpa → ekstrak ulang zip |
| Tombol Tampilkan tidak bereaksi | `@stack('scripts')` belum ada di layout; cek `layouts/app.blade.php` |
| 419 Page Expired | form belum punya `@csrf`, atau sesi kadaluarsa → refresh halaman |
| "Email atau password salah" padahal benar | seeder belum dijalankan; jalankan `AdminUserSeeder` |
| `Table 'users' doesn't exist` | `php artisan migrate` |
| Email reset tidak muncul di log padahal sukses | Laravel membatasi 1 permintaan reset per email setiap **60 detik**. Tunggu semenit lalu coba lagi |
| Tautan reset tidak ditemukan | buka `storage/logs/laravel.log`, cari `reset-password` di bagian paling bawah |
| Tautan reset "tidak valid" | token hanya berlaku 60 menit dan sekali pakai; minta tautan baru |
| `Could not open input file: artisan` | terminal bukan di folder proyek → `cd` ke folder yang berisi `artisan` |
| Terlalu banyak percobaan (429) | tunggu 1 menit (throttle login) |

## Checklist akhir

- [ ] Tabel `users` terlihat di phpMyAdmin, password berawalan `$2y$`
- [ ] Halaman login: email, password, tampilkan/sembunyikan, ingat saya, lupa password
- [ ] Login benar → dashboard; login salah → pesan error
- [ ] Cookie remember muncul saat "Ingat saya" dicentang
- [ ] Forgot password sampai password baru berhasil dipakai login
- [ ] Dashboard hanya bisa dibuka setelah login
- [ ] Tombol Masuk/Keluar tampil sesuai status
- [ ] Beranda, Kursus, Pendaftaran, Laporan tetap terbuka
- [ ] `php artisan test` hijau
- [ ] Sudah di-push ke GitHub; video demo disiapkan (`evidence/uas/skrip-video-demo.md`)

## Hasil verifikasi paket ini

Paket ini dijalankan pada salinan proyek Anda (Laravel 13.39, PHP 8.3, database SQLite
sementara, bukan MySQL Laragon Anda):

- `php artisan test`: **73 tes lulus** (termasuk 22 tes `AuthTest`).
- Uji browser nyata (Chromium): login gagal, tampilkan/sembunyikan password, login + cookie
  `remember_web_...`, dashboard, navigasi 4 halaman, search + filter kursus, logout,
  `/dashboard` terkunci setelah logout, lupa password → tautan di log → password baru →
  login dengan password baru, password lama ditolak, tautan reset tidak bisa dipakai dua kali.
- Belum diuji di MySQL Laragon Anda sendiri — ulangi checklist di atas di komputer Anda
  dan ambil screenshot sebagai bukti.

## Jadwal penilaian (dari soal)

| Nilai | Syarat |
| --- | --- |
| 90 | dikumpulkan 26 Nov pukul 16.00, tanpa perbaikan/error/bug |
| 80 | 25 Nov pukul 23.59 |
| 75 | 26 Nov pukul 23.59, **wajib sertakan video demonstrasi** |
| batas akhir | 27 Nov 2026 pukul 23.59, tetap wajib di repo GitHub masing-masing |
