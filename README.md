<h1 align="center">Laci Point Of Sales - Laci POS</h1>
<p align="center">
  <img src="public/assets/logo/laci-banner-logo.png" alt="LaciPOS Logo" width="100%">
</p>

<p align="center">
  <a href="https://github.com/yogaarrd/LaciPOS/blob/main/LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License">
  </a>
  <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel" alt="Laravel">
  </a>
  <a href="https://vuejs.org">
    <img src="https://img.shields.io/badge/Vue_3-4FC08D?logo=vuedotjs&logoColor=white" alt="Vue 3">
  </a>
  <a href="https://inertiajs.com">
    <img src="https://img.shields.io/badge/Inertia.js-9553E9?logo=inertia&logoColor=white" alt="Inertia.js">
  </a>
  <a href="https://tailwindcss.com">
    <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  </a>
  <a href="https://nativephp.com">
    <img src="https://img.shields.io/badge/NativePHP-1.3-0055FF?logo=electron&logoColor=white" alt="NativePHP">
  </a>
  <img src="https://img.shields.io/badge/PHP_8.2-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white" alt="SQLite">
  <a href="https://github.com/yogaarrd/LaciPOS/releases">
    <img src="https://img.shields.io/github/v/release/yogaarrd/LaciPOS?color=00ed64&label=version" alt="Latest Release">
  </a>
</p>

<p align="center">
  Aplikasi POS (Point of Sale) desktop modern, cepat, ringan, dan siap digunakan untuk bisnis retail, kuliner, dan UKM.
  <br>
  Dibangun dengan Laravel, Vue 3, Inertia.js, dan NativePHP.
  <br>
  <br>
  <a href="#fitur">Fitur</a>
  ·
  <a href="#cara-install">Cara Install</a>
  ·
  <a href="#untuk-pengembang">Untuk Pengembang</a>
  ·
  <a href="#kontribusi">Kontribusi</a>
</p>

---

## Demo

<a href="https://youtu.be/smipcU3-IMw?si=KDgEeGNe68JX0r6o" target="_blank">
  <img src="https://img.youtube.com/vi/smipcU3-IMw/maxresdefault.jpg" alt="LaciPOS Demo" width="100%">
</a>

Klik gambar di atas untuk menonton video demo fitur utama LaciPOS.

---

## Fitur

- **POS Kasir** — Antarmuka kasir cepat dengan pencarian produk, keranjang real-time, dan checkout dalam satu klik.
- **Manajemen Produk** — Tambah, edit, hapus produk dengan kategori, harga, stok, dan gambar.
- **Stok & Restock Alert** — Pantau stok minimum dan peringatan produk yang perlu di-restock.
- **Manajemen Kategori** — Atur produk dalam kategori untuk navigasi lebih mudah.
- **Laporan Penjualan** — Grafik pendapatan harian/mingguan, laba kotor, dan riwayat transaksi.
- **Cetak Struk Thermal** — Cetak struk langsung ke printer thermal — lintas platform (macOS & Windows).
- **Download PDF Struk** — Simpan struk sebagai PDF untuk arsip atau dikirim ke pelanggan.
- **Multi-Kasir** — Dukungan beberapa kasir dengan sesi terpisah.
- **Onboarding Interaktif** — Panduan setup awal toko yang interaktif dan mudah diikuti.
- **Info Aplikasi** — Halaman informasi versi, database, dan pengembang.

## Cara Install

### Untuk Pengguna Umum (Non-Teknis)

1. Buka halaman [Releases](https://github.com/yogaarrd/LaciPOS/releases) di repositori ini.
2. Download file installer :
   - **Windows** — `LaciPOS-Setup-x.x.x.exe`
3. Jalankan file installer dan ikuti petunjuk instalasi.
4. Buka aplikasi LaciPOS yang sudah terinstal.
5. Aplikasi akan memandu Anda melalui proses setup awal (onboarding).

> **Persyaratan:** Windows 10+, printer thermal opsional.

### Untuk Pengembang (Developer)

Jika Anda ingin menjalankan dari source code, berkontribusi, atau melakukan kostumisasi:

```bash
# Clone repositori
git clone https://github.com/yogaarrd/LaciPOS.git
cd LaciPOS

# Copy environment
cp .env.example .env

# Buat file database SQLite
touch database/database.sqlite

# Install dependensi PHP
composer install

# Install dependensi JavaScript
npm install

# Generate key aplikasi
php artisan key:generate

# Jalankan migrasi database
php artisan migrate

# Build aset frontend
npm run build

# Jalankan development server
php artisan serve
```

Untuk menjalankan sebagai aplikasi desktop (NativePHP):

```bash
php artisan native:dev
```

> **Persyaratan Developer:** PHP 8.2+, Composer, Node.js 18+, npm, SQLite.

## Untuk Pengembang

| Bagian | Teknologi |
|--------|-----------|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js 3, Tailwind CSS |
| Desktop | NativePHP (Electron) |
| Database | SQLite |
| Cetak | DomPDF, NativePHP Print API |
| Chart | Unovis |
| UI Icons | Lucide |

## Sponsor

Dukung pengembangan LaciPOS agar terus berkembang untuk semua orang.

<a href="https://ko-fi.com/yogaardiana" target="_blank"><img src="https://img.shields.io/badge/Ko--fi-F16061?logo=ko-fi&logoColor=white" alt="Ko-fi" height="30"></a>
<a href="https://trakteer.id/yogaardiana" target="_blank"><img src="https://img.shields.io/badge/Trakteer.id-FF5E00?logo=buymeacoffee&logoColor=white" alt="Trakteer" height="30"></a>

Terima kasih atas dukungannya!

## Kontribusi

Kami menyambut kontribusi dari siapa pun! Silakan lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk panduan memulai.

## Kontak

- **Pengembang** — Yoga Ardiana
- **Email** — [yogaardiana05@gmail.com](mailto:yogaardiana05@gmail.com)
- **GitHub Issues** — [github.com/yogaarrd/LaciPOS/issues](https://github.com/yogaarrd/LaciPOS/issues)
