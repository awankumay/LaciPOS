# Mitra POS — Design Spec Fase 2

- **Tanggal:** 2026-10-06
- **Status:** Draft — menunggu review
- **Bergantung pada:** [Spec Fase 1](2026-10-05-mitra-pos-design.md)
- **Scope Fase 2:** (1) Billing & langganan, (4) Operasional offline tambahan, (6) Standalone lanjutan, (7) Observability

---

## 1. Ringkasan

Fase 2 mengubah Mitra POS dari "produk yang bisa di-deploy" menjadi **bisnis yang bisa dijalankan**:

- Tenant SaaS bisa berlangganan paket berjenjang (Free/Basic/Pro/Business) dan membayar lewat invoice Xendit.
- Pelanggan Standalone bisa membeli lisensi langganan atau **jual putus (perpetual)**, menginstal, memperbarui, mem-backup, dan memindahkan data antara Standalone dan SaaS.
- Kasir mendapat **shift + rekap kas (blind close)** dan **restock/adjustment stok** yang bisa dilakukan offline.
- Tim kita mendapat **tracing, metrik, dan alert** lintas aplikasi.

Semua logika bisnis ini (pelanggan, billing, lisensi, rilis) dipisah ke aplikasi baru **`mitra-hub`**, sehingga `mitra-pos` tetap menjadi satu produk dengan satu image untuk SaaS dan Standalone.

### 1.1 Kriteria sukses

1. Tenant SaaS baru mendapat trial 14 hari, menerima invoice H-7, membayar via QRIS/VA/e-wallet/kartu, dan entitlement-nya aktif di POS tanpa intervensi manual.
2. Tenant yang tidak membayar turun ke paket Free otomatis; tidak ada data yang dikunci atau dihapus, dan kasir tidak pernah berhenti bertransaksi karena tagihan.
3. Pelanggan Standalone bisa menginstal dengan satu skrip, mengaktifkan lisensi online maupun offline, dan memperbarui server dengan rollback otomatis bila gagal.
4. Data satu tenant bisa dipindah Standalone → SaaS dan SaaS → Standalone tanpa kehilangan transaksi (dibuktikan oleh tes round-trip).
5. Kasir bisa membuka shift, mencatat kas masuk/keluar, dan menutup shift secara blind close sepenuhnya offline; angka di back-office identik dengan laporan shift tercetak.
6. Satu `trace_id` menghubungkan POS → `mitra-pos` → job → `mitra-hub` → webhook Xendit.

### 1.2 Keputusan (hasil brainstorming)

| # | Keputusan | Alasan |
|---|---|---|
| E1 | **MitraSource = `mitra-pos` + `mitra-hub`** — billing, lisensi, dan Platform Admin di aplikasi terpisah | Pola industri (GitLab CustomersDot, portal lisensi Mattermost); kunci privat lisensi tidak pernah berdekatan dengan kode yang didistribusikan |
| E2 | **Satu image Docker `mitra-pos`** untuk SaaS dan Standalone | Pola GitLab/Mattermost; satu artefak untuk diuji dan dirilis |
| E3 | Pelanggan Standalone **hanya menginstal `mitra-pos`** | `mitra-hub` hanya dioperasikan kita |
| E4 | Model harga: **paket berjenjang per tenant**, bulanan/tahunan | Mudah dipahami UMKM; cocok dengan `limits` Fase 1 |
| E5 | **Paket Free tetap cloud**, dibatasi skala & fitur (bukan local-only) | Arsitektur tetap satu; upgrade instan tanpa migrasi; local-only bertentangan dengan D5 Fase 1 |
| E6 | Tidak bayar → **downgrade ke Free**, bukan suspend; `suspended` hanya untuk pelanggaran (manual) | Usaha pelanggan tidak boleh berhenti karena tagihan |
| E7 | **Trial 14 hari** untuk registrasi SaaS | Disetujui |
| E8 | Billing **berbasis invoice** (bukan auto-debit) dengan **Xendit** sebagai gateway pertama, di balik interface `PaymentGateway` | QRIS/VA/e-wallet tidak bisa auto-debit; Invoice API Xendit + webhook paid/expired cocok |
| E9 | Lisensi Standalone: **`subscription`** (masa tenggang 30 hari → mode terbatas) dan **`perpetual`** (tanpa kedaluwarsa, update 1 tahun via `updates_until`) | Mengakomodasi model jual putus |
| E10 | Lisensi = file JSON bertanda tangan **Ed25519**, terikat `instance_id`, aktivasi online maupun offline | Bisa diverifikasi tanpa jaringan; tidak bisa dipalsukan |
| E11 | Migrasi data **dua arah** via paket `.mitrapkg` | Jaminan "data milik pelanggan"; skema identik di kedua mode |
| E12 | Shift: **satu shift per perangkat**, **blind close**, ambang selisih → PIN manajer | Standar POS modern; mengurangi manipulasi angka |
| E13 | Stok offline: **restock & adjustment sebagai delta**; stock opname tetap di luar scope | Delta bebas konflik; angka absolut rawan saat offline |
| E14 | Observability: **OpenTelemetry + Grafana stack** untuk server kita; mati secara default di Standalone | Melanjutkan fondasi `traceparent` Fase 1 |

### 1.3 Di luar scope Fase 2

- Klien Android (Flutter).
- Stok bahan baku/resep (BOM), stok per varian, transfer antar-outlet, stock opname.
- Integrasi e-Faktur/Coretax dan e-Tax Bapenda; penerbitan faktur pajak atas langganan.
- Auto-debit kartu/e-wallet (struktur `PaymentGateway` sudah siap).
- Adapter Midtrans.
- Fitur penjualan lanjutan: pelanggan & loyalti, split bill, open bill/meja, QRIS dinamis di kasir.
- Importer LaciPOS → `mitra-pos` (dicatat sebagai jalur akuisisi di masa depan).
- Add-on berbayar per modul.

---

## 2. Perubahan terhadap Fase 1

| Fase 1 | Fase 2 |
|---|---|
| Panel `/admin` super-admin dan tabel `plans` di `mitra-pos` (mode SaaS) | Pindah ke `mitra-hub`. `mitra-pos` tidak lagi punya panel super-admin maupun tabel `plans` |
| `PlanLimiter` membaca `tenants.plan_id` → `plans.limits` | Diganti interface **`Entitlement`** (§5); `tenants.plan_id` dihapus |
| `tenants.status active\|suspended` diatur admin lokal | Status mengikuti entitlement (`trialing\|active\|past_due\|downgraded\|suspended\|limited\|migrated_out`) |
| Tenant `suspended` → sync ditahan (`403 tenant_suspended`) | Sync **tetap menerima transaksi** di semua status kecuali `migrated_out`; pembatasan hanya di back-office dan pembuatan transaksi baru di outlet `read_only` |
| Registrasi tenant hanya lokal | Juga mendaftarkan customer ke hub (asinkron, retry) |
| Route registrasi & admin dikondisikan per mode | Registrasi tetap di `mitra-pos` (mode SaaS); admin tidak ada lagi. Perbedaan mode: `TenantResolver`, sumber `Entitlement`, aktif/tidaknya `Updater` |
| Distribusi Standalone: Docker Compose | Ditambah `install.sh`, backup bawaan, updater, lisensi |
| Sync v1: `order.create`, `order.cancel` | Ditambah `shift.open`, `shift.cash_move`, `shift.close`, `stock.adjust` + `capabilities` di pull (additive, tetap v1) |
| `traceparent` diterima & dicatat | Diteruskan penuh dan diekspor via OpenTelemetry |

---

## 3. Arsitektur MitraSource

### 3.1 Topologi

```
┌────────────────────────────────────────────┐
│ mitra-hub  (hub.mitra-pos.id, hanya kita)  │
│ • Platform Admin  /admin                   │
│ • Portal pelanggan /portal                 │
│ • Billing (Xendit + webhook)               │
│ • License API /license/v1/*                │
│ • Katalog rilis                            │
│ • Kunci privat Ed25519                     │
└──────────▲─────────────────────▲───────────┘
           │ entitlement (HMAC)  │ cek lisensi mingguan,
           │                     │ katalog rilis
┌──────────┴─────────────┐  ┌────┴──────────────────────┐
│ mitra-pos (SaaS)       │  │ mitra-pos (Standalone)    │
│ MITRA_MODE=saas        │  │ MITRA_MODE=standalone     │
│ image mitra-pos:1.x    │  │ image mitra-pos:1.x (sama)│
│ back-office, POS PWA,  │  │ back-office, POS PWA,     │
│ Sync API               │  │ Sync API, Updater, Backup │
└──────────▲─────────────┘  └────▲──────────────────────┘
           │                     │
     PWA / Electron         PWA / Electron
```

### 3.2 Struktur repo

```
mitra-pos/
├─ app/
│  ├─ Domain/
│  │  ├─ Tenancy/ Catalog/ Sales/ Inventory/ Devices/        (Fase 1)
│  │  ├─ Shifts/        Shift, CashMovement, ShiftCalculator
│  │  └─ Entitlements/  Entitlement (interface), EntitlementData,
│  │                    HubEntitlementSource, LicenseEntitlementSource
│  ├─ Licensing/        LicenseFile, LicenseVerifier (public key), LicenseStatus,
│  │                    TrustedClock, LicenseChecker (job mingguan)
│  ├─ Hub/              HubClient (HMAC), InternalHubController (/internal/hub/v1/*)
│  ├─ Updater/          ReleaseChecker, Preflight, UpdateRunner, Rollback
│  ├─ Backup/           BackupRunner, RestoreRunner
│  ├─ Migration/        TenantExporter, TenantImporter, PackageFormat
│  ├─ Http/Web/  Http/Api/V1/
│  └─ Console/          mitra:install, mitra:license:activate, mitra:license:request,
│                       mitra:update, mitra:backup, mitra:restore,
│                       mitra:tenant:export, mitra:tenant:import
├─ resources/js/  backoffice/  pos/  shared/
├─ electron/
├─ docker/
│  ├─ Dockerfile                    (satu image)
│  ├─ compose.saas.yml
│  ├─ compose.standalone.yml        (app, horizon, scheduler, postgres, redis, caddy)
│  └─ install.sh
├─ keys/license-public/*.pem        (per key_id; kunci privat tidak pernah di repo)
└─ tests/  … + fixtures/shifts/*.json, contracts/hub/*.json

mitra-hub/
├─ app/
│  ├─ Customers/     Customer
│  ├─ Billing/       Plan, Price, Subscription, Invoice, Payment, WebhookEvent,
│  │                 PaymentGateway (interface), XenditGateway, SubscriptionLifecycle,
│  │                 Proration, InvoiceReconciler
│  ├─ Licensing/     License, LicenseCheck, LicenseSigner, LicenseIssuer
│  ├─ Releases/      Release
│  ├─ Provisioning/  PosClient (HMAC), PushEntitlement, TenantRegistrationHandler
│  └─ Http/          Admin/, Portal/, Api/License/V1/, Api/Pos/V1/, Webhooks/Xendit
├─ resources/js/  admin/  portal/  shared/ (tema Edinburgh)
├─ docker/
└─ tests/  … + contracts/hub/*.json (salinan yang sama dengan mitra-pos)
```

Stack `mitra-hub` sama dengan `mitra-pos`: Laravel 13, Inertia + Vue 3 + Tailwind v4, PostgreSQL 16, Redis + Horizon, tema Edinburgh.

### 3.3 Kontrak hub ↔ pos

Semua request ditandatangani HMAC-SHA256 dengan header `X-Mitra-Timestamp` (toleransi ±5 menit, anti-replay) dan `X-Mitra-Signature`; membawa `traceparent` dan `X-Request-Id`.

| Arah | Endpoint | Kegunaan |
|---|---|---|
| hub → pos (SaaS) | `PUT /internal/hub/v1/tenants/{tenant}/entitlement` | Kirim entitlement baru (§5.1) |
| hub → pos (SaaS) | `POST /internal/hub/v1/tenants/{tenant}/suspend` · `/resume` | Pelanggaran (manual admin) |
| pos (SaaS) → hub | `POST /api/pos/v1/tenants` | Tenant baru terdaftar → hub membuat customer + langganan trial |
| pos (SaaS) → hub | `POST /api/pos/v1/tenants/{tenant}/usage` | Jumlah outlet/perangkat/produk/staf (harian, untuk validasi downgrade) |
| pos (Standalone) → hub | `POST /license/v1/activate` · `/check` | Aktivasi & cek lisensi |
| pos (Standalone) → hub | `GET /license/v1/releases` | Katalog rilis yang diizinkan lisensi |
| pos (Standalone) → hub | `POST /license/v1/telemetry` | Opt-in, default mati (§9.4) |

Skema JSON setiap payload disimpan di `tests/contracts/hub/*.json` dan diuji di **kedua** repo.

Bila hub tidak bisa dihubungi, `mitra-pos` tetap berjalan memakai entitlement/lisensi terakhir; panggilan keluar diantrekan dengan retry.

---

## 4. Billing (`mitra-hub`)

### 4.1 Paket (contoh awal, dikonfigurasi di hub)

| | Free | Basic | Pro | Business |
|---|---|---|---|---|
| Outlet | 1 | 1 | 3 | ∞ |
| Perangkat kasir | 1 | 2 | 10 | ∞ |
| Produk | 100 | ∞ | ∞ | ∞ |
| Staf | 2 | 5 | 20 | ∞ |
| Riwayat laporan di back-office | 30 hari | 1 tahun | ∞ | ∞ |
| Ekspor CSV/XLSX | — | ✓ | ✓ | ✓ |
| Laporan pajak | — | ✓ | ✓ | ✓ |
| Shift & rekap kas | — | ✓ | ✓ | ✓ |
| Stok offline (restock/adjustment dari POS) | — | ✓ | ✓ | ✓ |
| Offline-first + sync | ✓ | ✓ | ✓ | ✓ |

Angka dan harga hanya contoh; diubah lewat Platform Admin tanpa deploy.

### 4.2 Model data

| Tabel | Kolom kunci |
|---|---|
| `customers` | id, name, email, phone, npwp (nullable), type `saas\|standalone` |
| `plans` | id, code, name, limits jsonb, features jsonb, is_public, sort |
| `prices` | id, plan_id, edition `saas\|standalone`, license_type `subscription\|perpetual\|update_renewal`, period `monthly\|yearly\|once`, amount, is_active |
| `subscriptions` | id, customer_id, plan_id, price_id, edition, status `trialing\|active\|past_due\|downgraded\|suspended\|cancelled`, trial_ends_at, current_period_start, current_period_end, pos_tenant_id (SaaS), license_id (Standalone), cancel_at_period_end, scheduled_plan_id (downgrade) |
| `invoices` | id, number (`INV/YYYY/MM/NNNN`), customer_id, subscription_id, items jsonb, subtotal, ppn_amount, total, status `draft\|open\|paid\|expired\|void`, due_at, gateway, gateway_invoice_id, payment_url, paid_at |
| `payments` | id, invoice_id, gateway_payment_id, method, amount, payload jsonb, received_at |
| `webhook_events` | id (= id event gateway, unique), gateway, type, payload jsonb, processed_at, error |
| `credits` | id, customer_id, amount, reason, applied_invoice_id (dari konversi migrasi / kompensasi) |

### 4.3 Siklus langganan SaaS

```
registrasi ─► trialing (14 hari)
                │ invoice H-7 sebelum trial_ends_at
                ▼
        bayar ─► active ─► invoice H-7 sebelum current_period_end ─► bayar ─► active (periode diperpanjang)
                                     │
                                     └─ jatuh tempo, belum bayar ─► past_due (7 hari, fitur penuh + banner)
                                                                       │
                                                                       └─ tetap belum bayar ─► downgraded (entitlement Free)

downgraded/past_due ─ bayar invoice kapan pun ─► active (entitlement paket kembali)
admin (pelanggaran) ─► suspended ─► admin ─► status sebelumnya
```

- Trial habis tanpa bayar → langsung `downgraded` (tanpa `past_due`).
- Job harian `billing:generate-invoices` membuat invoice H-7; `billing:advance-lifecycle` memindahkan status sesuai tanggal.
- Pengingat email: H-7, H-3, H-0, H+3, dan saat downgrade.
- `cancel_at_period_end = true` → di akhir periode menjadi `downgraded` (bukan dihapus).

### 4.4 Upgrade & downgrade

- **Upgrade:** aktif segera; invoice prorata = (harga baru − harga lama) × sisa hari / hari periode, dibulatkan ke rupiah. Entitlement baru dikirim setelah invoice prorata **dibayar**; bila tidak dibayar dalam 3 hari, invoice `expired` dan paket tetap.
- **Downgrade:** dijadwalkan ke awal periode berikutnya (`scheduled_plan_id`). Ditolak bila `usage` terakhir melebihi limit paket tujuan; pesan menyebut outlet/perangkat/staf yang harus dinonaktifkan dulu.

### 4.5 Xendit

```php
interface PaymentGateway {
    public function createInvoice(Invoice $invoice): GatewayInvoice;          // id + payment_url + expiry
    public function fetchInvoice(string $gatewayInvoiceId): GatewayInvoiceStatus;
    public function parseWebhook(Request $request): GatewayEvent;             // verifikasi + normalisasi
}
```

- `XenditGateway` memakai Invoice API (satu payment link untuk QRIS, VA, e-wallet, kartu).
- Webhook `POST /webhooks/xendit`: verifikasi callback token → insert `webhook_events` (unik; duplikat diabaikan) → dalam satu transaksi DB: catat `payments`, invoice `paid`, perpanjang `subscriptions`, antrekan `PushEntitlement`.
- `InvoiceReconciler` (per jam) menanyakan status semua invoice `open` ke Xendit untuk menutup celah webhook terlambat/hilang.
- Invoice yang dibayar setelah `expired` (gateway tetap menerima) diproses normal dan dicatat untuk ditinjau.
- Biaya per metode (QRIS 0,7% sesuai ketentuan BI; VA biaya tetap) harus dicek ulang ke halaman tarif resmi Xendit sebelum produksi.

### 4.6 Portal pelanggan & Platform Admin

**Portal (`/portal`, login terpisah dari back-office `mitra-pos`, SSO di luar scope):** paket & masa aktif, upgrade/downgrade, invoice + bayar, unduh kuitansi PDF. Pelanggan Standalone: unduh/aktivasi lisensi (termasuk aktivasi offline), lihat rilis yang tersedia, beli perpanjangan update.

**Platform Admin (`/admin`, tim kita):** pelanggan, langganan (perpanjang manual, kredit, suspend/resume), invoice (void, tandai lunas manual untuk transfer bank langsung), paket & harga, lisensi (terbitkan, cabut, lihat `license_checks`), rilis (daftarkan versi, channel, `security_until`).

**PPN atas langganan:** dicatat di invoice bila badan usaha kita PKP (konfigurasi hub). Faktur pajak di luar scope.

---

## 5. Entitlement & Lisensi

### 5.1 Entitlement

```json
{
  "tenant_id": "01J…", "plan": "pro", "edition": "saas",
  "status": "active",
  "limits":   { "outlets": 3, "devices": 10, "products": null, "staff": 20, "report_history_days": null },
  "features": { "shifts": true, "export": true, "tax_report": true, "offline_stock": true },
  "valid_until": "2026-11-06T00:00:00+07:00",
  "issued_at": "2026-10-06T10:00:00+07:00",
  "version": 42
}
```

- `null` = tak terbatas. `version` monoton naik; payload dengan `version` lebih kecil dari yang tersimpan diabaikan.
- Disimpan di `mitra-pos` tabel `tenant_entitlements` (tenant_id PK, data jsonb, version, source `hub\|license\|default`, received_at).
- API domain: `Entitlement::for($tenant)->allows('shifts')`, `->limit('outlets')`, `->status()`.
- Tanpa data sama sekali → default paket **Free**.
- Dikirim ke POS lewat sync pull (`changes.entitlement`); POS memakai entitlement terakhir saat offline.

**Penegakan saat limit terlampaui (downgrade):**
- Owner memilih outlet yang tetap aktif (wizard di back-office, wajib sebelum menu lain bisa dipakai). Outlet lain → `outlets.status = read_only`; perangkatnya ikut.
- Sync push dari outlet `read_only` **tetap diterima** untuk transaksi dengan `client_created_at` sebelum perangkat menerima entitlement baru; transaksi setelahnya diterima dengan flag `created_while_read_only` (tidak ditolak, sesuai D12 Fase 1).
- POS di outlet `read_only` menonaktifkan tombol checkout setelah menerima entitlement baru, dengan pesan untuk menghubungi owner.
- Fitur nonaktif disembunyikan; datanya tetap tersimpan dan muncul kembali saat upgrade.
- `report_history_days` membatasi rentang tampilan laporan, tidak menghapus data.
- Perangkat/staf melebihi limit: yang paling baru dibuat dinonaktifkan sampai owner memilih ulang.

### 5.2 File lisensi Standalone

```json
{
  "payload": {
    "license_id": "LIC-01J…",
    "customer": "Toko Maju",
    "type": "subscription",
    "plan": "pro",
    "limits": {}, "features": {},
    "issued_at": "2026-10-06T10:00:00Z",
    "expires_at": "2027-10-06T00:00:00Z",
    "updates_until": "2027-10-06T00:00:00Z",
    "instance_id": "01J…",
    "version": 3
  },
  "signature": "base64(Ed25519(canonical_json(payload)))",
  "key_id": "2026-01"
}
```

- `type: perpetual` → `expires_at: null`; `updates_until` = tanggal beli + 1 tahun.
- `canonical_json`: kunci terurut, tanpa spasi, UTF-8 — implementasi yang sama di hub (signer) dan pos (verifier), diuji dengan fixture bersama.
- `mitra-pos` membawa public key per `key_id` di `keys/license-public/`; rotasi kunci tidak membatalkan lisensi lama.
- `instance_id` dibuat oleh `mitra:install` dan disimpan di DB + `storage/instance_id`.
- File disimpan di `storage/license.json`; isinya diterjemahkan menjadi entitlement (`source = license`).

**Aktivasi:**
- Online: `mitra:license:activate <KODE>` atau halaman Pengaturan → Lisensi di back-office → `POST /license/v1/activate { code, instance_id, app_version }` → file lisensi.
- Offline: `mitra:license:request` menghasilkan activation request (file + QR berisi `instance_id`, versi, tanda waktu) → pelanggan mengunggahnya di portal → mengunduh file lisensi → mengunggahnya ke back-office.

**Cek berkala:** job mingguan (jitter acak 0–24 jam) → `POST /license/v1/check { license_id, instance_id, version, app_version }` → hub mengembalikan file lisensi terbaru (perpanjangan, upgrade, pencabutan) atau `304`. Gagal terhubung → lisensi terakhir tetap berlaku; dicoba lagi dengan backoff.

**Status:**

| Kondisi | `subscription` | `perpetual` |
|---|---|---|
| Sebelum `expires_at` | `active` | `active` |
| ≤ 30 hari sebelum habis | `active` + banner | — |
| 0–30 hari setelah habis | `past_due` (fitur penuh + banner) | — |
| > 30 hari setelah habis | `limited` | — |
| Dicabut hub | `limited` | `limited` |
| Tidak ada file / tanda tangan invalid / `instance_id` tidak cocok | `limited` (entitlement Free) | `limited` (entitlement Free) |

`limited`: back-office baca-saja, tidak bisa mengaktivasi perangkat baru, entitlement turun ke Free; **POS dan sync tetap berjalan**.

**`TrustedClock`:** menyimpan `last_seen_time` tertinggi (diperbarui tiap jam dan saat cek lisensi berhasil memakai waktu server hub). Status lisensi dihitung dari `max(now, last_seen_time)`.

### 5.3 Pengelolaan lisensi di hub

- `licenses`: id, customer_id, subscription_id (nullable), type, plan_id, instance_id, expires_at, updates_until, revoked_at, version, last_check_at.
- `license_checks`: license_id, instance_id, app_version, ip, result, created_at — dipakai mendeteksi satu lisensi di banyak instance (`instance_id` berbeda → alert ke admin; tidak otomatis dicabut).
- Lisensi `subscription` mengikuti siklus §4.3 (dengan `edition = standalone`); `expires_at` = `current_period_end`.
- Perpetual dibeli via `prices.period = once`; **perpanjangan update** (`license_type = update_renewal`) menggeser `updates_until` +1 tahun dari nilai terbesar antara `updates_until` saat ini dan tanggal bayar.
- Upgrade paket perpetual: invoice selisih harga → lisensi baru (`version` +1) dengan limit baru.

---

## 6. Standalone: Instalasi, Backup, Update, Migrasi

### 6.1 Instalasi

**Prasyarat:** Linux x86_64 (Ubuntu 22.04/24.04 atau Debian 12), Docker Engine + Compose v2, RAM 2 GB, disk 20 GB.

`install.sh` (juga tersedia sebagai panduan manual):
1. Membuat `/opt/mitra-pos/` berisi `compose.yml`, `.env`, dan volume `data/`.
2. Wizard HTTPS: (a) domain publik + Let's Encrypt; (b) LAN + CA lokal Caddy (dengan panduan memasang root cert di tablet/HP); (c) hanya Electron, HTTP di LAN (PWA tidak didukung).
3. Membuat password DB/Redis dan `APP_KEY` acak.
4. `php artisan mitra:install`: migrasi, `instance_id`, tenant tunggal, outlet pertama, akun owner.
5. Aktivasi lisensi (opsional; tanpa lisensi berjalan sebagai Free/`limited`).

Mode non-interaktif (`install.sh --non-interactive --domain … --owner-email …`) untuk CI dan instalasi oleh mitra reseller.

### 6.2 Backup

- `mitra:backup` (terjadwal harian 02:00 waktu tenant): `pg_dump` + file upload → arsip terkompresi dan terenkripsi (kunci di `.env`) → `data/backups/`, retensi 14 hari.
- Tujuan tambahan opsional: S3-compatible, dikonfigurasi di back-office.
- `mitra:restore <file>`.
- Back-office menampilkan status backup terakhir; peringatan bila > 48 jam.
- Mode SaaS memakai backup infrastruktur kita; perintah yang sama tersedia tapi tidak dijadwalkan.

### 6.3 Update

**Hub `releases`:** version (semver), released_at, channel `stable\|beta`, image_digest, notes, min_from_version, security_until.

**`mitra:update`** (dari back-office atau CLI; otomatis terjadwal bila diaktifkan):
1. `GET /license/v1/releases?current=…&instance_id=…` → hanya versi dengan `released_at ≤ updates_until`, atau rilis keamanan yang `security_until ≥ hari ini` dan berada di minor yang sama dengan versi terpasang. Versi lain ditandai "perlu perpanjangan update".
2. Pre-flight: ruang disk, `min_from_version` (loncatan terlalu jauh → bertahap), peringatan bila ada perangkat dengan outbox besar.
3. Backup otomatis (wajib).
4. Pull image berdasarkan **digest**.
5. Mode maintenance hanya untuk back-office; Sync API membalas `503 + Retry-After`; POS tetap berjualan offline.
6. `php artisan migrate --force` → health check (`/up`, DB, versi skema).
7. Gagal → rollback ke image sebelumnya + restore backup langkah 3; error dicatat (dan dikirim ke hub bila telemetri diizinkan).

**Aturan rilis:**
- Migrasi DB dalam satu versi minor hanya additive (expand → contract minimal dua rilis kemudian), supaya rollback image aman.
- Server mendukung sync protocol N dan N−1 (Fase 1). PWA ter-update bersama server; Electron memakai auto-update sendiri.

Security patch untuk lisensi perpetual: diberikan untuk versi yang dirilis dalam 2 tahun terakhir (`security_until` = `released_at` + 2 tahun).

### 6.4 Migrasi tenant (dua arah)

**Format `.mitrapkg`:**
```
manifest.json      app_version, schema_version, tenant_id, row_counts per tabel, created_at, source_mode
data/*.jsonl       satu file per tabel, urutan sesuai dependensi FK
files/             logo, foto produk
checksums.sha256
```
Seluruh arsip dienkripsi AES-256-GCM dengan passphrase acak yang ditampilkan sekali saat ekspor.

**Alur:**
1. **Pra-ekspor** (wizard owner di back-office): daftar perangkat dengan outbox > 0 atau tidak sinkron > 1 jam; owner bisa memaksa lanjut dengan konfirmasi tertulis.
2. **Ekspor:** tenant masuk status `migrated_out` → back-office baca-saja; sync push membalas `409 tenant_migrated` + `target_url` (bila diketahui); paket dibuat.
3. **Impor** (`mitra:tenant:import` atau wizard): verifikasi checksum + passphrase + versi skema (paket versi lama dimigrasikan dulu) → impor dalam satu transaksi DB.
   - ULID dipertahankan; nomor order dan referensi tidak berubah.
   - SaaS: membuat tenant baru (ditolak bila `tenant_id` sudah ada).
   - Standalone: mengisi tenant tunggal yang **belum punya transaksi**; selain itu ditolak.
4. **Perangkat:** token lama tidak berlaku; owner mengaktivasi ulang perangkat ke URL server baru; POS melakukan pull penuh. Outbox yang tersisa (bila owner memaksa lanjut) bisa diekspor dari POS ke JSON dan diimpor di server baru (fitur Fase 1).
5. **Hub:** mencatat perpindahan.
   - Standalone → SaaS: lisensi `subscription` dicabut; sisa masa aktif dikonversi ke `credits`; langganan SaaS dibuat. Lisensi `perpetual` dicabut tanpa kredit otomatis (keputusan komersial, ditangani manual).
   - SaaS → Standalone: langganan SaaS dibatalkan; sisa masa aktif menjadi `credits` untuk lisensi Standalone.

**Tidak ikut migrasi:** token perangkat, sesi, log sinkron (`sync_runs`, `sync_mutations`), cache, job antrean, entitlement (dibuat ulang dari sumber tujuan).

---

## 7. Shift & Rekap Kas

### 7.1 Aturan

- Satu shift `open` per perangkat. Pergantian kasir di tengah shift diperbolehkan; setiap order tetap mencatat `user_id`.
- Pengaturan outlet: `require_shift` (default `true` bila fitur `shifts` aktif), `shift_variance_threshold` (default Rp10.000), `cash_out_approval_threshold` (default Rp100.000).
- Hanya tersedia bila entitlement `features.shifts = true`.
- Sepenuhnya bisa offline.

### 7.2 Data

| Tabel | Kolom kunci |
|---|---|
| `shifts` | id (ULID klien), tenant_id, outlet_id, device_id, opened_by, opened_at, opening_float, closed_by, closed_at, counted_cash, counted_breakdown jsonb, expected_cash (snapshot klien), difference, close_note, approved_by, status `open\|closed\|force_closed`, force_closed_by, flags jsonb |
| `cash_movements` | id (ULID klien), tenant_id, shift_id, type `in\|out`, amount, reason `owner_deposit\|operational\|add_change\|other`, note, user_id, approved_by, created_at |
| `orders` | + `shift_id` (nullable) |
| `order_cancellations` | + `shift_id` (shift tempat pembatalan dicatat) |

### 7.3 Kas seharusnya

```
expected_cash = opening_float
              + Σ (cash_received − change_amount)   order tunai berstatus completed di shift ini
              − Σ total order tunai yang dibatalkan  pembatalan yang dicatat di shift ini
              + Σ cash_movements.in
              − Σ cash_movements.out
```

- Pembatalan order dari shift sebelumnya mengurangi laci shift saat ini.
- Non-tunai hanya direkap per metode bayar (untuk dicocokkan dengan mutasi bank/EDC).
- `ShiftCalculator` (PHP) dan `shift.ts` (JS) diuji dengan `tests/fixtures/shifts/*.json` yang sama.

### 7.4 Alur

1. **Buka shift:** kasir memasukkan modal awal → `shift.open`.
2. **Kas masuk/keluar:** jumlah + alasan; `out` di atas `cash_out_approval_threshold` butuh PIN manajer → `shift.cash_move`.
3. **Tutup shift (blind close):**
   1. Kasir menghitung uang (total, atau per pecahan Rp100.000 … koin); angka seharusnya tidak ditampilkan.
   2. POS menghitung `difference = counted_cash − expected_cash`.
   3. `|difference| > shift_variance_threshold` → PIN manajer + catatan wajib; flag `shift_variance`.
   4. Laporan shift dicetak (ESC/POS) dan bisa dicetak ulang: waktu buka/tutup, kasir yang bertransaksi, modal awal, penjualan per metode bayar, jumlah transaksi, pembatalan, kas masuk/keluar, seharusnya vs dihitung, selisih.
   5. `shift.close` masuk outbox.
4. **Paksa tutup** (back-office, manajer/owner) untuk perangkat hilang/rusak: status `force_closed`, `counted_cash = null`. Bila perangkat kemudian mengirim `shift.close`, data hitung disimpan, status tetap `force_closed`, flag `closed_after_force`.
5. Order tanpa shift padahal `require_shift = true` (mis. pengaturan berubah saat perangkat offline): tetap diterima dengan flag `order_outside_shift`.

### 7.5 Back-office

- Daftar shift per outlet/perangkat/kasir, filter "ada selisih", detail, paksa tutup.
- Laporan selisih kas per kasir per periode.

---

## 8. Stok Offline

- Restock dan adjustment dari POS, wajib PIN manajer, hanya untuk produk `track_stock = true`, hanya bila `features.offline_stock = true`.
- Input: produk, delta (+/−), alasan `restock\|damaged\|lost\|correction`, catatan.
- Klien: `stock_deltas` lokal langsung diperbarui (proyeksi stok berubah seketika).
- Server: `stock_movements` ditambahkan apa adanya (`reason` dipetakan: `restock` → `restock`, lainnya → `adjustment` dengan `note` berisi alasan rinci) dan diproyeksikan ke `outlet_stocks`.
- Riwayat stok di back-office menampilkan perangkat dan manajer yang menyetujui.
- Stock opname (angka absolut) tetap di luar scope.

---

## 9. Sync Protocol v1 — Tambahan

### 9.1 Mutasi baru

| Tipe | Payload | Validasi struktural |
|---|---|---|
| `shift.open` | `shift` (id, opening_float, opened_by, opened_at) | device & outlet cocok; id belum ada |
| `shift.cash_move` | `cash_movement` | shift ada dan milik device yang sama |
| `shift.close` | `shift_id` + data penutupan | shift ada; bila `force_closed` → `applied` + flag `closed_after_force` |
| `stock.adjust` | `stock_movements[]` + `approved_by` | produk ada dan `track_stock = true` |

Aturan Fase 1 tetap berlaku: idempotent per `mutation.id`, satu transaksi DB per mutasi, aturan bisnis tidak menyebabkan penolakan.

### 9.2 Capabilities

Respons pull menambahkan:
```json
{ "capabilities": ["order.create", "order.cancel", "shift.open", "shift.cash_move", "shift.close", "stock.adjust"],
  "changes": { "entitlement": { } } }
```
POS hanya menampilkan fitur yang ada di `capabilities` **dan** diizinkan entitlement. Mutasi yang tetap terkirim ke server yang tidak mendukungnya → `rejected: unsupported_mutation` → Perlu Perhatian.

### 9.3 Urutan & referensi

Outbox mengirim sesuai urutan pembuatan (`shift.open` → order → `shift.close`). Bila order tiba sebelum shift-nya (seharusnya tidak terjadi), order tetap `applied`; `shift_id` disimpan dan relasi terselesaikan saat shift tiba (tanpa FK keras pada `orders.shift_id`).

### 9.4 Flag baru

`shift_variance`, `closed_after_force`, `order_outside_shift`, `created_while_read_only`.

---

## 10. Observability

### 10.1 Tracing

- OpenTelemetry PHP SDK + instrumentasi Laravel (HTTP masuk, query DB, queue, HTTP client) di `mitra-pos` dan `mitra-hub`, diekspor via OTLP ke OpenTelemetry Collector.
- `traceparent` diteruskan: POS → `mitra-pos` → job → hub → (webhook Xendit diproses) → hub → `mitra-pos`.
- `request_id`, `tenant_id`, `device_id`, `mutation_id` menjadi atribut span.
- Backend untuk server kita: Grafana Tempo (trace), Loki (log), Prometheus/Mimir (metrik).
- Standalone: OTel mati secara default; bisa diarahkan ke collector milik pelanggan via `.env`.

### 10.2 Metrik

| Metrik | Aplikasi |
|---|---|
| `sync_push_mutations_total{status,type}` | pos |
| `sync_push_duration_seconds`, `sync_pull_duration_seconds` | pos |
| `device_outbox_size` (dari header `X-Outbox-Size` di push) | pos |
| `device_last_sync_age_seconds` | pos |
| `orders_flagged_total{flag}` | pos |
| `entitlement_updates_total{source,result}` | pos |
| `hub_webhook_events_total{gateway,status}` | hub |
| `hub_invoice_reconcile_mismatch_total` | hub |
| `entitlement_push_failures_total` | hub |
| `license_checks_total{result}` | hub |

Diekspos via OTLP dan endpoint `/metrics` internal (tidak publik).

### 10.3 Alert (Grafana Alerting → email/Telegram tim)

- Rasio `rejected` > 1% dalam 15 menit.
- `orders_flagged_total{flag="totals_mismatch"}` bertambah dalam 1 jam.
- Perangkat dengan outbox > 0 dan tidak sinkron > 24 jam (juga email ke owner tenant).
- Webhook Xendit gagal diproses > 5 kali beruntun; `hub_invoice_reconcile_mismatch_total` > 0.
- `entitlement_push_failures_total` naik terus 30 menit.
- Antrean Horizon tertahan > 10 menit.
- Satu `license_id` terlihat dari > 1 `instance_id`.

### 10.4 Telemetri Standalone (opt-in)

Default mati. Bila diaktifkan owner: versi aplikasi, status backup terakhir, jumlah outlet/perangkat, hasil update terakhir. Tidak ada data transaksi, pelanggan, atau produk.

---

## 11. Error Handling (tambahan)

| Situasi | Perilaku |
|---|---|
| Hub tidak bisa dihubungi dari pos | Entitlement/lisensi terakhir berlaku; panggilan keluar diantrekan + retry |
| Webhook Xendit terlambat/hilang | `InvoiceReconciler` per jam |
| Webhook ganda | `webhook_events.id` unik, diproses sekali |
| Provisioning hub → pos gagal | Retry backoff + metrik + alert; `version` mencegah urutan terbalik |
| Invoice dibayar setelah expired | Diproses normal + dicatat untuk ditinjau |
| Update Standalone gagal | Rollback image + restore backup otomatis |
| Impor `.mitrapkg` gagal | Satu transaksi DB; tidak ada perubahan parsial |
| Push ke tenant `migrated_out` | `409 tenant_migrated` + `target_url`; POS menampilkan instruksi aktivasi ulang |
| Mutasi tidak didukung | `rejected: unsupported_mutation` → Perlu Perhatian |
| Jam server Standalone dimundurkan | `TrustedClock` |
| File lisensi rusak/dihapus | `limited` + banner; aktivasi ulang tersedia |

---

## 12. Testing

### 12.1 `mitra-hub` (Pest, PostgreSQL)

- Siklus langganan lengkap dengan `travelTo`: trial → invoice H-7 → paid → active; tidak bayar → past_due → downgraded; trial habis → downgraded; bayar saat downgraded → active.
- Webhook: token salah ditolak; event ganda diproses sekali; pembayaran setelah expired.
- Rekonsiliasi: invoice `paid` di Xendit (fake) tapi `open` di hub → diperbaiki.
- Prorata upgrade; downgrade ditolak saat `usage` melebihi limit.
- Lisensi: tanda tangan valid/invalid, rotasi `key_id`, perpetual, perpanjangan update, katalog rilis tersaring, deteksi multi-instance.
- Xendit di-fake via `Http::fake()` + fixture payload webhook.

### 12.2 `mitra-pos` (tambahan)

- `Entitlement`: satu dataset kasus diuji untuk sumber hub dan sumber lisensi; hasil harus identik.
- Status lisensi di setiap titik waktu (sebelum habis, H-30, masa tenggang, limited, perpetual, dicabut, invalid), termasuk jam dimundurkan.
- Downgrade: outlet `read_only`, flag `created_while_read_only`, wizard pemilihan outlet.
- Shift: test vector bersama PHP ↔ JS; paksa tutup + `closed_after_force`; pembatalan lintas shift; `order_outside_shift`.
- `stock.adjust`; negosiasi `capabilities`.
- Migrasi: round-trip ekspor → impor → ekspor ulang menghasilkan data identik; impor ke tenant yang sudah bertransaksi ditolak; checksum/passphrase salah ditolak; paket versi skema lama.
- Updater: pre-flight menolak loncatan versi; rollback saat migrasi gagal (image uji).
- Kontrak hub: payload divalidasi terhadap `tests/contracts/hub/*.json`.

### 12.3 POS Client (Vitest)

- `shift.ts` dengan fixture bersama.
- UI fitur mengikuti `capabilities` × entitlement.
- Outbox menjaga urutan `shift.open` → order → `shift.close`.

### 12.4 E2E (Playwright)

1. Offline: buka shift → 10 transaksi tunai/QRIS → kas keluar → pembatalan → blind close dengan selisih di atas ambang (PIN manajer) → online → back-office menampilkan angka identik dengan laporan shift.
2. Registrasi SaaS → trial → bayar invoice (Xendit fake) → entitlement Pro sampai ke POS lewat pull → menu Shift muncul.
3. Downgrade: tenant Pro 3 outlet → tidak bayar → downgraded → pilih 1 outlet → POS outlet lain menonaktifkan checkout.
4. Standalone: `install.sh --non-interactive` (Docker-in-Docker) → aktivasi lisensi via file → transaksi → ekspor `.mitrapkg` → impor ke instance SaaS → aktivasi ulang perangkat → riwayat order lengkap.

### 12.5 CI

Pipeline Fase 1 ditambah:
- Job `mitra-hub` (lint, PHPStan, Pest).
- Contract test hub ↔ pos (kedua repo memvalidasi skema yang sama; perubahan skema wajib di kedua repo).
- Smoke Standalone: build image tunggal → `install.sh --non-interactive` → health check → update ke image berikutnya → rollback.

---

## 13. Risiko & Verifikasi Terbuka

| Risiko | Mitigasi |
|---|---|
| Biaya & fitur Xendit berubah | Cek halaman tarif dan dokumentasi resmi sebelum produksi; `PaymentGateway` memungkinkan ganti gateway |
| Dua aplikasi menambah beban operasional | Stack identik; contract test; hub gagal tidak menghentikan pos |
| Lisensi dibajak (file disalin ke server lain) | `instance_id` + deteksi multi-instance di `license_checks` |
| Pelanggan Standalone tidak pernah update | Banner rilis; security patch 2 tahun; telemetri opt-in |
| Rollback gagal karena migrasi tidak additive | Aturan expand/contract dicek saat review rilis; smoke test update + rollback di CI |
| Kebijakan downgrade membuat pelanggan kecewa | Data tidak pernah dihapus; pemilihan outlet oleh owner; upgrade instan |
| Paket ekspor berisi data sensitif | Enkripsi AES-256-GCM + passphrase sekali tampil |
| Tafsiran PPN atas langganan | Validasi ke konsultan pajak sebelum menagih |

---

## 14. Sub-proyek & Urutan Implementasi

1. **Fondasi `mitra-hub`** — repo, customers, plans/prices, Platform Admin dasar, kontrak hub ↔ pos + contract test.
2. **Entitlement di `mitra-pos`** — `tenant_entitlements`, interface `Entitlement`, endpoint internal, pengiriman lewat pull, penegakan limit & `read_only`; hapus `plans`/`PlanLimiter` Fase 1.
3. **Billing** — subscriptions, invoices, Xendit, webhook, rekonsiliasi, siklus langganan, portal pelanggan.
4. **Shift & stok offline** — tabel, mutasi sync, `capabilities`, UI POS, laporan shift, back-office.
5. **Lisensi Standalone** — signer/verifier Ed25519, aktivasi online/offline, cek berkala, `TrustedClock`.
6. **Distribusi Standalone** — `install.sh`, backup/restore, katalog rilis, updater + rollback.
7. **Migrasi tenant** — `.mitrapkg`, ekspor/impor, alur hub.
8. **Observability** — OpenTelemetry, metrik, collector + Grafana, alert, telemetri opt-in.

Sub-proyek 4 hanya bergantung pada 2 (entitlement) dan bisa dikerjakan paralel dengan 3. Setiap sub-proyek mendapat plan implementasi sendiri.

---

## Lampiran: Referensi

- Spec Fase 1 — [2026-10-05-mitra-pos-design.md](2026-10-05-mitra-pos-design.md)
- GitLab CE/EE satu codebase — <https://help.creoline.com/en/doc/differences-between-gitlab-ce-and-ee-ZKcHjRqx08>
- GitLab license file — <https://devecorridor.iit.cnr.it/gitlab/help/user/admin_area/license.md>
- GitLab Cloud Licensing & CustomersDot — <https://handbook.gitlab.com/handbook/support/license-and-renewals/license-and-renewals-glossary>
- Metabase open-core (`enterprise/`) — <https://www.metabase.com/blog/opening-metabase-enterprise>
- Mattermost self-hosted subscriptions — <https://docs.mattermost.com/product-overview/self-hosted-subscriptions.html>
- Mattermost subscription overview (grace period, downgrade) — <https://docs.mattermost.com/product-overview/subscription.html>
- Xendit webhook — <https://docs.xendit.co/apidocs/set-webhook-url.md>
- Midtrans Payment Link & Subscription — <https://docs.midtrans.com/docs/payment-link-1>
- Perbandingan biaya Xendit vs Midtrans 2026 — <https://www.pytagotech.com/blog/integrasi-payment-gateway-lokal-midtransxendit-yang-aman>
- Pasar aplikasi kasir Indonesia 2026 — <https://inticore.co.id/aplikasi-kasir-terbaik-gratis-berbayar/>, <https://founderplus.id/blog/aplikasi-kasir-pos-ukm-terbaik/>
