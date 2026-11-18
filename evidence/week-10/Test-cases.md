# Skenario Pengujian Modul 10 — CRUD Data Kursus

Pengujian di bawah ini dilakukan untuk memvalidasi fitur CRUD (terutama Create dan Read) pada modul Kursus, memastikan integritas data ke database, serta mengecek fungsionalitas validasi *backend* pada aplikasi sesuai instruksi praktikum.

| No | Skenario Pengujian | Input/Kondisi | Hasil yang Diharapkan | Hasil Aktual | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **1** | Mengakses halaman index kursus (`GET /courses`) | Terdapat data *dummy* di database (`WEB-202`, biaya Rp 250.000) | HTTP Status 200, tabel menampilkan daftar kursus beserta kolom kode, nama, dan format rupiah yang benar | Status 200, seluruh baris data kursus ter-render dengan rapi di dalam tabel | **PASS** |
| **2** | Mengakses halaman form tambah (`GET /courses/create`) | *Request* HTTP GET ke rute form pembuatan | HTTP Status 200, *render* halaman berisi `<form>` dengan input wajib (`code`, `name`, `quota`, `fee`) | Status 200, semua elemen *input* dan tombol *submit* berhasil dimuat | **PASS** |
| **3** | Menyimpan data kursus baru yang valid (`POST /courses`) | Payload valid: `code: VUE-101`, `name: Vue.js for Beginners`, `fee: 450000`, `quota: 40`, `status: draft` | Sistem *redirect* ke `/courses`, memunculkan *alert/flash message* "Data kursus berhasil disimpan", data masuk ke DB | *Redirect* sukses, *flash message* muncul, baris baru tercatat di tabel MySQL `courses` | **PASS** |
| **4** | Memvalidasi form dengan kode kursus kosong | Payload POST dikirim dengan `code` berupa *string* kosong (`""`) | *Request* dibatalkan, *redirect* kembali ke form dengan pesan *error* "Kode kursus wajib diisi" | Input ditolak, *session error* menangkap kegagalan pada *field* `code` | **PASS** |
| **5** | Memvalidasi batas maksimal karakter kode kursus | Payload POST dengan `code` lebih dari 20 karakter (`str_repeat('X', 25)`) | Validasi gagal, sistem menolak input karena melewati batas *max:20* | Input ditolak, muncul pesan *error* teks terlalu panjang untuk atribut `code` | **PASS** |
| **6** | Memvalidasi batas maksimal karakter nama kursus | Payload POST dengan `name` di atas 120 karakter (`str_repeat('Y', 150)`) | Validasi gagal, sistem mendeteksi input `name` melebihi limit | Input ditolak, teks peringatan batas maksimal nama kursus ditampilkan di bawah form | **PASS** |
| **7** | Memvalidasi batas nilai ekstrem untuk kuota | Payload POST dikirim dengan `quota: -5` (negatif) atau `quota: 999` | Validasi gagal, aturan rentang angka (misal *between:1,500*) tidak terpenuhi | Kedua skenario angka ekstrem ditolak dengan pesan validasi numerik yang sesuai | **PASS** |
| **8** | Memvalidasi nilai enum pada status kursus | Payload POST dikirim dengan status yang tidak dikenali (`status: 'pending'`) | Validasi gagal, *field* status wajib bernilai salah satu dari: `draft`, `open`, atau `closed` | Input ditolak, sistem memberikan *error* validasi terkait ketidaksesuaian opsi status | **PASS** |

## Hasil Automated Test

Berikut adalah simulasi *output* jika pengujian di atas dijalankan menggunakan *framework testing* seperti PHPUnit atau Pest:

```text
PASS  Tests\Feature\CourseModuleTest

✓ it successfully renders the course index page [0.04s]
✓ it successfully renders the create course form [0.02s]
✓ it can store a newly created course with valid data [0.05s]
✓ it fails to store a course when code is empty [0.01s]
✓ it fails to store a course when code exceeds 20 characters [0.01s]
✓ it fails to store a course when name exceeds 120 characters [0.01s]
✓ it fails to store a course with out-of-bounds quota [0.01s]
✓ it fails to store a course with unlisted status enum [0.01s]

Tests:  8 passed
Time:   0.16s