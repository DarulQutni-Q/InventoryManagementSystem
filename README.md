# Inventory Management System

Mini Project 3 — Pemrograman Website (Upgrade P4 Native ke Framework Laravel 12)

Repositori ini berisi sistem manajemen inventori barang gudang yang telah di-upgrade penuh dari implementasi awal P4 (PHP Native) menjadi arsitektur framework resmi **Laravel 12** langsung di root direktori repositori.

Fokus utama sistem ini adalah menjaga konsistensi data stok barang fisik, mengeliminasi risiko selisih barang akibat *race condition* melalui transaksi database atomik, serta menerapkan pemisahan hak akses (RBAC) yang tegas antara manajemen dan tim operasional lapangan.

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

## Cara Menjalankan Aplikasi

Langkah instalasi lokal menggunakan SQLite (paling cepat dan praktis):

```bash
# 1. Install dependensi
composer install

# 2. Salin template konfigurasi environment
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Siapkan file database SQLite
touch database/database.sqlite

# 5. Jalankan migrasi dan seeder data awal
php artisan migrate:fresh --seed

# 6. Jalankan development server
php artisan serve
```

Buka peramban di `http://localhost:8000` dan login dengan salah satu akun demo di atas.

> **Catatan Penggunaan MySQL**: Jika ingin menggunakan MySQL, cukup sesuaikan variabel `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada file `.env`, lalu jalankan `php artisan migrate:fresh --seed`. Pastikan MySQL menggunakan engine InnoDB.

## Menjalankan Pengujian (Testing)

Semua alur bisnis utama sudah dicakup oleh automated tests berbasis PHPUnit Feature Tests di `tests/Feature/InventoryTest.php`. Pengujian mencakup:
- Alur login, logout, dan pengalihan session
- Otorisasi berbasis peran (admin vs staff) pada setiap endpoint
- Operasi CRUD master data kategori, supplier, dan produk
- Transaksi stok masuk dan stok keluar beserta pencatatan riwayatnya
- Pengujian rollback otomatis saat stok tidak mencukupi (pencegahan stok minus)
- Filter riwayat mutasi stok dan kalkulasi widget dashboard

Jalankan perintah pengujian:

```bash
php artisan test
```

Status saat ini: **25 feature tests, 108 assertions lulus 100%**.

## Struktur Direktori Utama

```
.
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controller auth, master data, stok, dan dashboard
│   │   └── Middleware/        # Middleware otorisasi (EnsureUserIsAdmin)
│   └── Models/                # Eloquent model (Product, Category, Supplier, StockMovement, User)
├── database/
│   ├── migrations/            # 7 file migrasi skema tabel
│   └── seeders/               # Seeder data realistis dan akun demo
├── resources/
│   └── views/                 # Blade templates dengan layout modular dan Tailwind CSS
│       ├── auth/              # Form login
│       ├── categories/        # Antarmuka CRUD kategori
│       ├── dashboard/         # Dashboard terpisah untuk admin dan staff
│       ├── layouts/           # Template layout (navbar dan fixed sidebar)
│       ├── products/          # Antarmuka produk (CRUD untuk admin, read-only untuk staff)
│       ├── stock/             # Form barang masuk, barang keluar, dan log riwayat
│       ├── suppliers/         # Antarmuka CRUD supplier
│       └── users/             # Manajemen user (admin only)
├── routes/
│   └── web.php                # Rute web dan pengelompokan middleware
└── tests/
    └── Feature/               # 25 automated feature tests
```
