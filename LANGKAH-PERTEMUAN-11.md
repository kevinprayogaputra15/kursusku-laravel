# KursusKu — Pertemuan 11 (file siap pakai)

Lanjutan pertemuan 10. Hasil akhir: **CRUD Course lengkap** — detail, Form Request,
edit/update, delete, error dekat field, dan 15 kasus uji.

## Cara memakai (timpa langsung)

1. Tutup `php artisan serve` / `composer run dev` bila sedang jalan.
2. Ekstrak zip ini **langsung ke folder proyek** (`C:\laragon\www\kursusku`) dan pilih
   **Replace / Timpa** untuk file yang sama. Isi zip tidak punya folder pembungkus.
3. Tidak ada migration baru. Jangan jalankan `migrate:fresh`.

## Daftar file

| File | Status | Isi |
| --- | --- | --- |
| `app/Http/Requests/StoreCourseRequest.php` | baru | aturan validasi create, `authorize()` = true |
| `app/Http/Requests/UpdateCourseRequest.php` | baru | aturan update + `Rule::unique()->ignore($course)` |
| `app/Http/Controllers/CourseController.php` | timpa | `show`, `edit`, `update`, `destroy` + `validated()` |
| `resources/views/courses/show.blade.php` | baru | halaman detail |
| `resources/views/courses/edit.blade.php` | baru | form edit, `old('field', $course->field)`, `@method('PUT')` |
| `resources/views/courses/index.blade.php` | timpa | kolom Aksi: Detail, Edit, Hapus (CSRF + DELETE + confirm) |
| `resources/views/courses/create.blade.php` | timpa | error per field (`is-invalid`), `novalidate` |
| `tests/Feature/CourseCrudTest.php` | baru | 15 kasus test matrix + tes tambahan |
| `evidence/week-11/*` | baru | test matrix, log AI, daftar screenshot |

## Langkah 0 — Pastikan titik awal aman

```
cd C:\laragon\www\kursusku
php artisan --version
php artisan route:list --name=courses
```

Harus ada route: index, create, store, show, edit, update, destroy. Pastikan `/courses`
tampil dan form create bisa menyimpan data. Disarankan commit dulu:

```
git add .
git commit -m "checkpoint before course crud advanced"
```

## Langkah 1–2 — Show + route model binding

`CourseController@show(Course $course)` memakai route model binding: Laravel mencari
Course dari ID di URL. ID tidak ada → 404 otomatis. View: `courses/show.blade.php`.

Cek: buka `/courses/1`, lalu `/courses/999999` (harus 404).

## Langkah 3 — Tombol aksi di index

Kolom **Aksi** berisi Detail, Edit, dan Hapus. Hapus memakai
`<form method="POST">` + `@csrf` + `@method('DELETE')` — bukan link biasa.

## Langkah 4–6 — Form Request

- `StoreCourseRequest`: `code` wajib unik terhadap semua Course.
- `UpdateCourseRequest`: `Rule::unique('courses', 'code')->ignore($course)`; code milik
  sendiri boleh disimpan lagi.
- Kedua request: `authorize()` harus `return true;` (jika `false` hasilnya 403).

## Langkah 7 — Controller

`store` dan `update` hanya memakai `$request->validated()`. Aturan validasi tidak
lagi ada di controller.

## Langkah 8 — Edit view

Pola setiap field: `old('field', $course->field)` + `@error('field')` dekat input.
Tanggal diformat `Y-m-d` agar terbaca `<input type="date">`.

Cek: buka `/courses/1/edit`, ubah nama saja, simpan → pindah ke detail + pesan sukses.

## Langkah 9 — Destroy

`destroy` menghapus data lalu redirect ke index dengan flash sukses.
Uji dengan Course dummy `DEL-001`: Cancel → data tetap; OK → data hilang.

## Langkah 10 — Error dekat field

Semua field (`code`, `name`, `description`, `fee`, `quota`, `start_date`, `status`)
memakai `is-invalid` + `invalid-feedback`. Form create dan edit diberi `novalidate`
supaya validasi server tetap bisa diuji (HTML `min`/`max` tidak menutupinya).

## Penyesuaian terhadap proyek Anda

- **Flash message ganda**: layout `layouts/app.blade.php` sudah menampilkan
  `session('success')`. Alert yang sama di `index` dan `show` tidak dipakai agar pesan
  tidak muncul dua kali.
- **Typo di PDF**: `format('Y-md')` pada contoh edit seharusnya `format('Y-m-d')`
  (patah baris saat PDF dibuat). File di paket ini sudah benar.
- **Struktur HTML**: contoh `show.blade.php` di PDF memiliki `</div>` berlebih; versi
  paket sudah dirapikan.
- **Database**: `.env` Anda memakai `DB_DATABASE=kursusku-laravel`. Tidak diubah.

## Pengujian otomatis (opsional, disarankan)

```
php artisan test --filter=CourseCrudTest
```

Memakai SQLite in-memory dari `phpunit.xml`, jadi tidak menyentuh database MySQL Anda.
Pastikan ekstensi `pdo_sqlite` aktif di PHP Laragon. Screenshot hasilnya sebagai
`evidence/week-11/08-php-artisan-test.png`.

## Pengujian manual (isi `evidence/week-11/test-matrix.md`)

Urutan aman: data valid → detail → edit tanpa ubah code → error satu per satu →
boundary quota (0 → 1 → 500 → 501) → status invalid lewat DevTools → delete terakhir.

## Troubleshooting singkat

| Gejala | Perbaikan |
| --- | --- |
| 403 setelah submit | `authorize()` harus `return true;` |
| 419 Page Expired | tambahkan `@csrf` |
| Method Not Allowed saat update/delete | tambahkan `@method('PUT')` / `@method('DELETE')` |
| Code sendiri dianggap duplikat | pakai `Rule::unique(...)->ignore($course)` |
| Tanggal edit kosong | `$course->start_date?->format('Y-m-d')` |
| Data tidak tersimpan | cek `$fillable` di model `Course` |
| Input hilang setelah error | pakai `old('field', $course->field)` |

## Alur yang harus bisa dijelaskan

- **Update**: Browser → `PUT /courses/{course}` → route model binding →
  `UpdateCourseRequest` (validasi) → `$course->update($request->validated())` →
  redirect ke `courses.show` + flash.
- **Delete**: form `POST` + `_method=DELETE` → `destroy` → `$course->delete()` →
  redirect ke index + flash.
