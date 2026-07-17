# Contributing to LaciPOS

Terima kasih tertarik untuk berkontribusi pada LaciPOS! Kami sangat menghargai waktu dan usaha Anda. Berikut adalah panduan untuk memulai.

## Cara Berkontribusi

### 1. Laporkan Bug atau Masalah

Jika Anda menemukan bug, silakan buka [GitHub Issues](https://github.com/yogaarrd/LaciPOS/issues) baru dengan informasi berikut:

- Langkah-langkah untuk mereproduksi bug
- Perilaku yang diharapkan vs yang terjadi
- Screenshot (jika ada)
- Environment (OS, versi PHP, versi Node.js)

### 2. Ajukan Fitur Baru

Punya ide fitur? Buka [GitHub Issues](https://github.com/yogaarrd/LaciPOS/issues) baru dengan label `enhancement` dan jelaskan:

- Apa yang ingin Anda tambahkan
- Mengapa fitur ini berguna
- Bagaimana seharusnya fitur ini bekerja

### 3. Pull Request

1. **Fork repositori** ini ke akun GitHub Anda.
2. **Clone** fork Anda ke lokal:
   ```bash
    git clone https://github.com/username-anda/LaciPOS.git
    cd LaciPOS
   ```
3. **Buat branch** baru untuk perubahan Anda:
   ```bash
   git checkout -b feat/nama-fitur-anda
   ```
   Gunakan prefix branch:
   - `feat/` — fitur baru
   - `fix/` — perbaikan bug
   - `refactor/` — refaktor kode
   - `docs/` — perubahan dokumentasi
   - `style/` — perubahan gaya kode (formatting, dll)
4. **Lakukan perubahan** yang diinginkan.
5. **Jalankan pengujian** jika ada:
   ```bash
   composer test
   npm run lint
   ```
6. **Commit** perubahan Anda:
   ```bash
   git add .
   git commit -m "feat: deskripsi singkat perubahan"
   ```
   Gunakan konvensi commit: `feat:`, `fix:`, `refactor:`, `docs:`, `style:`.
7. **Push** ke branch Anda:
   ```bash
   git push origin feat/nama-fitur-anda
   ```
8. **Buka Pull Request** ke branch `main` repositori ini.

## Panduan Kode

- Ikuti gaya kode yang sudah ada (Laravel conventions, PSR-12 untuk PHP, Vue 3 Composition API untuk frontend).
- Gunakan TypeScript untuk file Vue baru.
- Pastikan tidak ada debugging code (`dd()`, `dump()`, `console.log()`) yang tertinggal.
- Tulis kode yang bersih dan mudah dibaca, komentar minimal.
- Tambahkan validasi untuk input pengguna.

## Struktur Proyek

```
laci-pos/
├── app/
│   ├── Http/Controllers/   # Controller
│   ├── Models/             # Eloquent Models
│   ├── Services/           # Business logic / Services
│   └── ...
├── resources/
│   ├── js/
│   │   ├── Components/     # Vue components
│   │   ├── Pages/          # Inertia pages
│   │   ├── Layouts/        # Layout components
│   │   └── ...
│   └── views/              # Blade templates
├── routes/
│   └── web.php             # Route definitions
├── public/
│   └── assets/             # Static assets (logo, images)
└── ...
```

## Kode Etik

Dengan berpartisipasi dalam proyek ini, Anda setuju untuk menjaga lingkungan yang terbuka dan ramah. Harap bersikap sopan dan hormat kepada sesama kontributor.

## Butuh Bantuan?

Jika Anda memiliki pertanyaan, jangan ragu untuk membuka [GitHub Issues](https://github.com/yogaarrd/LaciPOS/issues) atau hubungi pengembang melalui email.
