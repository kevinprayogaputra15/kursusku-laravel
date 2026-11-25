# KursusKu — Tampilan v2 (hitam + aksen gradien)

## Cara memakai (timpa langsung)

1. Ekstrak zip ini ke folder proyek (folder yang berisi file `artisan`), pilih **Replace**.
2. **Tidak perlu `npm run build`.** Zip ini sudah berisi hasil build (`public/build/`),
   jadi cukup `Ctrl + F5` di browser.
   - Jika file `public/hot` ada (sisa `npm run dev`) dan Vite tidak sedang berjalan,
     hapus file itu; kalau tidak, browser mencari CSS ke server Vite yang mati.
   - Jika Anda mengubah `resources/css/app.css` sendiri nanti, jalankan `npm run build`
     lagi atau pakai `composer run dev`.
3. Tidak ada migration, seeder, atau perubahan route.

## Yang berubah

| File | Perubahan |
| --- | --- |
| `public/build/*` | hasil build Vite dari CSS v2 (`app-Cze-cXGP.css`) |
| `resources/css/app.css` | tema hitam v2: cahaya halus di latar, aksen gradien violet-cyan, tombol bercahaya, kartu kaca, pagination gelap, status berwarna, animasi masuk, scrollbar gelap, ikon pencarian |
| `resources/views/partials/navbar.blade.php` | menu aktif ditandai otomatis (`request()->routeIs`) |
| `resources/views/courses/_results.blade.php` | status Course tampil sebagai pil berwarna |
| `resources/views/courses/show.blade.php` | status berwarna di halaman detail |
| `resources/views/enrollments/index.blade.php` | status enrollment berwarna |

## Warna status

| Status | Warna |
| --- | --- |
| open | hijau |
| closed | merah |
| draft | kuning |
| registered | cyan |

## Mengubah warna aksen

Semua warna ada di blok `:root` pada `resources/css/app.css`:

```
--kk-accent:   #a78bfa;   /* violet */
--kk-accent-2: #22d3ee;   /* cyan */
--kk-grad: linear-gradient(135deg, #a78bfa 0%, #60a5fa 55%, #22d3ee 100%);
```

Ganti nilai tersebut (misalnya hijau-teal atau oranye-merah) dan seluruh tombol, judul,
pagination, serta glow ikut berubah.

## Catatan

- Animasi otomatis nonaktif untuk pengguna dengan pengaturan "reduce motion".
- Saat dicetak (`Ctrl + P`), latar kembali putih agar hemat tinta.
- Tes otomatis tidak terpengaruh: teks status tetap ada di dalam pil.
