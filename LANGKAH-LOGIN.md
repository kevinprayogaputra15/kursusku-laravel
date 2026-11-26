# KursusKu — Login + Profil Saya (file siap pakai)

Fitur: login (username/email + password ter-hash), tombol mata (hiding password),
ingat saya, lupa password, halaman Profil Saya setelah login, tombol Masuk/Keluar,
tabel users di phpMyAdmin, avatar inisial. Menu lain tetap bisa diakses.

## Cara memakai
1. Hentikan `php artisan serve` / `composer run dev`.
2. Ekstrak zip **langsung ke folder proyek** (yang berisi file `artisan`), pilih **Replace**.
3. Di folder proyek jalankan:

```
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan optimize:clear
```

4. Buka `http://localhost:8000/login`
   - Username: `admin`  |  Password: `admin12345`
5. **Jangan** pakai `migrate:fresh` (data Course hilang). Tidak perlu `npm run build`.

## Cek di phpMyAdmin
Database `kursusku-laravel` -> tabel `users`: kolom `username`, `email`,
`password` (berisi hash `$2y$...`, bukan teks asli), `role`, `remember_token`.

## Lupa password
`MAIL_MAILER=log` di `.env`, jadi email tidak benar-benar terkirim. Buka
`storage/logs/laravel.log`, cari link `reset-password/...`, salin ke browser.

## Menambah user baru
```
php artisan tinker
>>> App\Models\User::create(['name'=>'Kevin','username'=>'kevin','email'=>'kevin@kursusku.test','password'=>'rahasia123','role'=>'user']);
```

## Daftar file
| File | Status |
| --- | --- |
| `database/migrations/2026_11_26_000001_add_username_and_role_to_users_table.php` | baru |
| `database/seeders/UserSeeder.php` | baru |
| `database/seeders/DatabaseSeeder.php`, `database/factories/UserFactory.php` | timpa |
| `app/Models/User.php` | timpa (username, role, inisial avatar) |
| `app/Http/Controllers/Auth/LoginController.php` | baru |
| `app/Http/Controllers/Auth/PasswordResetController.php` | baru |
| `app/Http/Controllers/ProfileController.php` | baru |
| `routes/web.php`, `bootstrap/app.php` | timpa |
| `resources/views/auth/*` (login, forgot-password, reset-password, _eye, _logo) | baru |
| `resources/views/profile/show.blade.php` | baru |
| `resources/views/components/avatar.blade.php` | baru (logo profil) |
| `resources/views/layouts/app.blade.php`, `partials/navbar.blade.php` | timpa |
| `public/css/auth.css`, `public/js/auth.js` | baru |

Catatan: `/profil` wajib login. Halaman lain sengaja dibiarkan terbuka agar fitur
navigasi tetap bisa diakses. Untuk mengunci, bungkus route dengan `Route::middleware('auth')->group(...)`.
