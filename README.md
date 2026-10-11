# Inventory Management System

Mini Project 3 — Pemrograman Website

Repositori ini memuat sistem manajemen inventori gudang dalam dua implementasi terpisah yang rapi dan terstruktur:

1. **`inventory-native/`** — Implementasi Pertemuan 4 (P4) berbasis **PHP Native** murni tanpa framework, memanfaatkan PDO prepared statements, session native, CSRF token kustom, dan arsitektur modular.
2. **`inventory-laravel/`** — Implementasi Pertemuan 5 (P5) berbasis framework **Laravel 12**, memanfaatkan Eloquent ORM, database migrations, resource controllers, form request validation, Blade templating, dan PHPUnit feature tests.

Kedua implementasi berfokus menjaga integritas data stok fisik secara real-time, mengeliminasi risiko selisih barang akibat *race condition* melalui transaksi database atomik (beserta *pessimistic locking*), dan menerapkan pemisahan hak akses (RBAC) yang tegas antara Administrator dan Staf Gudang.
## Tech Stack

Stack yang digunakan sengaja dipilih agar ringan dijalankan di lokal tanpa dependensi rumit, namun tetap menerapkan standar arsitektur modern:

- **Framework**: Laravel 12
- **Bahasa**: PHP 8.2+
- **Database**:
  - **SQLite** (bawaan default `.env.example`, langsung jalan tanpa perlu instal server database tambahan)
  - **MySQL 5.7+ / MariaDB** (menggunakan storage engine InnoDB untuk mendukung row-level locking di lingkungan produksi)
- **ORM**: Eloquent ORM dengan relasi relasional lengkap (one-to-many, many-to-many junction)
- **Frontend**: Blade Templates + Tailwind CSS (via CDN) dengan desain antarmuka utilitarian yang bersih, sidebar tetap (fixed), dan tata letak responsif

## Pembagian Peran Pengguna (RBAC)

Aplikasi membedakan hak akses secara ketat di sisi backend (melalui middleware dan proteksi controller), bukan sekadar menyembunyikan navigasi di antarmuka:

### 1. Administrator
- **Dashboard Eksekutif**: Ringkasan menyeluruh kondisi gudang, termasuk metrik valuasi finansial (total nilai aset inventaris dalam Rupiah), statistik produk aktif, peringatan stok kritis, dan rasio barang masuk vs keluar.
- **Master Data Penuh (CRUD)**: Kontrol penuh untuk membuat, memperbarui, dan menghapus data Kategori, Produk (termasuk penetapan harga beli, harga jual, dan relasi multi-supplier), serta Data Supplier.
- **Manajemen Pengguna**: Mengelola data akun staf dan administrator di sistem.

### 2. Operator Gudang (Staff)
- **Dashboard Operasional Logistik**: Tampilan yang disesuaikan khusus untuk aktivitas fisik gudang tanpa menampilkan metrik valuasi uang atau harga beli barang. Memuat widget peringatan stok menipis dengan tombol aksi cepat restock (langsung membuka form barang masuk).
- **Transaksi Barang Masuk & Keluar**: Mencatat mutasi stok harian secara real-time (penerimaan barang dari supplier atau pengeluaran barang untuk kebutuhan distribusi).
- **Katalog Produk Mode Baca (Read-Only)**: Staf dapat mencari dan memeriksa SKU, nama barang, kategori, serta sisa stok fisik. Staf tidak memiliki akses untuk mengubah harga atau menghapus data master. Setiap upaya manipulasi langsung diblokir dengan response HTTP 403 (Forbidden).

## Integritas Transaksi & Pessimistic Locking

Pencatatan mutasi stok adalah bagian paling kritis di sistem ini untuk memastikan stok fisik selalu sinkron dengan data di sistem:

- **Transaksi Atomik (`DB::transaction()`)**: Perubahan jumlah stok pada tabel produk dan pencatatan riwayat log di tabel mutasi (`stock_movements`) selalu dieksekusi dalam satu transaksi database tunggal. Kedua aksi harus berhasil bersamaan atau dibatalkan seluruhnya (*all-or-nothing*).
- **Pessimistic Locking (`lockForUpdate()`)**: Pada proses pencatatan barang keluar, baris produk dikunci sementara menggunakan klausa `SELECT ... FOR UPDATE`. Mekanisme ini mencegah *race condition* jika ada dua operator yang mencatat pengeluaran barang yang sama di detik yang bersamaan.
- **Pencegahan Stok Negatif**: Sistem selalu memvalidasi ketersediaan barang sebelum pengurangan dilakukan. Jika permintaan melebihi sisa stok, transaksi otomatis di-rollback, sistem menampilkan pesan galat yang informatif, dan saldo stok tidak akan pernah menjadi negatif.

## Akun Demo

Untuk keperluan demo atau pengujian, database seeder sudah menyediakan dua akun siap pakai:

| Peran | Username | Password | Deskripsi Wewenang |
|---|---|---|---|
| **Administrator** | `admin` | `admin123` | Akses penuh dashboard keuangan, CRUD master data, dan user |
| **Operator Gudang (Staff)** | `staff` | `staff123` | Dashboard logistik, transaksi masuk/keluar, katalog mode baca |

## Struktur Direktori Repositori

```
.
├── inventory-native/         # P4: Implementasi PHP Native
│   ├── config/               # Konfigurasi database PDO
│   ├── database/             # schema.sql (DDL & data awal)
│   ├── helpers/              # Helper auth, CSRF, escaping, validasi
│   ├── public/               # Document root web server native
│   ├── tests/                # Test suite native (119 assertions)
│   └── views/                # Template antarmuka native
├── inventory-laravel/        # P5: Implementasi Laravel 12
│   ├── app/                  # Controller, Model Eloquent, Middleware
│   ├── database/             # Migrasi tabel, factory, dan seeder
│   ├── resources/views/      # Blade templates dengan Tailwind CSS
│   ├── routes/               # Definisi rute web
│   └── tests/                # Feature & Unit tests (25 tests)
├── PRD.md                    # Product Requirement Document
├── RULES.md                  # Aturan teknis dan standar kode
├── LICENSE                   # Lisensi open-source MIT
└── README.md                 # Dokumentasi utama proyek
```

## Panduan Menjalankan P4 (PHP Native)

Masuk ke folder `inventory-native/`:

```bash
cd inventory-native
```

### 1. Konfigurasi Database
Salin file konfigurasi database:
```bash
cp config/database.example.php config/database.php
```
Secara default, sistem akan otomatis menggunakan fallback SQLite untuk kemudahan pengujian lokal, atau gunakan MySQL dengan mengimpor skema awal:
```bash
mysql -u root -p inventory_db < database/schema.sql
```

### 2. Menjalankan Server Lokal
```bash
php -S localhost:8000 -t public/
```
Buka browser di `http://localhost:8000` dan masuk menggunakan akun demo.

### 3. Menjalankan Pengujian P4
Seluruh modul P4 telah diaudit secara otomatis melalui test runner bawaan:
```bash
php tests/run_all.php
```
Status pengujian: **119 assertions lulus 100%** (fondasi, autentikasi, master data, transaksi stok, dan manajemen user).

---

## Panduan Menjalankan P5 (Laravel 12)

Masuk ke folder `inventory-laravel/`:

```bash
cd inventory-laravel
```

### 1. Instalasi dan Setup Environment
```bash
# Install dependensi Composer
composer install

# Salin template konfigurasi
cp .env.example .env

# Generate application key
php artisan key:generate

# Siapkan file database SQLite
touch database/database.sqlite

# Eksekusi migrasi dan seeder data demo
php artisan migrate:fresh --seed
```

### 2. Menjalankan Server Laravel
```bash
php artisan serve
```
Buka peramban di `http://localhost:8000` dan masuk menggunakan akun demo.

### 3. Menjalankan Pengujian P5
```bash
php artisan test
```
Status pengujian: **25 feature tests, 108 assertions lulus 100%**.

