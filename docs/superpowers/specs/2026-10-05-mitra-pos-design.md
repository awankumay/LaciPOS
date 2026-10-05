# Mitra POS — Design Spec (Fase 1)

- **Tanggal:** 2026-10-05
- **Status:** Draft — menunggu review
- **Repo target:** `mitra-pos` (baru; spec ini sementara disimpan di LaciPOS branch `docs/mitra-pos-spec`)
- **Basis:** LaciPOS (`develop`, commit `c1e1036`) — POS desktop single-store, Laravel 11 + Inertia/Vue 3 + NativePHP + SQLite

---

## 1. Ringkasan

Mitra POS adalah turunan LaciPOS yang dibangun ulang sebagai **POS offline-first dengan arsitektur SaaS multi-tenant** di atas **Laravel 13**. Satu codebase berjalan dalam dua mode:

- **SaaS** — satu server cloud melayani banyak tenant (bisnis), self-signup.
- **Standalone** — server single-tenant yang di-host sendiri oleh pelanggan (mini-PC toko di LAN, VPS milik pelanggan, atau Docker).

Kasir tetap bisa bertransaksi saat internet putus; transaksi tersinkron otomatis ke server tanpa hilang dan tanpa dobel.

### 1.1 Tujuan & kriteria sukses

1. Tenant (mode SaaS) bisa mendaftar sendiri, membuat outlet, mengundang staf, dan mengaktifkan perangkat kasir.
2. Kasir bisa membuka POS dari kondisi mati total tanpa internet dan melakukan transaksi penuh (termasuk cetak struk).
3. Transaksi offline tidak pernah hilang dan tidak pernah tercatat dobel setelah sinkron — dibuktikan oleh tes E2E.
4. Pemilik bisa melihat laporan penjualan, stok, dan pajak lintas outlet dari back-office.
5. Kontrak Sync API cukup stabil untuk dipakai klien Android (Flutter) di fase berikutnya tanpa perubahan.
6. Setiap request bisa dilacak end-to-end melalui `request_id`.

### 1.2 Keputusan utama (hasil brainstorming)

| # | Keputusan | Alasan |
|---|---|---|
| D1 | Repo baru `mitra-pos`; LaciPOS tetap berjalan apa adanya | Memisahkan produk open-source desktop dari produk SaaS |
| D2 | Klien kasir: **PWA + Electron dari satu codebase Vue** (Fase 1); Flutter Android fase berikutnya | Satu implementasi logika offline; tanpa instalasi untuk PWA |
| D3 | Server **API-first** untuk sinkronisasi; Sync API client-agnostic | Dipakai PWA, Electron, dan Flutter |
| D4 | Tenancy: **single database + kolom `tenant_id`** + PostgreSQL RLS | Operasional sederhana; RLS sebagai lapisan pengaman kedua |
| D5 | Offline hanya untuk **transaksi**; katalog & pengaturan diedit online | Data offline append-only → praktis tanpa konflik |
| D6 | Fase 1 = **fondasi SaaS tanpa billing otomatis** | Plan/limit diset manual oleh super-admin |
| D7 | Back-office: **Inertia + Vue**, bukan Filament | Filament (Livewire) terasa lambat karena round-trip server untuk setiap interaksi |
| D8 | Tema: **Edinburgh** (`spykapps/theme-edinburgh`, MIT) diadaptasi dari token CSS-nya | Paket aslinya khusus Filament (selector `.fi-*`), jadi tidak bisa dipasang langsung |
| D9 | Dua mode deployment: **SaaS & Standalone** dari satu codebase | Pelanggan yang ingin data on-premise / internet buruk tapi punya LAN |
| D10 | Stok: `track_stock` per produk + `stock_policy` per outlet (default `warn`) | Menu racikan tidak butuh stok; offline tidak bisa menolak keras |
| D11 | Pajak dikonfigurasi **per outlet** (`none`/`pbjt`/`ppn`) sesuai aturan Indonesia | Tarif PBJT ditetapkan Perda per kabupaten/kota |
| D12 | Server **tidak menolak** order tersinkron karena aturan bisnis; diberi flag | Transaksi offline sudah terjadi di dunia nyata |

### 1.3 Di luar scope Fase 1

- Billing otomatis / payment gateway langganan (Midtrans/Xendit), trial, auto-suspend.
- Klien Flutter Android.
- Stok bahan baku/resep (BOM), stok per varian, transfer stok antar-outlet, stock opname terjadwal.
- Edit katalog / manajemen stok saat offline.
- Integrasi e-Faktur/Coretax DJP, e-Tax/tapping box Bapenda, PPnBM, rezim pajak campuran dalam satu outlet.
- Lisensi berbayar untuk Standalone, migrasi data Standalone ↔ SaaS, auto-update server Standalone.
- Exporter OpenTelemetry (header `traceparent` sudah diterima dan dicatat).
- Service charge nominal tetap (hanya persentase).

---

## 2. Arsitektur

### 2.1 Stack

| Lapisan | Teknologi |
|---|---|
| Server | Laravel 13, PHP 8.4, PostgreSQL 16, Redis, Laravel Horizon |
| Back-office | Inertia + Vue 3 + TypeScript + Tailwind v4 + shadcn-vue (reka-ui); session auth |
| POS Client | Vue 3 + TypeScript + Pinia + Vue Router + Dexie (IndexedDB) + Workbox (`vite-plugin-pwa`) |
| Desktop shell | Electron (bukan NativePHP) + `electron-updater` |
| Auth API | Laravel Sanctum — satu token per perangkat |
| Testing | Pest, Vitest + fake-indexeddb + MSW, Playwright |

### 2.2 Struktur repo

```
mitra-pos/
├─ app/
│  ├─ Domain/
│  │  ├─ Tenancy/     Tenant, Plan, Outlet, BelongsToTenant, TenantScope,
│  │  │               TenantResolver, PlanLimiter
│  │  ├─ Catalog/     Product, Category, ProductVariant, VariantOption,
│  │  │               Discount, PaymentMethod, ProductOutlet
│  │  ├─ Sales/       Order, OrderItem, OrderCancellation, OrderTotalsCalculator
│  │  ├─ Inventory/   StockMovement, OutletStock, StockProjector
│  │  └─ Devices/     Device, DeviceActivation
│  ├─ Http/
│  │  ├─ Api/V1/
│  │  │  ├─ DeviceAuthController.php    POST /api/v1/devices/activate
│  │  │  ├─ DiagnosticsController.php   POST /api/v1/devices/diagnostics
│  │  │  └─ Sync/ PullController.php, PushController.php, MutationHandlers/
│  │  ├─ Web/Controllers/               back-office (Inertia)
│  │  ├─ Web/Controllers/Admin/         super-admin (SaaS only)
│  │  ├─ Requests/
│  │  └─ Middleware/   AssignRequestContext, ResolveTenant, SetPostgresTenant,
│  │                   EnsureSuperAdmin, HandleInertiaRequests
│  ├─ Policies/
│  └─ Support/Logging/ RedactSensitiveProcessor
├─ resources/js/
│  ├─ backoffice/      Pages/, Layouts/, Components/
│  ├─ pos/             lihat §6
│  └─ shared/
│     ├─ ui/           komponen shadcn-vue + override Edinburgh
│     └─ theme/        edinburgh.tokens.css, edinburgh.components.css
├─ electron/           main.ts, preload.ts, escpos/
├─ routes/             web.php, admin.php (SaaS only), api.php
├─ database/           migrations, factories, seeders
├─ docker/             compose.standalone.yml, Caddyfile
├─ tests/
│  ├─ Feature/{Sync,Tenancy,Backoffice}, Unit/Domain, Arch/
│  └─ fixtures/totals/*.json   (dipakai bersama PHP & JS)
└─ docs/superpowers/specs/
```

### 2.3 Batas komponen

- **Back-office** (online) hanya bicara ke server lewat Inertia; tidak pernah menyentuh Sync API.
- **POS Client** hanya bicara ke server lewat `/api/v1/*`. UI POS tidak pernah memanggil HTTP atau Dexie langsung — selalu lewat `db/repositories` dan `sync/`.
- **Domain** (`app/Domain`) tidak tahu tentang HTTP maupun mode deployment.
- **Flutter** (fase berikutnya) hanya bergantung pada kontrak §5.

### 2.4 Performa back-office

Pelajaran dari pengalaman lambatnya Filament:
- Inertia **partial reload** (`only: [...]`) dan **deferred props** untuk widget berat.
- **Prefetch** link saat hover.
- Validasi di klien untuk umpan balik instan; FormRequest server sebagai otoritas.
- Tabel kecil (kategori, metode bayar) difilter di klien; tabel besar (order, produk) memakai pagination server dengan debounce pencarian 300 ms.

### 2.5 Tema Edinburgh

Sumber: `spykapps/theme-edinburgh` (MIT, © Spykapps). Paket ini adalah tema Filament v4/v5 — satu file `resources/css/edinburgh.css` (921 baris) dengan 170 selector `.fi-*`. Yang diambil:

- **`shared/theme/edinburgh.tokens.css`** — blok `:root` dan `.dark` disalin apa adanya (surface stone `#faf9f6`/`#edeae4`, brass `#a8872a`, slate `#3a362e`, radius 3px, shadow, warna corner-bracket, varian dark warm-black), dipetakan ke Tailwind v4 `@theme`. Atribusi lisensi MIT disertakan di header file.
- **`shared/theme/edinburgh.components.css`** — ciri khas dibuat ulang untuk komponen sendiri: sidebar gradien + indikator border kiri, crown border brass 3px pada modal/dropdown/kartu login, corner bracket pada kartu statistik & section, input recessed (inset shadow), tombol primary bergradien brass, header tabel tebal dengan double border, avatar bulat dengan ring brass.
- Dipakai bersama oleh back-office dan POS, termasuk dark mode (kelas `.dark` di `<html>`).

### 2.6 Yang diporting dari LaciPOS

- Logika domain: kalkulasi diskon (persen/nominal, rentang tanggal/jam, kuota), service charge, MDR metode pembayaran, modifier harga/HPP varian.
- Struktur data produk/varian/kategori/diskon/metode pembayaran (ULID primary key).
- Halaman Vue LaciPOS sebagai titik awal UI back-office & POS.

Tidak dibawa: NativePHP, dompdf di klien, tabel `store_profiles` single-row, `products.stock` global, `users.role` global.

---

## 3. Mode Deployment

`MITRA_MODE=saas|standalone` → `config('mitra.mode')`.

| Aspek | SaaS | Standalone |
|---|---|---|
| Tenant | Banyak, self-signup | Satu, dibuat oleh `php artisan mitra:install` |
| URL back-office | `/app/{tenant}` | `/app/{tenant}` (sama) |
| Resolusi tenant di Sync API | Dari `device.tenant_id` | Sama |
| Registrasi tenant, `/admin`, `plans` | Aktif | Route tidak didaftarkan |
| Limit plan | Ditegakkan | Selalu diizinkan |
| RLS + `BelongsToTenant` | Aktif | Aktif |
| Multi-outlet | Ya | Ya |
| Distribusi | Server kita | Docker Compose: app + postgres + redis + caddy |

**Perbedaan mode hanya boleh ada di tiga tempat:** pendaftaran route, `TenantResolver`, dan `PlanLimiter`. Domain, Sync API, dan POS Client tidak tahu mode.

**HTTPS di LAN (Standalone):** Service Worker wajib HTTPS (kecuali `localhost`). Docker Compose menyertakan Caddy dengan dua opsi: domain publik + Let's Encrypt, atau CA lokal Caddy (root cert di-install sekali di tiap tablet/HP). Electron tidak terdampak karena memuat file lokal.

---

## 4. Model Data

### 4.1 Prinsip

- Primary key **ULID** (dibuat di klien saat offline, terurut waktu).
- Uang: `decimal(15,2)` di server; rupiah integer di klien.
- Setiap tabel tenant-scoped punya `tenant_id` (ULID, indexed, FK ke `tenants`).
- Tabel yang ditarik ke klien punya `updated_at`, `deleted_at` (soft delete) dan `sync_version xid8` (diisi trigger dengan `pg_current_xact_id()` pada insert/update).

### 4.2 Tenancy & akses

| Tabel | Kolom kunci |
|---|---|
| `tenants` | id, name, slug (unique), status `active\|suspended`, plan_id (nullable), timezone (default `Asia/Jakarta`), settings jsonb (`default_track_stock`, …) |
| `plans` | id, code, name, limits jsonb (`max_outlets`, `max_devices`, `max_products`) |
| `outlets` | id, tenant_id, code (2–4 huruf, unique per tenant), name, address, phone, logo_path, receipt_footer, paper_size `58mm\|80mm`, auto_print, `stock_policy` `block\|warn\|allow` (default `warn`), pengaturan pajak §4.6 |
| `users` | id, name, email (unique), password, is_super_admin |
| `tenant_user` | tenant_id, user_id, role `owner\|manager\|cashier`, pin_hash (bcrypt, untuk verifikasi server), pin_verifier (PBKDF2, untuk verifikasi offline), is_active |
| `outlet_user` | outlet_id, user_id |
| `devices` | id, tenant_id, outlet_id, name, code (2 karakter, unique per outlet), platform `pwa\|electron\|android`, app_version, last_seen_at, revoked_at |
| `device_activations` | id, tenant_id, outlet_id, code_hash, expires_at (15 menit), used_at, created_by |

### 4.3 Katalog (diedit online, ditarik ke klien)

- `categories`, `products`, `product_variants`, `variant_options`, `discounts`, `payment_methods` — struktur mengikuti LaciPOS, ditambah `tenant_id`.
- `products` menambah `track_stock` (bool, default dari `tenants.settings.default_track_stock`) dan mempertahankan `min_stock_alert`. Kolom `stock` dihapus.
- `product_outlet`: product_id, outlet_id, is_available (default true), price_override (nullable).

### 4.4 Transaksi (dibuat di klien, append-only)

- `orders`: id (ULID klien), tenant_id, outlet_id, device_id, user_id, order_number, status `completed\|cancelled`, subtotal, discount_type/value/amount/note, service_rate, service_amount, tax_regime, tax_rate, tax_label, prices_include_tax, service_in_tax_base, dpp_amount, tax_amount, total, payment_method_id, payment_method_snapshot (nama, tipe, MDR), mdr_amount, cash_received, change_amount, customer_name, customer_phone, flags jsonb, client_created_at, received_at.
- `order_items`: id, order_id, product_id, product_name_snapshot, snapshot_price, snapshot_cogs, variant_label, snapshot_discount_type/value/amount, quantity, subtotal, notes.
- `order_cancellations`: id (ULID klien), order_id, reason, cancelled_by, approved_by, device_id, client_created_at. Status order dihitung server.
- **Nomor order:** `{OUTLET}-{DEVICE}-{YYMMDD}-{seq4}` (contoh `JKT1-A3-261005-0042`); `seq` lokal per device per hari; `UNIQUE(tenant_id, order_number)`.
- **Flags** (`orders.flags`): `stock_negative`, `discount_over_quota`, `totals_mismatch`, `clock_skew`.

### 4.5 Stok

- Hanya untuk produk `track_stock = true`.
- `stock_movements`: id (ULID), tenant_id, outlet_id, product_id, delta (±int), reason `sale\|cancellation\|restock\|adjustment`, reference_id, device_id, user_id, created_at.
- `outlet_stocks`: (outlet_id, product_id) PK → qty. Proyeksi dari jumlah delta, diperbarui oleh `StockProjector` dalam transaksi yang sama dengan insert movement. Tidak pernah ditimpa nilai absolut dari klien.
- Restock & adjustment hanya dilakukan dari back-office (online).
- Klien menghitung `stok_lokal = outlet_stocks_terakhir_dari_pull + Σ delta lokal yang belum tersinkron`.
- `stock_policy` outlet ditegakkan di POS: `block` = produk habis tidak bisa masuk keranjang; `warn` = peringatan tapi boleh jual; `allow` = tanpa peringatan.
- Server menerima stok negatif; back-office menampilkan laporan **Stok Minus**.

### 4.6 Pajak (aturan Indonesia)

Referensi aturan (dicek 2026-10-05):
- **PBJT makanan/minuman** (UU 1/2022 HKPD): tarif maksimal 10%, ditetapkan Perda per kabupaten/kota; dasar pengenaan = jumlah yang dibayar (setelah diskon); usaha di bawah ambang omzet Perda dikecualikan. Restoran objek PBJT **tidak** dikenakan PPN (PMK-70/PMK.03/2022).
- **PPN** (PMK 131/2024, masih berlaku 2026): 12% × DPP nilai lain 11/12 untuk barang/jasa non-mewah (efektif 11%); wajib bagi PKP (omzet > Rp4,8 M/tahun atau PKP sukarela).
- **PPh Final UMKM 0,5%** (PP 55/2022): kewajiban pemilik atas omzet; tidak dicetak di struk.
- Service charge bukan pajak. Apakah service charge masuk DPP PBJT bisa berbeda per Perda — karena itu dibuat konfigurable.

Kolom di `outlets`:

| Kolom | Nilai | Validasi |
|---|---|---|
| `tax_regime` | `none\|pbjt\|ppn` | — |
| `tax_rate` | desimal | `pbjt`: 0–10 (default 10); `ppn`: tetap 12 |
| `tax_label` | string ≤ 30 | default "PBJT 10%" / "PPN" |
| `prices_include_tax` | bool | default `false` untuk pbjt, `true` untuk ppn |
| `service_charge_rate` | desimal % | 0–100; 0 = mati |
| `service_in_tax_base` | bool | default `true` |

**Algoritma total** (identik di `OrderTotalsCalculator` PHP dan `totals.ts` JS):

```
item_net_i    = (harga_i + Σ modifier varian_i − diskon item_i) × qty_i
subtotal      = Σ item_net_i
net           = subtotal − diskon_order
service       = round_half_up(net × service_rate / 100)
base          = net + (service_in_tax_base ? service : 0)

regime none:  dpp = 0; tax = 0
regime pbjt:  eksklusif → dpp = base;                          tax = round(dpp × rate/100)
              inklusif  → dpp = round(base × 100/(100+rate));  tax = base − dpp
regime ppn:   (dpp yang disimpan selalu DPP nilai lain = 11/12 × harga jual)
              eksklusif → dpp = round(base × 11/12);           tax = round(dpp × 12/100)
              inklusif  → tax = round(base × 11/111);          dpp = round((base − tax) × 11/12)
              (efektif 11% dari harga jual di kedua cara)

total = net + service + (eksklusif ? tax : 0)
```

Pembulatan ke rupiah penuh (half-up) di level order. Semua nilai di atas disnapshot ke `orders`.

**Laporan pajak** (back-office): rekap per outlet per bulan — total DPP dan pajak terpungut, dipisah PBJT/PPN — serta omzet bruto bulanan (dasar PPh Final).

> Konfigurasi default dan format struk harus divalidasi ke konsultan pajak / Bapenda setempat sebelum rilis.

### 4.7 Isolasi tenant

1. Trait `BelongsToTenant`: global scope `where tenant_id = current` + auto-fill `tenant_id` saat create.
2. **PostgreSQL RLS** di semua tabel ber-`tenant_id`: `USING (tenant_id = current_setting('app.tenant_id', true))`. Middleware `SetPostgresTenant` menjalankan `SET LOCAL app.tenant_id` per request/job (di dalam transaksi).
3. Role DB `mitra_admin` dengan `BYPASSRLS` hanya dipakai koneksi `admin` di namespace `Admin` dan command lintas tenant.
4. Test arsitektur: setiap model di `app/Domain` kecuali `Tenant`, `Plan`, `User` wajib memakai `BelongsToTenant`.

### 4.8 Sinkronisasi (tabel pendukung)

- `sync_mutations`: id (= mutation id klien), tenant_id, device_id, type, status `applied\|duplicate\|rejected`, result jsonb, request_id, created_at. Retensi 90 hari.
- `sync_runs`: id, tenant_id, device_id, direction `push\|pull`, request_id, counts jsonb, duration_ms, created_at. Retensi 90 hari.

---

## 5. Protokol Sinkronisasi (kontrak v1)

Semua endpoint di `/api/v1`, header wajib `X-Sync-Protocol: 1`, opsional `X-Request-Id`, `traceparent`. Error memakai `application/problem+json` (RFC 9457).

### 5.1 Aktivasi perangkat

1. Owner/manajer: **Outlet → Perangkat → Tambah** → kode 8 karakter + QR (sekali pakai, 15 menit).
2. POS: input URL server + kode → `POST /devices/activate { code, name, platform, app_version }`.
3. Respons: `{ token, device, outlet, tenant, cursor: null }` → klien langsung pull penuh.
4. Token disimpan di IndexedDB (PWA) atau dienkripsi `safeStorage` (Electron).
5. Pencabutan di back-office → request berikutnya `401` dengan `code: device_revoked`.
6. Limit `max_devices` ditegakkan oleh `PlanLimiter` saat aktivasi.

### 5.2 Login kasir (offline)

- Pilih nama → PIN 6 digit → diverifikasi lokal terhadap `pin_verifier` (PBKDF2-SHA256, salt unik per user, 210.000 iterasi, WebCrypto).
- 5 percobaan salah → perangkat terkunci 5 menit.
- Hanya user aktif yang ditugaskan ke outlet perangkat yang ditarik ke klien.
- PIN tidak pernah dipakai untuk login back-office.

### 5.3 Pull

`GET /sync/pull?cursor=<opaque>&limit=500`

```json
{
  "changes": {
    "categories": [], "products": [], "variants": [], "variant_options": [],
    "product_outlet": [], "discounts": [], "payment_methods": [],
    "outlet": {}, "users": [], "stock_levels": [{ "product_id": "…", "qty": 12 }]
  },
  "cursor": "…", "has_more": false, "reset": false, "server_time": "…"
}
```

- Cursor = `sync_version` tertinggi yang dikirim (dienkode opaque). Query: `sync_version > :cursor AND sync_version < pg_snapshot_xmin(pg_current_snapshot())` — baris dari transaksi yang belum commit tidak terlewat.
- Penghapusan dikirim sebagai tombstone `{ "id": "…", "deleted": true }`.
- `reset: true` bila cursor lebih tua dari retensi tombstone (30 hari) atau versi skema berubah → klien mengganti seluruh replika katalog; outbox tidak disentuh.
- `server_time` dipakai klien untuk mendeteksi selisih jam.

### 5.4 Push

`POST /sync/push`, maksimal 100 mutasi, urut sesuai pembuatan:

```json
{ "mutations": [
  { "id": "01J…", "type": "order.create", "occurred_at": "2026-10-05T09:12:03+07:00",
    "payload": { "order": {}, "items": [], "stock_movements": [] } },
  { "id": "01J…", "type": "order.cancel", "occurred_at": "…",
    "payload": { "cancellation": {}, "stock_movements": [] } }
]}
```

Respons:

```json
{ "results": [
  { "id": "01J…", "status": "applied", "flags": ["stock_negative"] },
  { "id": "01J…", "status": "rejected", "code": "unknown_product", "message": "…" }
]}
```

Aturan:
- **Idempotent** berdasarkan `mutation.id` (tabel `sync_mutations`); kiriman ulang → `duplicate` dengan hasil sebelumnya.
- Setiap mutasi diproses dalam transaksi DB sendiri.
- Aturan bisnis **tidak** menyebabkan penolakan — order diterima dan diberi flag (`stock_negative`, `discount_over_quota`, `totals_mismatch`, `clock_skew`). Server menghitung ulang total; jika beda, snapshot klien tetap dipakai dan `totals_mismatch` dicatat.
- `rejected` hanya untuk kesalahan struktural: payload tidak valid, outlet/device tidak cocok, referensi produk/metode bayar tidak ada.
- `order.cancel` hanya untuk order dari outlet yang sama dan wajib `approved_by` (manajer/owner, verifikasi PIN lokal).
- Tenant `suspended` → `403 tenant_suspended`; limit laju → `429`.

### 5.5 Siklus `SyncEngine` (klien)

- Urutan: **push, lalu pull**.
- Pemicu: event `online`, interval 30 detik saat online, 3 detik setelah checkout (debounce), tombol manual.
- Backoff eksponensial 5 detik → 5 menit dengan jitter.
- **Web Locks API** (`navigator.locks`) — satu siklus per origin.
- Status di header POS: tersinkron / *n* menunggu / offline sejak hh:mm / perlu perhatian.
- Outbox tidak pernah dihapus sebelum `applied`/`duplicate`. `rejected` → status `attention`.
- Bila `device_revoked` atau protokol tidak didukung: transaksi tertunda bisa diekspor ke JSON dan diimpor lewat back-office (**Perangkat → Impor transaksi**), yang diproses dengan handler push yang sama (tetap idempotent).

### 5.6 Versi

- `X-Sync-Protocol` tidak didukung → `426 Upgrade Required` → klien minta update; outbox disimpan.
- Server mendukung versi N dan N−1.
- Selisih jam > 5 menit → peringatan di POS dan flag `clock_skew`; laporan memakai `client_created_at`.

---

## 6. POS Client & Electron

### 6.1 Struktur

```
resources/js/pos/
├─ main.ts               bootstrap, register Service Worker, storage.persist()
├─ router.ts
├─ pages/                Activate, PinLogin, Cashier, Checkout, History, SyncStatus, Attention
├─ stores/               cart, session, syncStatus (Pinia)
├─ db/
│  ├─ schema.ts          Dexie schema + versi migrasi lokal
│  └─ repositories/      CatalogRepo, OrderRepo, StockRepo, OutboxRepo, LogRepo
├─ domain/               totals.ts, orderNumber.ts, stockPolicy.ts, pin.ts  (fungsi murni)
├─ sync/                 SyncEngine.ts, api.ts, outbox.ts
├─ hardware/             Printer.ts, ElectronPrinter.ts, WebUsbPrinter.ts,
│                        BrowserPrintFallback.ts, CashDrawer.ts
├─ receipt/              escpos.ts, html.ts
├─ diagnostics/          logger.ts
└─ sw.ts                 Workbox
```

Aturan dependensi: `pages → stores → (repositories | domain | sync | hardware)`. `domain/` tidak mengimpor apa pun selain dirinya.

### 6.2 Penyimpanan lokal (Dexie)

| Store | Isi |
|---|---|
| `categories`, `products`, `variants`, `variant_options`, `product_outlet`, `discounts`, `payment_methods`, `users`, `outlet`, `stock_levels` | Replika dari pull |
| `orders`, `order_items`, `cancellations` | Riwayat lokal 30 hari (dibersihkan bila sudah tersinkron) |
| `stock_deltas` | Delta lokal belum tersinkron |
| `outbox` | Mutasi: `pending\|sending\|attention` |
| `sync_meta` | cursor, last_sync_at, protocol, device, token (PWA) |
| `logs` | Ring buffer diagnostik (§7) |

`navigator.storage.persist()` diminta saat aktivasi; bila ditolak, POS menampilkan peringatan permanen di Status Sinkron.

### 6.3 Checkout

1. Hitung total dengan `totals.ts`.
2. Cek `stock_policy`.
3. Satu transaksi Dexie: `orders` + `order_items` + `stock_deltas` + `outbox`.
4. Cetak struk (gagal cetak tidak membatalkan order).
5. Picu `SyncEngine` (debounce).

### 6.4 PWA

- Workbox precache app shell → bisa cold-start offline.
- Gambar produk: cache-first, batas 200 MB.
- Service Worker baru menunggu sampai keranjang kosong; banner "Versi baru tersedia".
- Manifest installable (`display: standalone`).

### 6.5 Electron

- Memuat `pos/dist` dari protokol kustom `app://` (bukan URL server).
- `contextIsolation: true`, `nodeIntegration: false`, `sandbox: true`.
- `preload.ts` mengekspos `window.mitraBridge`: `printReceipt(bytes)`, `openCashDrawer()`, `listPrinters()`, `secureStore.get/set`, `appVersion()`.
- Auto-update via `electron-updater` (GitHub Releases). Target Fase 1: Windows; macOS menyusul.

### 6.6 Printer

- Struk dirender sekali ke byte ESC/POS (`receipt/escpos.ts`, 58/80 mm, logo raster).
- `ElectronPrinter` (USB/LAN via bridge) → `WebUsbPrinter` (Chrome/Edge) → `BrowserPrintFallback` (HTML + `window.print()`).

---

## 7. Request ID, Tracing & Logging

### 7.1 ID

| ID | Sumber | Fungsi |
|---|---|---|
| `request_id` | Header `X-Request-Id` dari klien (ULID/UUID valid), atau dibuat server | Melacak satu request klien → server → job |
| `trace_id` | Dari `traceparent` (W3C Trace Context) bila ada; selain itu = `request_id` | Persiapan OpenTelemetry |
| `mutation_id`, `order_id` | Klien | Melacak transaksi lintas retry |
| `tenant_id`, `outlet_id`, `device_id`, `user_id` | Server (auth) | Filter log |

### 7.2 Server

- Middleware `AssignRequestContext` (paling awal): `Context::add(['request_id', 'trace_id'])`; setelah auth menambah `tenant_id`, `outlet_id`, `device_id`, `user_id`.
- `X-Request-Id` dikembalikan di **setiap** respons, termasuk error.
- Laravel Context otomatis ikut ke queued job; saat push, setiap mutasi memakai `Context::scope(['mutation_id' => …])`.
- Log JSON terstruktur (Monolog `JsonFormatter`) ke stdout / file harian.
- `RedactSensitiveProcessor` menyamarkan `password`, `pin`, `pin_verifier`, `token`, `authorization`, `cash_received`.

### 7.3 Format error

- API: RFC 9457 `application/problem+json` dengan field tambahan `code` dan `request_id`.
- Back-office: halaman/toast error menampilkan **"Kode referensi: {request_id}"** + tombol salin.

### 7.4 Klien POS

- Setiap HTTP call membuat `X-Request-Id` sendiri; dicatat di `logs` (2.000 entri / 7 hari) beserta `mutation_id`, status, durasi.
- `window.onerror` / `unhandledrejection` ikut dicatat dengan versi aplikasi dan platform.
- Halaman Status Sinkron menampilkan riwayat siklus + `request_id`; tombol **Kirim log diagnostik** → `POST /api/v1/devices/diagnostics`.

### 7.5 Back-office: Log Sinkron

Per perangkat: waktu, arah, `request_id`, jumlah applied/duplicate/rejected, flags. Pencarian berdasarkan `request_id`, `mutation_id`, atau nomor order.

### 7.6 Sentry (opsional)

Aktif hanya bila `SENTRY_DSN` diisi (default mati di Standalone). Tag: `request_id`, `tenant_id`, `device_id`, `app_version`.

---

## 8. Back-office (Fase 1)

**Panel tenant `/app/{tenant}`:**
- Dashboard: omzet, jumlah transaksi, produk terlaris, stok rendah/minus, perangkat belum sinkron > 24 jam.
- Katalog: kategori, produk (+ varian, `track_stock`, ketersediaan & harga per outlet), diskon, metode pembayaran.
- Inventori: stok per outlet, restock, adjustment, riwayat movement, Stok Minus.
- Penjualan: daftar order (filter outlet/perangkat/kasir/tanggal/flag), detail, cetak ulang struk PDF (server-side).
- Laporan: penjualan harian/mingguan, laba kotor, rekap pajak bulanan, omzet bruto bulanan; ekspor CSV/XLSX.
- Outlet: profil, struk, kebijakan stok, pajak & service charge, perangkat (tambah/cabut/log sinkron/impor transaksi).
- Staf: undang via email, role, PIN, penugasan outlet.
- Perlu Perhatian: mutasi `rejected` dan order ber-flag.

**Panel super-admin `/admin` (SaaS only):** tenant (lihat, suspend/aktifkan, set plan), plan (CRUD limit).

**Registrasi (SaaS only):** `/register` → buat user owner + tenant + outlet pertama → wizard onboarding (porting dari LaciPOS).

**Role:**

| Kemampuan | owner | manager | cashier |
|---|---|---|---|
| Back-office: semua | ✓ | — | — |
| Back-office: katalog, inventori, laporan, perangkat | ✓ | ✓ (outlet yang ditugaskan) | — |
| Back-office: staf, pajak, pengaturan tenant | ✓ | — | — |
| POS: transaksi | ✓ | ✓ | ✓ |
| POS: approve pembatalan | ✓ | ✓ | — |

---

## 9. Error Handling

| Situasi | Perilaku |
|---|---|
| Offline / timeout / 5xx | Outbox `pending`, retry backoff; kasir tetap berjualan |
| Respons push hilang | Retry dengan `mutation.id` sama → `duplicate` → selesai |
| `rejected` | Pindah ke Perlu Perhatian (POS + back-office); tidak dihapus |
| `401 device_revoked` | Sync berhenti, POS terkunci, ekspor transaksi tertunda tersedia |
| `426` | Banner update; outbox disimpan |
| `403 tenant_suspended` / `429` | Sync ditahan dengan pesan; transaksi lokal tetap tersimpan |
| IndexedDB penuh / persist ditolak | Peringatan; bersihkan riwayat > 30 hari yang sudah tersinkron |
| Printer gagal | Order tersimpan; cetak ulang dari History |
| Jam menyimpang > 5 menit | Peringatan; flag `clock_skew` |
| Validasi back-office | Pesan per field; 5xx menampilkan kode referensi |

---

## 10. Testing

### 10.1 Server (Pest, PostgreSQL 16 asli)

- Isolasi tenant per model tenant-scoped, lewat Eloquent dan raw query (membuktikan RLS).
- Test arsitektur `BelongsToTenant`.
- Sync: idempotensi, push parsial, flags, cursor + tombstone + reset, **transaksi commit terlambat tidak terlewat** (dua koneksi paralel), pencabutan device, protokol N−1, `X-Request-Id` di semua respons.
- Matrix CI `MITRA_MODE=saas|standalone`; di standalone route registrasi & admin tidak ada.
- Pajak & total: test vector bersama (§10.3).

### 10.2 POS (Vitest + fake-indexeddb + MSW)

- Unit `domain/`.
- `SyncEngine`: putus di tengah batch, `duplicate`, `rejected`, `401`, `426`; outbox tidak kehilangan entri; Web Locks mencegah siklus paralel.
- Checkout atomik.

### 10.3 Test vector bersama PHP ↔ JS

`tests/fixtures/totals/*.json` (±40 kasus): diskon item/order persen/nominal, service charge, PBJT/PPN eksklusif & inklusif, `service_in_tax_base`, pembulatan, varian dengan modifier. Pest dan Vitest membaca file yang sama.

### 10.4 E2E (Playwright)

1. Aktivasi → login PIN → `setOffline(true)` → 5 transaksi + 1 pembatalan → reload (tetap jalan) → online → back-office menampilkan 5 order (1 dibatalkan) dan stok sesuai.
2. Dua perangkat offline menjual stok terakhir → stok −1 + flag `stock_negative`.

### 10.5 Electron

Smoke test: aplikasi terbuka, `mitraBridge` tersedia, printer mock menerima byte ESC/POS. Hardware nyata via checklist rilis manual.

### 10.6 CI (GitHub Actions)

Pint, ESLint, Prettier → PHPStan level 6, `vue-tsc` → Pest (matrix mode, service Postgres 16 + Redis) → Vitest → Playwright → build PWA + Electron.

---

## 11. Risiko & Verifikasi Terbuka

| Risiko | Mitigasi |
|---|---|
| Kompatibilitas paket dengan Laravel 13 (Inertia, Sanctum, Horizon) | Diverifikasi di langkah pertama plan; pin versi |
| Browser menghapus IndexedDB | `storage.persist()`, peringatan, Electron untuk outlet kritis |
| PIN 6 digit bisa di-brute-force dari perangkat curian | PIN hanya membuka perangkat terpercaya; pencabutan device; lockout |
| Tafsiran pajak berbeda per daerah | Semua parameter konfigurable per outlet; validasi ke konsultan pajak sebelum rilis |
| HTTPS di LAN untuk PWA Standalone | Caddy + Let's Encrypt atau CA lokal; Electron sebagai alternatif |
| WebUSB tidak tersedia di Safari/iOS | Fallback `window.print()` |

---

## 12. Sub-proyek & Urutan Implementasi

1. **Fondasi server** — Laravel 13, Postgres + RLS, tenancy, mode, auth, request context & logging.
2. **Katalog & back-office inti** — tema Edinburgh, outlet, staf, katalog, pajak, perangkat.
3. **Sync API** — aktivasi, pull, push, flags, Log Sinkron.
4. **POS Client (PWA)** — Dexie, SyncEngine, checkout, struk, Service Worker.
5. **Electron shell** — bridge, printer ESC/POS, auto-update.
6. **Laporan & Standalone packaging** — laporan pajak/penjualan, Docker Compose + Caddy, `mitra:install`.

Setiap sub-proyek mendapat plan implementasi sendiri.

---

## Lampiran: Referensi

- Tema: <https://github.com/SpykApp/theme-edinburgh> (MIT)
- PMK 131/2024 — <https://www.pajak.go.id/en/node/113453>
- PPN 2026 — <https://news.ddtc.co.id/berita/nasional/1812992/tak-berubah-tarif-ppn-12-tetap-berlaku-untuk-barang-mewah-di-2026>
- PBJT pasca UU HKPD — <https://ortax.org/pbjt-makanan-minuman>
- Restoran objek PBJT tidak kena PPN — <https://news.ddtc.co.id/ingat-makan-di-restoran-jadi-objek-pajak-daerah-tak-kena-ppn-11-39211>
- Ambang omzet PBJT per daerah — <https://news.ddtc.co.id/berita/daerah/1809267/omzet-belum-lewat-rp5-jutabulan-restoran-kini-tak-perlu-pungut-pajak>
- PPh Final UMKM hingga 2029 — <https://ikpi.or.id/en/pemerintah-perpanjang-pph-final-05-untuk-umkm-hingga-2029/>
- Laravel Context — <https://laravel.com/docs/13.x/context>
- W3C Trace Context — <https://www.w3.org/TR/trace-context-1/>
- RFC 9457 — <https://www.rfc-editor.org/rfc/rfc9457.html>
