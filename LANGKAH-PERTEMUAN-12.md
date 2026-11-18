# KursusKu — Pertemuan 12 (file siap pakai)

Lanjutan pertemuan 11. Hasil akhir: **Relational Core v1** — tabel `courses`,
`participants`, `enrollments` saling berelasi (2 foreign key + unique gabungan),
form enrollment, laporan relasional, dan uji cascade delete.

## Cara memakai (timpa langsung)

1. Hentikan `composer run dev` / `php artisan serve` bila sedang jalan.
2. Ekstrak zip ini **langsung ke folder proyek** (`C:\laragon\www\kursusku`), pilih
   **Replace / Timpa**. Zip tidak punya folder pembungkus.
3. Jalankan perintah pada bagian "Langkah 3" dan "Langkah 5" di bawah.
4. **Jangan** memakai `migrate:fresh` — data Course Anda akan hilang.

## Daftar file

| File | Status | Isi |
| --- | --- | --- |
| `database/migrations/2026_11_18_000001_create_participants_table.php` | baru | tabel participants, email unique |
| `database/migrations/2026_11_18_000002_create_enrollments_table.php` | baru | 2 FK `cascadeOnDelete()` + unique(participant_id, course_id) |
| `app/Models/Participant.php` | baru | `enrollments()`, `courses()` |
| `app/Models/Enrollment.php` | baru | `participant()`, `course()` |
| `app/Models/Course.php` | timpa | `$fillable` + cast lama dipertahankan; tambah `enrollments()`, `participants()` |
| `app/Http/Controllers/EnrollmentController.php` | baru | `index`, `create`, `store` |
| `routes/web.php` | timpa | resource `enrollments` (index/create/store) |
| `resources/views/enrollments/create.blade.php` | timpa | form 2 dropdown relasional (Bootstrap) |
| `resources/views/enrollments/index.blade.php` | baru | laporan relasional |
| `resources/views/partials/navbar.blade.php` | timpa | menu "Laporan Enrollment" |
| `database/seeders/ParticipantSeeder.php` | baru | 3 participant dummy |
| `tests/Feature/EnrollmentRelationTest.php` | baru | 8 skenario matrix + cascade + constraint |
| `evidence/week-12/*` | baru | ERD, test matrix, skrip tinker cascade, log AI |

## Langkah 0 — Pastikan titik awal aman

1. Laragon: **Start All** (Apache/Nginx + MySQL aktif).
2. Di terminal VS Code:

```
php artisan --version
php artisan migrate:status
php artisan route:list
```

3. Halaman `/courses` harus masih berjalan dan berisi data. `.env` Anda memakai
   `DB_DATABASE=kursusku-laravel` — jangan diubah.
4. Checkpoint Git:

```
git add .
git commit -m "checkpoint before meeting 12 relations"
```

## Langkah 1 — ERD final

Lihat `evidence/week-12/erd.md` (diagram Mermaid + jawaban checkpoint dosen).
Gambar ulang di draw.io atau tangkap layar sebagai `01-erd-final.png`.

- Course 1 → N Enrollment
- Participant 1 → N Enrollment
- Enrollment memuat `participant_id`, `course_id`, `status`

## Langkah 2 — Migration (sudah disiapkan di zip)

Panduan meminta `make:migration` dua kali. Hasilnya sudah ada di zip:

- `create_participants_table`: `name`, `email` (unique), `phone` (nullable)
- `create_enrollments_table`: `participant_id` + `course_id` memakai
  `constrained()->cascadeOnDelete()`, `status` default `registered`,
  dan `unique(['participant_id', 'course_id'])`

Nama file bertanggal `2026_11_18`, jadi dijalankan **setelah** migration `courses`
(FK butuh tabel `courses` sudah ada).

## Langkah 3 — Jalankan migration dan periksa foreign key

```
php artisan migrate
php artisan migrate:status
```

Yang harus muncul: `create_participants_table` dan `create_enrollments_table` berstatus
**DONE/Ran**. Hanya dua tabel baru yang dibuat; tabel `courses` tidak disentuh.

Periksa di phpMyAdmin/HeidiSQL, tabel `enrollments` → Structure → Relation view:

- `participant_id` → `participants.id`, ON DELETE **CASCADE**
- `course_id` → `courses.id`, ON DELETE **CASCADE**

Atau lewat SQL:

```sql
SHOW CREATE TABLE enrollments;
```

Cari `FOREIGN KEY`, `ON DELETE CASCADE`, dan `UNIQUE KEY` gabungan.
Screenshot: `02-migrate.png`, `03-foreign-key-db.png`.

| Gejala | Solusi |
| --- | --- |
| Cannot add foreign key | Pastikan tabel `courses` ada; cek `migrate:status` |
| Table already exists | Migration pernah sebagian berjalan; cek `migrate:status`, rollback `--step=1` bila perlu |
| Connection refused | Start MySQL di Laragon, cek `.env` |

## Langkah 4 — Model dan relasi

- `Participant`: `enrollments()` (hasMany), `courses()` (belongsToMany lewat `enrollments`)
- `Enrollment`: `participant()` dan `course()` (belongsTo)
- `Course`: tambahan `enrollments()` dan `participants()`. `$fillable` dan cast
  `start_date` yang sudah ada **tidak dihapus**.

Uji sintaks dan relasi:

```
php artisan tinker
>>> new App\Models\Participant;
>>> App\Models\Course::first()?->enrollments;
>>> App\Models\Participant::first()?->enrollments;
```

## Langkah 5 — Data dummy participant

Panduan memakai Tinker. Di zip sudah ada seeder yang hasilnya sama (3 participant,
aman dijalankan berulang):

```
php artisan db:seed --class=ParticipantSeeder
```

Periksa:

```
php artisan tinker
>>> App\Models\Participant::all(['id','name','email']);
>>> App\Models\Course::all(['id','code','name','start_date','status']);
```

Cara manual via Tinker (`Participant::create([...])`) tetap boleh dipakai.

> **Penting:** dropdown course hanya menampilkan course berstatus **`open`**.
> Contoh PDF memakai `active`, tetapi status di proyek ini `draft` / `open` / `closed`.
> Jika dropdown kosong, ubah status satu course menjadi `open` lewat menu Daftar Kursus.

## Langkah 6–7 — Controller, route, dan form

- `EnrollmentController@create`: mengambil participant dan course `open`.
- `EnrollmentController@store`: validasi `exists` + `Rule::unique` pada pasangan,
  lalu `status = registered`.
- `EnrollmentController@index`: `Enrollment::with(['participant', 'course'])`.
- Route: `Route::resource('enrollments', ...)->only(['index','create','store'])`.

**Penyesuaian terhadap proyek Anda (route bentrok):** pertemuan 9 sudah punya route
bernama `enrollments.create` di `/pendaftaran` (form preview tanpa database) dan
`enrollments.preview`. Keduanya digantikan resource baru agar tidak bentrok nama.

| Sebelum | Sesudah |
| --- | --- |
| `/pendaftaran` (form preview) | redirect ke `/enrollments/create` |
| `enrollments.preview` | dihapus |
| `route('enrollments.create')` di navbar/home | tetap jalan, kini menuju `/enrollments/create` |

Ajax sisa kursi pertemuan 9 tetap berfungsi (id `#course_id` dan `#availability`
dipertahankan). Rumusnya kini `remaining = kuota - jumlah enrollment`.

## Langkah 8 — Cegah pendaftaran ganda (dua lapis)

1. Laravel: `Rule::unique('enrollments')->where(participant_id = ...)` dengan pesan
   "Participant ini sudah terdaftar pada course tersebut."
2. Database: `unique(['participant_id', 'course_id'])`.

Kesalahan umum: membuat `participant_id` atau `course_id` masing-masing unique. Itu
membatasi satu peserta hanya satu kursus (atau sebaliknya).

Urutan uji: Alya→Laravel Dasar (berhasil) → ulangi (ditolak) → Alya→PHP Dasar (boleh)
→ Bima→Laravel Dasar (boleh).

## Langkah 9 — Laporan relasional

Buka `/enrollments`: Participant, Course, Start Date (dari `courses.start_date`,
format `d/m/Y`), dan Status. Data gabungan diambil lewat eager loading `with()`.

Flash message sukses ditampilkan oleh layout; view tidak mengulanginya.

## Langkah 10 — Uji end-to-end (8 skenario)

Isi `evidence/week-12/test-matrix.md`. Selain tampilan browser, cek database:

```sql
SELECT id, participant_id, course_id, status
FROM enrollments
ORDER BY id DESC;
```

## Langkah 11 — Uji cascade delete (data dummy saja)

Ikuti `evidence/week-12/cascade-test-tinker.md`:

- **A.** Hapus course dummy → enrollment-nya hilang, participant tetap ada.
- **B.** Hapus participant dummy → enrollment-nya hilang, course tetap ada.

Cascade hanya menghapus **dependency** (enrollment), bukan entity di sisi lain.

> Efek di aplikasi: setelah pertemuan ini, tombol **Hapus** pada Daftar Kursus ikut
> menghapus semua enrollment course tersebut. Jangan menghapus course yang sudah
> berisi pendaftaran nyata.

## Tes otomatis (opsional, disarankan)

```
php artisan test --filter=EnrollmentRelationTest
php artisan test
```

Memakai SQLite in-memory (`phpunit.xml`), sehingga database MySQL Anda aman.
Screenshot hasilnya sebagai `10-php-artisan-test.png`.

## Troubleshooting

| Masalah | Penyebab | Perbaikan |
| --- | --- | --- |
| Class Participant not found | Import salah | `use App\Models\Participant;` |
| Unknown column participant_id | Migration belum jalan | `php artisan migrate` |
| Foreign key constraint fails | ID tidak ada | Pilih dari dropdown; rule `exists` |
| Duplicate entry | Pasangan sudah ada | `Rule::unique` + tampilkan error |
| Attempt to read property name on null | Data yatim / FK tidak benar | Periksa constraint |
| Course tidak tampil di dropdown | Status bukan `open` | Ubah status course |
| Route [enrollments.preview] not defined | View lama masih memakainya | Pastikan `enrollments/create.blade.php` sudah ditimpa |
| Delete course tidak menghapus enrollment | Migration lama / FK bukan cascade | `SHOW CREATE TABLE enrollments` |

Urutan diagnosis: pesan error → route → controller → model → migration/DB.
`php artisan optimize:clear` membantu bila perubahan tidak terbaca.

## Checklist akhir

- [ ] ERD final, screenshot migrate, screenshot FK
- [ ] 2 foreign key + composite unique aktif
- [ ] Relasi `Participant->enrollments`, `Enrollment->participant/course`, `Course->enrollments`
- [ ] Form dua dropdown, duplicate ditolak, laporan tampil
- [ ] 8 skenario matrix + 2 skenario cascade
- [ ] Log AI dan refleksi diisi dengan kalimat sendiri

Commit:

```
git add .
git commit -m "feat: add participant course enrollment relations"
```

## Alur yang harus bisa dijelaskan

- **Store:** form → `POST /enrollments` → validasi (`exists` + unique pasangan) →
  `Enrollment::create` (INSERT dengan FK) → redirect `/enrollments` + flash.
- **Report:** `Enrollment::with(['participant','course'])` → nama peserta dari
  `participants.name`, start date dari `courses.start_date` (tidak diduplikasi).
- **Cascade:** FK `ON DELETE CASCADE` membuat MySQL menghapus enrollment yang
  menunjuk ke induk yang dihapus.
