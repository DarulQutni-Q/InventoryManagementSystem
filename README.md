# Inventory Management System
**Mini Project 3 — Pemrograman Website (PHP Native)**

Sistem manajemen inventori berbasis PHP native untuk mata kuliah Pemrograman Website.
Dibangun untuk memenuhi tugas Pertemuan 4 (P4), dan akan direfactor ke Laravel di Pertemuan 5 (P5).

Fitur utamanya: catat barang masuk dan keluar, kelola master data (produk, kategori, supplier),
lihat histori mutasi stok, dan dashboard laporan. Akses dibedakan antara Admin dan Staff.

---

## Tech Stack

- **Backend**: PHP 8.x (native, tanpa framework)
- **Database**: MySQL 5.7+ (wajib InnoDB), fallback SQLite untuk development lokal
- **Frontend**: Tailwind CSS via CDN, HTML murni tanpa build step
- **Auth**: Session PHP + `password_hash()` bcrypt
- **Security**: PDO Prepared Statement, CSRF token, XSS escaping via `e()`

---

## Fitur yang Sudah Selesai

### Autentikasi dan RBAC
Login dengan username dan password (bcrypt). Session di-regenerate setelah login berhasil
untuk cegah session fixation. Ada dua role: Admin dan Staff. Admin bisa akses semua modul
termasuk manajemen user. Staff bisa catat transaksi stok tapi tidak bisa ubah master data.
Proteksi dilakukan di backend, bukan sekadar sembunyikan link.

### Master Data
- **Kategori**: CRUD lengkap, cek duplikat nama, proteksi hapus jika kategori masih dipakai produk
- **Produk**: CRUD dengan relasi kategori, SKU unik, relasi many-to-many ke supplier via junction table
- **Supplier**: CRUD, proteksi hapus jika supplier masih terhubung produk

Semua listing punya search, sorting (allowlist), dan pagination 10 item per halaman.

### Transaksi Stok

Ini bagian paling kritis dari proyek. Barang masuk dan keluar masing-masing berjalan dalam
database transaction (`BEGIN/COMMIT/ROLLBACK`) sehingga update stok dan pencatatan histori
selalu sinkron, atau gagal bersama.

Untuk barang keluar, ada pessimistic locking:

```sql
SELECT stock, name FROM products WHERE id = ? FOR UPDATE
```

Row produk dikunci selama transaksi berjalan. Ini mencegah dua request bersamaan sama-sama lolos
validasi stok lalu sama-sama mengurangi stok yang sebenarnya tidak mencukupi. Kalau stok kurang,
sistem melempar exception, transaksi di-rollback, stok tidak berubah.

### Histori Stok
Semua mutasi tercatat di tabel `stock_movements`. Halaman histori bisa difilter berdasarkan produk,
tipe (IN/OUT), dan rentang tanggal. Join ke tabel `users` dan `user_profiles` untuk tampilkan
siapa yang mencatat transaksi.

### Dashboard
Empat widget: jumlah jenis produk, total nilai inventaris (stok x harga), jumlah produk stok kritis,
dan ringkasan total unit masuk vs keluar. Ada tabel peringatan produk yang stoknya sudah di bawah
batas minimum, plus laporan agregat per produk dengan query GROUP BY yang dicontohkan langsung
di slide dosen.

### Keamanan
- SQL Injection: 100% Prepared Statement PDO, tidak ada query concatenation
- XSS: semua output HTML lewat fungsi `e()` (wrapper `htmlspecialchars`)
- CSRF: setiap form POST punya hidden token, diverifikasi dengan `hash_equals()` (timing-safe)
- Sort injection: nama kolom ORDER BY hanya dari allowlist PHP, tidak langsung dari `$_GET`
- Password: bcrypt via `password_hash()`, tidak ada MD5 atau plaintext

---

## Akun Demo

Untuk penguji atau dosen, sudah ada seed data yang bisa langsung dipakai:

| Role  | Username | Password  |
|-------|----------|-----------|
| Admin | `admin`  | `admin123` |
| Staff | `staff`  | `staff123` |

---

## Instalasi

### Prasyarat
- PHP 8.0 atau lebih baru
- MySQL 5.7+ dengan storage engine InnoDB (wajib untuk `FOR UPDATE`)
- Ekstensi PHP: `pdo`, `pdo_mysql`, `session`

Kalau pakai XAMPP, pastikan versi MySQL-nya minimal 5.7. XAMPP lama kadang masih bawa MySQL 5.5
yang tidak support beberapa fitur yang kita pakai.

### Langkah-langkah

**1. Clone repo**
```bash
git clone https://github.com/DarulQutni-Q/InventoryManagementSystem.git
cd InventoryManagementSystem
```

**2. Setup database**

Buat database baru di MySQL:
```sql
CREATE DATABASE inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import schema dan seed data:
```bash
mysql -u root -p inventory_db < database/schema.sql
```

Seed data sudah termasuk kategori, produk, supplier, dan 2 akun user (admin + staff).

**3. Konfigurasi koneksi database**
```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php` dan isi kredensial MySQL:
```php
'host'     => 'localhost',
'dbname'   => 'inventory_db',
'username' => 'root',
'password' => 'password_anda',
```

Kalau tidak ada MySQL dan mau coba cepat pakai SQLite, biarkan saja
konfigurasi default, sistem akan pakai fallback SQLite otomatis.
Perlu dicatat: SQLite tidak support `FOR UPDATE`, jadi pessimistic locking
tidak aktif di mode ini. Untuk ujian atau demo resmi, gunakan MySQL.

**4. Jalankan server built-in PHP**
```bash
php -S localhost:8000 -t public/
```

Buka browser ke `http://localhost:8000`. Login dengan akun demo di atas.

---

## Menjalankan Test

Ada beberapa test script di folder `tests/`:

```bash
# Test transaksi stok IN, OUT (termasuk kasus gagal + rollback), dan query dashboard
php tests/test_stock_and_dashboard.php

# Test foundation: koneksi database, helper auth, escape, dan CSRF
php tests/test_foundation.php
```

Kalau semua test hijau, outputnya seperti ini:
```
Hasil: 16 PASS, 0 FAIL
```

Kalau ada yang gagal, periksa koneksi database dulu. Test akan membuat data sementara
dan menghapusnya lagi setelah selesai lewat `register_shutdown_function`.

---

## Struktur Folder

```
MiniProject03/
├── config/
│   ├── database.php          <- koneksi PDO (jangan di-commit jika ada password)
│   └── database.example.php  <- template konfigurasi
├── database/
│   └── schema.sql            <- DDL + seed data realistis
├── helpers/
│   ├── auth.php              <- requireLogin(), requireRole(), hasRole()
│   ├── csrf.php              <- csrf_field(), verify_csrf(), require_csrf()
│   ├── escape.php            <- e() wrapper htmlspecialchars
│   └── validation.php        <- fungsi validasi input
├── public/                   <- document root web server
│   ├── index.php
│   ├── login.php / logout.php
│   ├── dashboard.php
│   ├── categories/           <- CRUD kategori
│   ├── suppliers/            <- CRUD supplier
│   ├── products/             <- CRUD produk (roadmap P4 lanjutan)
│   └── stock/
│       ├── in.php            <- catat barang masuk
│       ├── out.php           <- catat barang keluar (FOR UPDATE)
│       └── history.php       <- riwayat mutasi stok
├── tests/
│   ├── test_foundation.php
│   └── test_stock_and_dashboard.php
└── views/
    ├── layouts/
    │   ├── header.php        <- HTML head + navbar + sidebar
    │   ├── sidebar.php       <- navigasi sidebar responsif
    │   └── footer.php        <- penutup HTML + script
    ├── auth/
    │   └── login.php         <- form login
    └── errors/
        ├── 403.php           <- tampilan akses ditolak
        └── 404.php           <- tampilan halaman tidak ditemukan
```

---

## Catatan Teknis dan Batasan

**MySQL wajib untuk fitur locking penuh.** Pessimistic locking (`SELECT FOR UPDATE`) hanya aktif
di MySQL InnoDB. Di SQLite mode fallback, sistem tetap bekerja dan validasi stok tetap jalan,
tapi row-level lock tidak ada. Untuk demo ke dosen, gunakan MySQL.

**`config/database.php` ada di `.gitignore`** jika menyimpan password. File `.example.php`
sudah tersedia sebagai template. Kalau mau push ke repo publik, pastikan kredensial tidak ikut.

**Fitur produk (CRUD) belum lengkap.** Modul `public/products/` ada di struktur folder
tapi implementasinya masuk roadmap P4 lanjutan. Yang sudah selesai: kategori, supplier,
transaksi stok, histori, dan dashboard.

---

## Roadmap ke P5 (Laravel)

Setelah P4 selesai dan diverifikasi, proyek ini akan direfactor ke Laravel. Business logic
yang sudah ada dipertahankan, hanya arsitekturnya yang berubah:

| Komponen P4 | Padanan P5 |
|---|---|
| `config/database.php` (PDO) | `.env` + `config/database.php` (Eloquent) |
| `views/layouts/header.php` | `resources/views/layouts/app.blade.php` |
| `helpers/auth.php` | Laravel Auth + middleware |
| File CRUD per aksi terpisah | Resource Controller (`--resource`) |
| `database/schema.sql` | `database/migrations/` dengan timestamp |

Urutan migrasi yang perlu diperhatikan: users, categories, suppliers, products,
user_profiles, product_suppliers, stock_movements. Timestamp migration harus mencerminkan
urutan ini karena ada foreign key dependency antar tabel.

---

## Konteks Tugas

Proyek ini dibuat untuk mata kuliah Pemrograman Website, Mini Project 3 (Pertemuan 4).
Branch aktif: `feat/p4-inventory-native`.
