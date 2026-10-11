# Product Requirements Document (PRD)
## Inventory Management System — Mini Project 3

---

### 1. Product Overview & Objectives

**Inventory Management System** adalah aplikasi manajemen inventaris pergudangan terintegrasi berbasis web yang dikembangkan untuk mata kuliah Pemrograman Website. Proyek ini terbagi menjadi dua fase utama:
- **Fase 1 (P4):** Arsitektur berbasis **PHP Native** murni dengan PDO MySQL, transaksi ACID, proteksi OWASP, dan Tailwind CSS.
- **Fase 2 (P5):** Refactor arsitektur ke **Laravel Framework** (Migration, Eloquent ORM, MVC Controller, Blade Engine).

#### Tujuan Utama:
1. Menjamin integritas data stok inventaris secara real-time melalui transaksi database relasional dan locking pesimistik.
2. Mencegah kerugian inventaris akibat race condition, mutasi ganda, atau stok negatif.
3. Mengamankan operasional sistem dari ancaman keamanan web modern (SQLi, XSS, CSRF, Session Fixation, Privilege Escalation).
4. Menyediakan antarmuka yang bersih, intuitif, dan responsif bagi operator gudang maupun administrator.

---

### 2. User Roles & Permission Matrix (RBAC)

Sistem membedakan hak akses pengguna menjadi 2 peran utama:

| Modul / Fitur | Admin | Staff Gudang | Catatan Keamanan |
| :--- | :---: | :---: | :--- |
| **Login / Logout** | Ya | Ya | Session regeneration + CSRF protection |
| **Dashboard Metrik & Laporan** | Ya | Ya | Agregasi inventaris real-time |
| **Master Kategori (CRUD)** | Ya | Hanya Baca | Proteksi referensial terhadap produk aktif |
| **Master Supplier (CRUD)** | Ya | Hanya Baca | Proteksi referensial terhadap relasi produk |
| **Master Produk (CRUD)** | Ya | Hanya Baca | Proteksi hapus jika riwayat stok ada |
| **Transaksi Barang Masuk (IN)** | Ya | Ya | ACID Transaction (`BEGIN/COMMIT/ROLLBACK`) |
| **Transaksi Barang Keluar (OUT)** | Ya | Ya | Pessimistic Locking (`SELECT ... FOR UPDATE`) |
| **Histori Mutasi Stok** | Ya | Ya | Filter multi-dimensi + pagination |
| **Manajemen Pengguna (User)** | Ya | Ditolak (403) | Khusus Admin; staff tidak diizinkan |

---

### 3. Functional Specifications

#### 3.1. Autentikasi & Manajemen Sesi
- **Login:** Autentikasi berbasis username dan verifikasi hash password (`password_verify` bcrypt).
- **Session Hijacking Mitigation:** Regenerasi ID sesi (`session_regenerate_id(true)`) pasca login sukses.
- **CSRF Token:** Setiap request mutasi (POST) wajib menyertakan token CSRF yang diverifikasi dengan `hash_equals()`.
- **Role Enforcement:** Validasi role dilakukan pada lapis backend/server-side; pengguna unauthorized langsung dialihkan ke halaman HTTP 403 Forbidden.

#### 3.2. Master Data Management
- **Kategori:**
  - Kode unik (slug) dan nama kategori.
  - Pengecekan duplikasi nama secara atomik.
  - Soft check integritas referensial: kategori tidak dapat dihapus jika masih berelasi dengan produk aktif.
- **Supplier:**
  - Informasi kontak: nama, telepon, email, alamat.
  - Proteksi integritas relasi many-to-many.
- **Produk:**
  - Atribut: SKU (unik), Nama, Deskripsi, Kategori, Harga Beli, Harga Jual, Stok Awal, Minimum Stok.
  - Relasi Supplier: Dukungan multi-supplier per produk melalui tabel junction `product_suppliers`.
  - Pengecekan riwayat mutasi: produk tidak boleh dihapus bila telah memiliki log mutasi stok.

#### 3.3. Transaksi Mutasi Stok (Core Business Logic)
- **Barang Masuk (Stock In):**
  - Input: Produk, Jumlah Masuk, Tanggal, Catatan/Keterangan.
  - Menambah stok produk dan mencatat detail ke `stock_movements`.
  - Terbungkus dalam transaksi database (`BEGIN/COMMIT/ROLLBACK`).
- **Barang Keluar (Stock Out):**
  - Input: Produk, Jumlah Keluar, Tanggal, Catatan/Keterangan.
  - **Pessimistic Locking:** Eksekusi `SELECT stock, name FROM products WHERE id = ? FOR UPDATE` untuk mencegah race condition (TOCTOU).
  - Validasi stok non-negatif: Jika jumlah keluar > stok tersedia, rollback transaksi dan kembalikan pesan kesalahan.
  - Mengurangi stok produk dan mencatat log mutasi stok.

#### 3.4. Riwayat Mutasi (Stock Movement History)
- Menampilkan seluruh alur keluar/masuk barang secara kronologis.
- Filter komprehensif: berdasarkan ID Produk, Tipe Mutasi (`IN`/`OUT`), dan Rentang Tanggal.
- Metadata lengkap: Timestamp, User pencatat, Selisih stok, dan Keterangan.
- Paginasi data 10 baris per halaman dengan sorting yang tervalidasi.

#### 3.5. Dashboard & Laporan Eksekutif
- **Widget Metrik:**
  1. Total Jenis Produk Terdaftar.
  2. Total Nilai Aset Inventaris (`SUM(stock * purchase_price)`).
  3. Indikator Produk Kritis (Stok $\le$ Minimum Stok).
  4. Ringkasan Total Unit Masuk vs Keluar.
- **Tabel Peringatan Stok Kritis:** Menampilkan produk yang membutuhkan re-order segera.
- **Laporan Agregasi Produk:** Rekapitulasi pergerakan barang per produk (`GROUP BY`).

#### 3.6. Manajemen Pengguna (Admin Only)
- Pembuatan akun baru dengan role `admin` atau `staff`.
- Input profil lengkap: nama lengkap, nomor telepon, alamat (`user_profiles`).
- Enkripsi password menggunakan `PASSWORD_BCRYPT` (cost 10/12).

---

### 4. Non-Functional Specifications & Security

1. **Prepared Statements Mandat (OWASP A03 - Injection):**
   - 100% query database menggunakan parameter binding PDO (`?` atau named parameter).
   - Dilarang keras melakukan konkatenasi string SQL dengan input pengguna.
2. **Output Sanitization (OWASP A03 - XSS):**
   - Seluruh rendering data ke HTML wajib melalui helper escape `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
3. **Cross-Site Request Forgery (OWASP A01 - Broken Access Control):**
   - Proteksi token anti-CSRF pada semua form bersubmisi POST.
4. **Resilience & Fallback:**
   - Dukungan koneksi utama MySQL dengan fallback otomatis ke SQLite untuk pengujian lokal terisolasi tanpa konfigurasi rumit.
5. **Clean Architecture & Maintainability:**
   - Pemisahan concerns antara `config/`, `helpers/`, `views/`, dan `public/` entry points.
   - Tanpa ketergantungan pihak ketiga/vendor runtime berat pada P4.

---

### 5. Skema Relasional Basis Data (7 Tabel)

1. `users` — Kredensial akun dan peran sistem (`admin`, `staff`).
2. `user_profiles` — Detail informasi personal pengguna (relasi 1:1 dengan `users`).
3. `categories` — Pengelompokan jenis barang (`code`, `name`, `description`).
4. `suppliers` — Data mitra vendor penyedia barang.
5. `products` — Master barang inventaris (`sku`, `name`, `stock`, `min_stock`, `purchase_price`, `selling_price`).
6. `product_suppliers` — Tabel relasi many-to-many antara produk dan supplier (`lead_time_days`, `is_primary`).
7. `stock_movements` — Catatan mutasi logistik barang (`type: IN/OUT`, `quantity`, `reference_no`, `notes`, `user_id`).

---

### 6. Acceptance Criteria (Definition of Done - P4)

- [x] Skema database relasional 7 tabel dengan foreign key aktif dan indeks SKU.
- [x] Helper security (auth, csrf, escape, validation) tuntas tanpa silent failure.
- [x] Otentikasi lengkap dengan regenerasi sesi dan role staff/admin.
- [x] CRUD lengkap Kategori, Supplier, Produk dengan proteksi integritas referensial.
- [x] Transaksi mutasi stok ACID dengan penanganan race condition (`FOR UPDATE`).
- [x] Dashboard analitik 4 widget dan tabel peringatan stok kritis.
- [x] Modul manajemen pengguna khusus admin (HTTP 403 untuk staff).
- [x] Suite pengujian otomatis (`tests/run_all.php`) lulus 100% seluruh skenario pengujian.
- [x] Dokumentasi README.md dan arsitektur repositori siap produksi.
