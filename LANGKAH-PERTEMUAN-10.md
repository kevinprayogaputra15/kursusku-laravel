# KursusKu — Pertemuan 10 (file siap pakai)

Lanjutan proyek pertemuan 9. Yang dikerjakan di sini hanya **Create + Read**
kursus: migration, model, controller, route, view, seeder, dan Ajax availability
yang membaca tabel `courses`.

Update dan delete **belum** dibuat (materi pertemuan berikutnya). Method
`show`, `edit`, `update`, dan `destroy` ada agar resource route tidak error,
tetapi semuanya `abort(404)`.

## Salin file ini ke proyek Laragon

Salin isi paket ke `C:\laragon\www\kursusku` (timpa file yang sama):

| File di paket | Tujuan di proyek |
| --- | --- |
| `app/Models/Course.php` | `$fillable` + cast `start_date` |
| `app/Http/Controllers/CourseController.php` | `index`, `create`, `store` |
| `database/migrations/2026_11_04_112645_create_courses_table.php` | skema tabel `courses` |
| `database/seeders/CourseSeeder.php` | 8 data latihan |
| `resources/views/courses/index.blade.php` | daftar |
| `resources/views/courses/create.blade.php` | form tambah |
| `resources/views/partials/navbar.blade.php` | menu Daftar Kursus |
| `resources/views/enrollments/create.blade.php` | opsi kursus dari database |
| `routes/web.php` | resource route + availability |

Layout `layouts/app.blade.php` pertemuan 9 sudah menampilkan `session('success')`.
View index juga menampilkannya sesuai panduan. Jika pesan sukses muncul dua kali,
hapus salah satu blok alert.

## .env

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kursusku
DB_USERNAME=root
DB_PASSWORD=
```

Panduan memakai database `kursusku`. Jika database yang sudah dibuat bernama
`kursusku-laravel`, jangan diganti — samakan `DB_DATABASE` dengan nama yang ada.

## Perintah (setelah Laragon Start All)

```
cd C:\laragon\www\kursusku
php artisan config:clear
php artisan migrate
php artisan migrate:status
php artisan db:seed --class=CourseSeeder
php artisan route:list --name=courses
```

Jika `migrate` bilang *Nothing to migrate* tetapi tabel `courses` hanya berisi
`id` dan `timestamps` (migration kosong sempat dijalankan) dan tabel itu masih
kosong:

```
php artisan migrate:rollback --step=1
php artisan migrate
```

Jangan pakai `migrate:fresh` kalau database sudah berisi data penting.

Jika `WEB-101` sudah pernah disimpan lewat form, seeder gagal karena `code`
unik. Itu diharapkan. Pakai kode lain di form, atau kosongkan tabel latihan
dulu dengan sadar.

## Cek di browser

1. `/courses` — 8 baris, urutan terbaru di atas (`Course::latest()`).
2. `/courses/create` — simpan satu data baru, kembali ke daftar + pesan sukses.
3. Kode kosong, nama kosong, biaya negatif, kuota 0, dan kode duplikat ditolak.
4. `/pendaftaran` — pilih kursus, sisa kursi muncul tanpa reload.
5. phpMyAdmin: database `kursusku`, tabel `courses`, kolom sesuai blueprint.

## Uji yang diharapkan

| No | Skenario | Hasil |
| --- | --- | --- |
| 1 | Semua field valid | Tersimpan, redirect, pesan sukses |
| 2 | Kode kosong | Ditolak |
| 3 | Kode `WEB-101` kedua kali | Ditolak, unique |
| 4 | Nama kosong | Ditolak |
| 5 | Biaya `-1000` | Ditolak, min 0 |
| 6 | Kuota `0` | Ditolak, min 1 |
| 7 | Status di luar draft/open/closed | Ditolak |
| 8 | Muat ulang `/courses` | Data tetap ada |

## Alur yang harus bisa dijelaskan

Browser → `Route::resource` → `CourseController@store` → `$request->validate`
→ `Course::create` (INSERT) → redirect `courses.index` → `Course::latest()->get()`
(SELECT … ORDER BY created_at DESC) → Blade.
