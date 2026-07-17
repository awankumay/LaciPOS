<h1 align="center">Laci Point Of Sales - Laci POS</h1>
<p align="center">
  <img src="public/assets/logo/laci-banner-logo.png" alt="LaciPOS Logo" width="full" height="auto">
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
2. Download file installer sesuai sistem operasi Anda:
   - **Windows** — `LaciPOS-Setup-x.x.x.exe`
   - **macOS** — `LaciPOS-x.x.x.dmg`
3. Jalankan file installer dan ikuti petunjuk instalasi.
4. Buka aplikasi LaciPOS yang sudah terinstal.
5. Aplikasi akan memandu Anda melalui proses setup awal (onboarding).

> **Persyaratan:** macOS 12+ atau Windows 10+, printer thermal opsional.

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

## Kontribusi

Kami menyambut kontribusi dari siapa pun! Silakan lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk panduan memulai.

## Kontak

- **Pengembang** — Yoga Ardiana
- **Email** — [yogaardiana05@gmail.com](mailto:yogaardiana05@gmail.com)
- **GitHub Issues** — [github.com/yogaarrd/LaciPOS/issues](https://github.com/yogaarrd/LaciPOS/issues)
