# Panduan Fitur Reset Password — KursusKu Laravel

Paket ini sudah mencakup **2 alur lengkap Reset Password** tanpa error:
1. **Lupa Password (Forgot & Reset Password via Token/Email)** — saat pengguna lupa sandi di halaman `/login`.
2. **Ubah / Reset Password dari Halaman Profil Saya** (`/profil` & `/dashboard`) — saat pengguna sudah login seperti pada tampilan profil Anda.

---

## Langkah Pemasangan di Laragon (Tanpa Error)

1. **Ekstrak File ZIP**
   Ekstrak `kursusku-uas-login-reset-password.zip` ke dalam folder root proyek Laravel Anda (`C:\laragon\www\kursusku-laravel`), lalu pilih **Replace / Timpa** semua file.

2. **Jalankan Migrasi, Seeder & Bersihkan Cache**
   Buka terminal di dalam folder proyek (`C:\laragon\www\kursusku-laravel`) lalu jalankan perintah berikut secara berurutan:
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   php artisan optimize:clear
   ```

3. **Pastikan `@stack('scripts')` Ada di Layout Utama**
   Buka `resources/views/layouts/app.blade.php` dan pastikan sebelum tag penutup `</body>` terdapat baris:
   ```blade
   @stack('scripts')
   ```
   *(Ini agar tombol **Tampilkan / Sembunyikan** password berjalan lancar).*

4. **Jalankan Pengujian Otomatis**
   ```bash
   php artisan test --filter=AuthTest
   ```
   Pastikan seluruh pengujian berwarna hijau (`PASS`).

---

## Cara Menggunakan & Menguji di Browser

### Skenario A: Lupa Password (Saat Logout)
1. Buka `http://kursusku-laravel.test/login`, klik **Lupa password?** (atau buka `http://kursusku-laravel.test/forgot-password`).
2. Masukkan email `admin@kursusku.test`, lalu klik **Kirim Tautan Reset**.
3. Karena pengaturan default Laravel di Laragon menggunakan `MAIL_MAILER=log`, buka file `storage/logs/laravel.log` dan gulir ke baris paling bawah.
4. Salin URL yang berformat `http://kursusku-laravel.test/reset-password/TOKEN...?email=admin%40kursusku.test` dan tempel di browser.
5. Masukkan **Password Baru** (minimal 8 karakter) dan **Ulangi Password Baru**, lalu klik **Simpan Password Baru**.

### Skenario B: Reset / Ubah Password dari Halaman Profil Saya (Saat Login)
1. Login menggunakan `admin@kursusku.test` dan buka `http://kursusku-laravel.test/profil` atau `/dashboard`.
2. Klik tombol **Ubah / Reset Password** di kartu **Profil Saya**.
3. Isi **Password Lama**, **Password Baru**, dan **Ulangi Password Baru**, lalu klik **Simpan Password Baru**.
