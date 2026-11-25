# KursusKu — Pertemuan 13 (file siap pakai)

Lanjutan pertemuan 12. Hasil akhir: **Data Discovery** — Course index punya search `q`,
filter status, pagination 10 data/halaman, empty state, fallback tanpa JavaScript,
Ajax opsional (live search + debounce 300 ms + Ajax pagination), dashboard count,
dan kerangka draft artikel ilmiah.

## Cara memakai (timpa langsung)

1. Hentikan `composer run dev` / `php artisan serve` bila sedang jalan.
2. Ekstrak zip ini **langsung ke folder proyek** (folder yang berisi file `artisan`,
   misalnya `C:\laragon\www\kursusku-laravel`). Pilih **Replace / Timpa**.
3. Buka terminal **di folder proyek tersebut** (bukan `C:\laragon\www`), lalu:

```
php artisan optimize:clear
php artisan db:seed --class=CourseDiscoverySeeder
php artisan test
```

4. Tidak ada migration baru. Jangan memakai `migrate:fresh`.
5. Ajax ditulis langsung di halaman (`@push('scripts')`), jadi **tidak perlu**
   `npm run build`.

## Daftar file

| File | Status | Isi |
| --- | --- | --- |
| `app/Http/Controllers/CourseController.php` | timpa | `index(Request)`: search, filter, `paginate(10)->withQueryString()`, respons partial. Method lain tidak berubah. |
| `app/Providers/AppServiceProvider.php` | timpa | `Paginator::useBootstrapFive()` |
| `resources/views/courses/index.blade.php` | timpa | form GET + `@include` partial + skrip Ajax |
| `resources/views/courses/_results.blade.php` | baru | tabel hasil, empty state, info jumlah, pagination |
| `resources/views/layouts/app.blade.php` | timpa | tambah `@stack('scripts')` |
| `app/Http/Controllers/HomeController.php` | timpa | hitungan `$stats` |
| `resources/views/home.blade.php` | timpa | bagian "Ringkasan Data" |
| `database/seeders/CourseDiscoverySeeder.php` | baru | 30 course dummy + 1 course unik |
| `tests/Feature/CourseSearchTest.php` | baru | 5 skenario + tambahan + partial + dashboard |
| `tests/Feature/ExampleTest.php` | timpa | tambah `RefreshDatabase` (beranda kini query DB) |
| `evidence/week-13/*` | baru | test matrix, kerangka artikel, log AI, daftar screenshot |

## Langkah 0 — Pastikan titik awal aman

```
cd C:\laragon\www\kursusku-laravel
php artisan about
php artisan route:list --name=courses
php artisan migrate:status
git add .
git commit -m "chore: checkpoint before course search pagination"
```

Cek: `/courses` terbuka, tambah/edit/hapus Course masih jalan. Data yang diperlukan:
minimal 21 Course dengan status open, closed, draft → jalankan seeder (Langkah 1).

## Langkah 1 — Siapkan data uji

```
php artisan db:seed --class=CourseDiscoverySeeder
```

Hasil: 30 course `DISC-001..030` (18 open, 6 closed, 6 draft) dan 1 course
`UNIK-001` ("Kursus Unik Zeta"). Aman dijalankan berulang (tidak membuat duplikat).
Skema database tidak berubah.

## Langkah 2 — Search `q`

Di `CourseController@index`:

- `trim((string) $request->query('q'))` mengambil keyword.
- `when($search !== '', ...)` mencari pada `code` **atau** `name`.
- `orderBy('name')` + `paginate(10)`.

Uji: `/courses?q=laravel`

## Langkah 3 — Filter status

Status hanya diterapkan jika ada di daftar `['open', 'closed', 'draft']`.
Nilai kosong atau tidak dikenal (misalnya `active`) **diabaikan**, bukan menghasilkan 0 data.

Uji:
```
/courses?status=open
/courses?status=closed
/courses?status=draft
/courses?q=web&status=open
```

## Langkah 4 — Form GET di Blade

Form berada di atas tabel, `method="GET"`, tanpa `@csrf` (tidak mengubah data).
Setelah **Cari**, URL berubah menjadi `?q=...&status=...` dan nilai input tetap.
**Reset** mengembalikan ke `/courses`.

## Langkah 5 — Pagination 10 data

`{{ $courses->links() }}` ada di partial. `withQueryString()` menjaga `q` dan `status`
saat pindah halaman.

Penyesuaian proyek: tampilan default pagination Laravel memakai Tailwind, sedangkan
KursusKu memakai Bootstrap, jadi `AppServiceProvider` memanggil
`Paginator::useBootstrapFive()`.

Uji: `/courses?q=DISC` → page 1 berisi 10 baris. Klik page 2 → URL tetap memuat `q=DISC`.

## Langkah 6 — Empty state

`@forelse` menampilkan "Tidak ada kursus yang sesuai pencarian/filter." dan
"0 kursus ditemukan". 0 hasil adalah kondisi normal, bukan error.

Uji: `/courses?q=tidak-ada-xyz-999`

## Langkah 7 — Uji tanpa JavaScript

1. DevTools → Ctrl+Shift+P → "Disable JavaScript".
2. Reload `/courses`, isi keyword/status, klik **Cari**.
3. Pindah ke page 2.
4. Aktifkan JavaScript kembali dan pastikan tidak ada regresi.

Screenshot hasil search beserta URL-nya sebagai bukti.

## Langkah 8–9 — Partial view dan satu query, dua respons

- `courses/_results.blade.php` berisi tabel + pagination, dibungkus `<div id="course-results">`.
- Controller memakai **satu query**. Jika `?partial=1`, yang dikembalikan hanya partial.
- Link pagination pada respons partial tidak membawa `partial=1`
  (`->appends('partial', null)`), agar link yang dibuka di tab baru tetap halaman penuh.

Uji: `/courses?q=web&status=open&partial=1` → hanya blok hasil, tanpa navbar/layout.

## Langkah 10–11 — Live search dan Ajax pagination (opsional)

Skrip ada di bagian bawah `courses/index.blade.php`:

- **Debounce 300 ms:** `clearTimeout` + `setTimeout`.
- **Filter status:** event `change` memuat ulang hasil.
- **Ajax pagination:** klik link di `#course-results .pagination` di-intercept.
- Request lama dibatalkan (`abort`) bila ada request baru.
- Hasil memudar saat memuat, pesan error tampil jika gagal.
- URL diperbarui lewat `history.replaceState` (tanpa `partial`), sehingga bisa disalin.
- Tombol **Cari** tidak dihapus, jadi fallback GET tetap ada.

Uji dengan DevTools → Network: ketik keyword, request baru muncul sekitar 300 ms setelah
berhenti mengetik; pilih status; klik page 2; semua respons 200 tanpa reload penuh.
Screenshot sebagai `09-network-ajax.png`.

Catatan teknis: jQuery dimuat oleh `resources/js/app.js` sebagai module, sehingga skrip
inline menunggu `DOMContentLoaded`.

## Langkah 12 — Dashboard count (tambahan proyek)

Beranda (`/`) menampilkan Total Course, Course Open, Participant, dan Enrollment.
Karena beranda kini membaca database, `ExampleTest` diberi `RefreshDatabase`.

## Langkah 13 — Pengujian

Otomatis (SQLite in-memory, aman untuk database MySQL):

```
php artisan test --filter=CourseSearchTest
php artisan test
```

Manual: isi `evidence/week-13/test-matrix.md` (5 skenario utama), ambil screenshot
sesuai `evidence/week-13/README.md`.

| Skenario | URL |
| --- | --- |
| 0 hasil | `/courses?q=tidak-ada-xyz-999` |
| 1 hasil | `/courses?q=Unik Zeta` |
| >10 hasil | `/courses?q=DISC` |
| Keyword spasi | `/courses?q=Web Modern` |
| Filter + page 2 | `/courses?status=open` → klik page 2 |

## Langkah 14 — Draft artikel ilmiah

Gunakan `evidence/week-13/artikel-draft.md`: judul sementara, problem statement, gap,
metode, tabel skenario uji, hasil awal, pembahasan, referensi.
Bagian **[ISI]** diisi dari hasil uji nyata. Jangan menulis Pass atau angka performa
sebelum benar-benar diuji, dan jangan mengarang referensi.

## Troubleshooting

| Gejala | Periksa |
| --- | --- |
| Search tidak berubah | URL harus memuat `?q=...`; name input harus `q` |
| Page 2 kehilangan keyword | `withQueryString()` setelah `paginate(10)` |
| Filter kosong → 0 data | Pastikan memakai `when(in_array(...))` |
| Pagination tampil aneh/besar | `Paginator::useBootstrapFive()` belum aktif; jalankan `php artisan optimize:clear` |
| Error Blade saat 0 hasil | Pakai `@forelse` / `@empty` |
| Ajax tidak jalan | Console DevTools; pastikan Vite berjalan (`composer run dev`) agar `app.js` ter-load |
| Ajax mengganti seluruh halaman | `?partial=1` tidak sampai ke controller; cek tab Network |
| Klik pagination reload penuh | Handler klik belum aktif atau JS error |
| `Could not open input file: artisan` | Terminal bukan di folder proyek; `cd` ke folder yang berisi `artisan` |
| `ExampleTest` gagal | Pastikan `ExampleTest.php` ikut tertimpa |

Urutan debugging: URL → route → controller/query → Blade → JavaScript/Ajax.

## Checklist akhir

- [ ] Search `q` dan filter open/closed/draft bekerja
- [ ] Pagination 10 data/halaman, `withQueryString` aktif
- [ ] Empty state 0 hasil
- [ ] Tanpa JavaScript tetap bekerja
- [ ] 5 skenario uji dicatat + screenshot
- [ ] (Opsional) Partial, live search, debounce 300 ms, Ajax pagination, Network evidence
- [ ] Dashboard count tampil
- [ ] Draft artikel: judul, problem, gap, metode, tabel uji
- [ ] Log AI dan jawaban pertanyaan cepat diisi dengan kalimat sendiri

Commit:

```
git add .
git commit -m "feat: add course search, status filter, pagination and ajax live search"
```
