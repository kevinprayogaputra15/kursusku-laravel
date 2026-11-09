# Test Matrix Pertemuan 11 — CRUD Course Lanjutan

Kolom **Actual**, **Status**, dan **Bukti** diisi setelah diuji sendiri di browser.
Kolom Expected sudah sesuai panduan. Kolom "Tes otomatis" menunjuk ke
`tests/Feature/CourseCrudTest.php` (`php artisan test --filter=CourseCrudTest`).

| No | Kasus | Input / tindakan | Expected | Actual | Status | Bukti | Tes otomatis |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | Duplicate code saat create | Pakai code Course yang sudah ada | Ditolak; error dekat field `code` | | | 04-validation-duplicate-code.png | test_01 |
| 2 | Nama kosong | `name` = kosong | Ditolak; name required | | | | test_02 |
| 3 | Nama terlalu pendek | `name` = `AI` | Ditolak; minimal 3 karakter | | | | test_03 |
| 4 | Fee negatif | `fee` = `-1000` | Ditolak; minimal 0 | | | | test_04 |
| 5 | Fee bukan integer | `fee` = `abc` | Ditolak; harus integer | | | | test_05 |
| 6 | Quota nol | `quota` = `0` | Ditolak | | | | test_06 |
| 7 | Quota 501 | `quota` = `501` | Ditolak | | | 05-validation-quota-501.png | test_07 |
| 8 | Boundary quota minimum | `quota` = `1` | Diterima | | | | test_08 |
| 9 | Boundary quota maksimum | `quota` = `500` | Diterima | | | | test_09 |
| 10 | Tanggal invalid | Kirim nilai bukan tanggal (DevTools) | Ditolak oleh rule `date` | | | | test_10 |
| 11 | Status invalid | `status` = `active` (DevTools) | Ditolak; hanya draft/open/closed | | | | test_11 |
| 12 | Update dengan code sendiri | Edit tanpa mengubah code | Berhasil | | | 06-update-success.png | test_12 |
| 13 | Update ke code Course lain | Ganti code ke milik Course lain | Ditolak unique | | | | test_13 |
| 14 | Update normal | Ubah nama/fee/quota/status valid | Berhasil; detail menampilkan data baru | | | | test_14 |
| 15 | Delete | Hapus Course dummy `DEL-001` | Hilang dari index + flash sukses | | | 07-delete-success.png | test_15 |

Catatan uji manual:

- Form create dan edit memakai `novalidate` supaya batas `min`/`max` HTML
  tidak menutupi validasi server. Tujuannya membuktikan server tidak
  mempercayai UI.
- Uji boundary quota berurutan: 0 → 1 → 500 → 501.
- Uji delete paling akhir dengan Course dummy.
