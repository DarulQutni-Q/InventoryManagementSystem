# RULES.md — Standar Pengerjaan MiniProject 3: Inventory Management System

> Dokumen ini adalah **acuan mutlak** selama pengerjaan Mini Project 3.
> Setiap developer/agent wajib membacanya sebelum menyentuh satu baris kode pun.

---

## Daftar Isi

1. [Roadmap & Urutan Pengerjaan](#1-roadmap--urutan-pengerjaan)
2. [Arsitektur Kode & Struktur File](#2-arsitektur-kode--struktur-file)
3. [Standar Komentar Kode](#3-standar-komentar-kode)
4. [Keamanan & OWASP Wajib](#4-keamanan--owasp-wajib)
5. [Validasi Input & Business Rules](#5-validasi-input--business-rules)
6. [Alur Kerja Git & Commit Message](#6-alur-kerja-git--commit-message)
7. [Panduan Penulisan README.md](#7-panduan-penulisan-readmemd)
8. [Anti-Pattern yang Dilarang](#8-anti-pattern-yang-dilarang)
9. [Referensi Penilaian Dosen](#9-referensi-penilaian-dosen)

---

## 1. Roadmap & Urutan Pengerjaan

### Aturan Paling Mendasar

**P4 selesai dulu, baru P5. Titik.**

Jangan loncat ke Laravel sebelum seluruh checklist P4 hijau. Framework bisa dipelajari kapan saja — business logic yang salah di awal akan dibawa terus ke P5 dan jadi beban dua kali.

### Fase 1 — P4: PHP Native (Prioritas Sekarang)

Kerjakan secara berurutan, commit tiap tahap selesai:

| Tahap | Fokus | Selesai jika... |
|-------|-------|-----------------|
| 1.1 | Database Design & ERD | `schema.sql` jalan di MySQL tanpa error, FK aktif |
| 1.2 | Config & Helper | Koneksi PDO berjalan, fungsi auth/escape/validation tersedia |
| 1.3 | Autentikasi | Login/logout bekerja, session regenerate, akses tanpa login → redirect |
| 1.4 | Master Data CRUD | Kategori, produk, supplier: CRUD + search + sort + pagination |
| 1.5 | Transaksi Stok | Masuk/keluar dengan `BEGIN/COMMIT/ROLLBACK`, stok tidak negatif |
| 1.6 | Dashboard | 4 widget (count, nilai, stok rendah, laporan agregat) |
| 1.7 | Security Audit | Tidak ada SQL concatenation, semua output di-escape, semua halaman terlindungi |

### Fase 2 — P5: Laravel Refactor (Setelah P4 Tuntas)

Baru dikerjakan setelah acceptance criteria P4 (§9) seluruhnya terpenuhi. Urutan: Setup env → Migration+Model+Seeder → Routing+Controller → Blade Views → Migrasi fitur inventory → Demo.

---

## 2. Arsitektur Kode & Struktur File

### Prinsip Desain

- **KISS**: kalau bisa lebih sederhana, buat lebih sederhana.
- **YAGNI**: jangan buat class/abstraksi yang belum dibutuhkan.
- **SRP**: satu file/fungsi = satu tanggung jawab.
- **Tidak ada overengineering**: tidak perlu ORM buatan sendiri, tidak perlu service layer yang berlebihan.

### Batas Ukuran File (WAJIB Ditaati)

| Unit | Maksimum |
|------|----------|
| Baris per file | **150–200 baris** |
| Baris per fungsi/method | **20–30 baris** |

Kalau satu file sudah mendekati 200 baris, itu sinyal bahwa ada tanggung jawab yang harus dipecah ke file lain.

### Struktur Folder P4 (PHP Native)

```
inventory-app/          ← root aplikasi P4
├── config/
│   └── database.php    ← koneksi PDO saja, tidak ada logic lain
├── helpers/
│   ├── auth.php        ← requireLogin(), requireRole(), cek session
│   ├── escape.php      ← wrapper htmlspecialchars()
│   └── validation.php  ← validasi input form
├── public/
│   └── index.php       ← entry point (jika pakai front controller)
├── views/
│   ├── layouts/
│   │   └── main.php    ← template header + footer
│   ├── auth/
│   │   └── login.php
│   ├── products/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── categories/     ← sama strukturnya
│   ├── suppliers/      ← sama strukturnya
│   ├── stock/
│   │   ├── in.php
│   │   ├── out.php
│   │   └── history.php
│   └── dashboard.php
├── controllers/        ← opsional jika pakai front controller
│   ├── ProductController.php
│   ├── CategoryController.php
│   ├── SupplierController.php
│   └── StockMovementController.php
├── database/
│   └── schema.sql
├── login.php           ← atau langsung di root jika tidak pakai front controller
├── logout.php
└── dashboard.php
```

### Urutan Include/Require

Setiap file handler harus diawali dengan urutan ini:

```php
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/escape.php';
require_once __DIR__ . '/helpers/validation.php';

requireLogin();          // redirect ke login jika tidak ada session
requireRole('admin');    // hanya untuk halaman yang butuh admin
```

### Struktur Folder P5 (Laravel — dikerjakan setelah P4)

Ikuti konvensi Laravel standar. Tidak ada kreativitas di sini — dosen menguji pemahaman konvensi resmi Laravel, bukan variasi kreatif.

```
inventory-laravel/
├── app/Http/Controllers/    ← Resource Controllers
├── app/Models/              ← Eloquent Models dengan $fillable & relationships
├── database/
│   ├── migrations/          ← urutan timestamp penting!
│   ├── factories/
│   └── seeders/
├── resources/views/
│   ├── layouts/app.blade.php
│   └── components/          ← x-alert, x-pagination
└── routes/web.php
```

Urutan migration (timestamp harus mencerminkan ini):

```
1. users (sudah ada dari Laravel default)
2. categories
3. suppliers
4. products          (FK → categories)
5. user_profiles     (FK → users)
6. product_suppliers (FK → products, suppliers)
7. stock_movements   (FK → products, users)
```

---

## 3. Standar Komentar Kode

### Filosofi Dasar

Komentar itu untuk menjelaskan **MENGAPA**, bukan **APA**. Pembaca bisa baca kode untuk tahu APA yang dilakukan — tugas komentar adalah menjelaskan alasan di balik keputusan yang tidak obvious.

### Bahasa

**Bahasa Indonesia yang natural**. Bukan formal kaku, bukan robotik AI. Tulis seperti catatan ke teman developer yang baca kode ini 6 bulan lagi.

### Yang Boleh Dikomentar

```php
// Pakai FOR UPDATE supaya row di-lock selama transaksi ini —
// tanpa ini, dua request bersamaan bisa sama-sama lolos cek stok
$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");

// session_regenerate_id wajib setelah login berhasil,
// bukan paranoia — ini standar OWASP untuk cegah session fixation
session_regenerate_id(true);

// Allowlist kolom sort — kalau langsung pakai $_GET ke ORDER BY
// itu SQL injection via column name, prepared statement tidak cukup
$allowed_sort = ['name', 'price', 'stock', 'created_at'];
$sort = in_array($_GET['sort'] ?? '', $allowed_sort) ? $_GET['sort'] : 'name';

// Rollback juga saat Throwable, bukan hanya Exception —
// Error PHP (TypeError, dll) pun harus batalkan transaksi
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

### Yang DILARANG KERAS

```php
// ❌ Mengulang kode — tidak ada nilai tambah
// Inisialisasi variabel
$name = $_POST['name'];

// ❌ Banner dekoratif — noise, bukan sinyal
// ================================================
// FUNGSI VALIDASI
// ================================================

// ❌ Narasi langkah — komentar bukan tutorial
// Langkah 1: Ambil data dari POST
// Langkah 2: Validasi input
// Langkah 3: Simpan ke database

// ❌ Emoji dekoratif
// 🚀 Mulai proses transaksi
// ✅ Berhasil simpan ke database
```

### Aturan Praktis

- Fungsi sederhana yang sudah jelas: **tidak perlu komentar**.
- Komentar yang butuh penjelasan panjang: pertimbangkan apakah kodenya yang perlu disederhanakan.
- Komentar lama yang sudah tidak relevan dengan kode: **hapus**, jangan dibiarkan jadi kebohongan.

---

## 4. Keamanan & OWASP Wajib

Ini bukan saran. Dosen secara eksplisit menilai keamanan sebagai 15% bobot P4.

### 4.1 SQL Injection — Prepared Statement 100%

```php
// ✅ WAJIB — selalu seperti ini
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? AND category_id = ?");
$stmt->execute(["%{$search}%", $category_id]);

// ❌ DILARANG — tidak peduli seberapa "aman" kelihatannya
$result = $pdo->query("SELECT * FROM products WHERE name = '$name'");
```

Tidak ada pengecualian. Setiap query yang mengandung variabel dari luar (GET, POST, SESSION, header) wajib pakai Prepared Statement.

### 4.2 XSS — Output Encoding Wajib

```php
// Wrapper ini WAJIB ada di helpers/escape.php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// ✅ WAJIB di semua output HTML
<td><?= e($product['name']) ?></td>
<input value="<?= e($product['sku']) ?>">

// ❌ DILARANG — walau datanya dari database sekalipun
<td><?= $product['name'] ?></td>
echo $product['description'];
```

Di Laravel P5: `{{ $product->name }}` sudah auto-escape — jangan pakai `{!! !!}` kecuali benar-benar untuk HTML yang sudah trusted.

### 4.3 Sort/Order Injection — Allowlist Wajib

Prepared Statement tidak bisa melindungi nama kolom di ORDER BY. Wajib pakai allowlist:

```php
$allowed_sort = ['name', 'sku', 'price', 'stock', 'created_at'];
$allowed_dir  = ['ASC', 'DESC'];

$sort = in_array($_GET['sort'] ?? '', $allowed_sort) ? $_GET['sort'] : 'name';
$dir  = in_array(strtoupper($_GET['dir'] ?? ''), $allowed_dir) ? strtoupper($_GET['dir']) : 'ASC';

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY $sort $dir LIMIT ? OFFSET ?");
```

### 4.4 Password — Bcrypt Wajib

```php
// Saat simpan
$hash = password_hash($password, PASSWORD_DEFAULT);

// Saat verifikasi login
if (!password_verify($input, $hash_from_db)) {
    // gagal login
}
```

MD5, SHA1, plaintext: tidak bisa dinegosiasi, langsung tidak lulus.

### 4.5 Session Fixation

```php
// Setelah login berhasil — wajib ada sebelum set session user
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
```

### 4.6 CSRF

Setiap form POST wajib ada token CSRF:

```php
// Generate saat tampilkan form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verifikasi saat proses POST
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request');
}
```

### 4.7 Role & Otorisasi

Setiap endpoint wajib punya guard:

```php
// helpers/auth.php
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        include __DIR__ . '/../views/403.php';
        exit;
    }
}
```

Staff tidak boleh akses halaman manajemen user. Admin bisa akses semua. Verifikasi dilakukan di **backend**, bukan hanya sembunyi link di frontend.

---

## 5. Validasi Input & Business Rules

### 5.1 Validasi Teknis

```php
// helpers/validation.php — contoh pola
function validateProduct(array $data): array {
    $errors = [];

    if (empty(trim($data['name'] ?? ''))) {
        $errors['name'] = 'Nama produk tidak boleh kosong';
    }

    // SKU: alphanumeric, max 100 karakter
    if (!preg_match('/^[A-Za-z0-9\-_]{1,100}$/', $data['sku'] ?? '')) {
        $errors['sku'] = 'SKU harus alphanumeric, maksimal 100 karakter';
    }

    // Harga: angka, tidak boleh negatif
    if (!is_numeric($data['price'] ?? '') || (float)$data['price'] < 0) {
        $errors['price'] = 'Harga tidak valid';
    }

    // Stock minimum: integer, tidak boleh negatif
    if (!ctype_digit((string)($data['minimum_stock'] ?? '')) || (int)$data['minimum_stock'] < 0) {
        $errors['minimum_stock'] = 'Minimum stok harus bilangan bulat non-negatif';
    }

    return $errors;
}
```

### 5.2 Business Rules Non-Negotiable (dari PRD)

Rules ini tidak bisa di-bypass dari sisi UI — harus di-enforce di backend:

| Rule | Implementasi |
|------|-------------|
| SKU unik | `UNIQUE constraint` di DB + cek di aplikasi sebelum insert |
| Stok tidak boleh negatif | Validasi `stock >= quantity` + `SELECT FOR UPDATE` |
| Quantity harus > 0 | Validasi integer positif di form handler |
| Produk dengan histori tidak bisa dihapus bebas | `ON DELETE RESTRICT` di FK `stock_movements` + tangkap exception |
| Foreign key aktif | `SET FOREIGN_KEY_CHECKS = 1` (default MySQL InnoDB) |

### 5.3 Transaksi Stok — Pola Wajib

```php
$pdo->beginTransaction();
try {
    // Lock row — kritis untuk cegah race condition
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['stock'] < $quantity) {
        throw new RuntimeException("Stok tidak mencukupi ({$row['stock']} tersedia)");
    }

    $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
        ->execute([$quantity, $product_id]);

    $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?,?,?,?,?)")
        ->execute([$product_id, $user_id, 'OUT', $quantity, $note]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

### 5.4 Pagination

```php
$per_page    = 10;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;
$total_pages = ceil($total_data / $per_page);
```

---

## 6. Alur Kerja Git & Commit Message

### Setup Branch

Semua pekerjaan P4 dilakukan di branch `feat/p4-inventory-native`:

```bash
git checkout -b feat/p4-inventory-native
```

Branch `main` hanya untuk kode yang sudah diverifikasi bersih.

### Kapan Commit

Setiap kali 1 tahap dari roadmap selesai, langsung commit. Jangan menumpuk pekerjaan tanpa commit — kalau ada yang rusak, lebih sulit melacaknya.

### Format Commit Message

**Natural, manusiawi, tidak kaku**. Gambarkan apa yang benar-benar dikerjakan.

```
# ✅ Contoh commit yang baik
tambah schema.sql dengan 7 tabel dan relasi FK lengkap
setup koneksi PDO dan helper auth/escape/validation
implementasi login dengan prepared statement dan session regenerate
CRUD kategori selesai beserta search dan pagination
tambah validasi SKU unik di form create produk
transaksi stok masuk pakai BEGIN/COMMIT, stok ter-update atomik
fix: barang keluar tidak cek stok cukup dulu, sekarang sudah pakai FOR UPDATE
dashboard: widget total produk dan nilai inventory dari query agregat
security audit: semua output sudah pakai htmlspecialchars

# ❌ Jangan seperti ini
feat: implement CRUD functionality
fix: bug fix
update: update files
add validation
```

Gunakan bahasa Indonesia atau Inggris, yang penting jujur menggambarkan apa yang dilakukan. Kalau ada trade-off atau keputusan desain yang tidak obvious, boleh tulis di body commit.

### .gitignore

File-file berikut jangan di-commit:

```
.env
config/database.php    ← kalau menyimpan kredensial
vendor/
node_modules/
*.log
```

---

## 7. Panduan Penulisan README.md

README ditulis **setelah** fitur selesai, bukan ditulis dulu sebagai template kosong.

### Gaya Penulisan

Tulis seperti manusia yang sudah bekerja di proyek ini, bukan copywriter pemasaran. Variasi panjang kalimat itu wajar. Boleh mengakui bahwa ada hal yang rumit atau bagian instalasi yang butuh perhatian khusus.

**Dilarang formula AI-slop:**
- "Di era digital yang serba cepat..."
- "Solusi inovatif dan mutakhir untuk..."
- "Tanpa berlama-lama lagi, mari kita..."
- "Sebagai tonggak sejarah dalam..."

**Yang diinginkan:**

```markdown
## Instalasi

Clone repo, buat database MySQL baru, import `database/schema.sql`.
Salin `config/database.example.php` ke `config/database.php` dan isi kredensial.

Satu hal yang perlu diperhatikan: MySQL harus versi 5.7+ karena kita pakai
`FOR UPDATE` dalam InnoDB. Kalau pakai XAMPP lama, cek versinya dulu.

Jalankan dengan server built-in PHP:
```bash
php -S localhost:8000 -t public/
```

Login default: admin@example.com / password (akan diubah setelah demo).
```

### Isi Minimum README

1. Deskripsi singkat proyek (2–3 kalimat, jujur tentang scope)
2. Tech stack yang dipakai
3. Cara instalasi yang benar-benar bisa diikuti
4. Cara menjalankan
5. Struktur folder (jika tidak obvious)
6. Catatan penting / known limitations (jujur saja)
7. Kredit / konteks tugas kuliah

---

## 8. Anti-Pattern yang Dilarang

Ini daftar hal yang langsung disebut dosen sebagai penyebab nilai turun.

### Kode

| Anti-Pattern | Kenapa Dilarang |
|---|---|
| `$pdo->query("... WHERE name = '$name'")` | SQL injection langsung |
| `echo $product['name']` tanpa escape | XSS vulnerability |
| `ORDER BY $_GET['sort']` langsung | SQL/column injection |
| `INSERT INTO ... VALUES (MD5('$pass'))` | Password mudah di-crack |
| Logic di route closure (P5) | Melanggar MVC |
| Business logic di Blade `@php` (P5) | Melanggar MVC |
| `$fillable = []` kosong di Model (P5) | Mass assignment vulnerability |
| N+1 query tanpa `with()` (P5) | Performance problem |
| Satu file lebih dari 200 baris yang berisi semua hal | Sulit dibaca, sulit debug |
| Komentar mengulang kode | Noise, bukan dokumentasi |

### Workflow

| Anti-Pattern | Konsekuensi |
|---|---|
| Loncat ke P5 sebelum P4 selesai | Business logic rusak di P5, nilai turun dua kali |
| Commit `.env` ke public repo | Kredensial bocor |
| `migrate:fresh` di production (P5) | Semua data hilang |
| Cuma hapal command tanpa paham file yang dihasilkan (P5) | Dosen akan bertanya detail, tidak bisa jawab |

---

## 9. Referensi Penilaian Dosen

### Bobot P4 (PHP Native)

| Komponen | Bobot |
|---|---|
| Desain database dan relasi | **20%** |
| Foreign key dan integritas data | **10%** |
| CRUD master data | **10%** |
| Transaksi masuk/keluar | **20%** |
| Search, filter, sorting, pagination | **10%** |
| Login, session, authorization | **10%** |
| Keamanan aplikasi | **15%** |
| Kerapian dan presentasi | **5%** |

### Checklist P4 (Harus Semua Hijau Sebelum Submit)

- [ ] Relasi dan foreign key berjalan di database
- [ ] SKU unik dan validasi server aktif (tidak bisa duplikat)
- [ ] Login, logout, session, dan role berjalan
- [ ] **Tidak ada query concatenation dari input user** (semua Prepared Statement)
- [ ] **Output HTML menggunakan escaping** (`htmlspecialchars`)
- [ ] Search, filter, sorting, pagination berjalan di semua listing
- [ ] Stok tidak negatif dan histori tersimpan
- [ ] Transaksi menggunakan commit/rollback
- [ ] Dashboard menampilkan laporan agregat

### Filosofi yang Dosen Tekankan

> *"Tujuan akhir: mahasiswa mampu membangun aplikasi yang datanya benar, prosesnya konsisten, dan aksesnya aman."*

Penilaian bukan hanya apakah kode **jalan**, tapi apakah kita **mengerti mengapa** setiap komponen ada di sana.

---

*Dokumen ini hidup bersama kode. Kalau ada keputusan desain besar yang diambil di tengah jalan dan tidak tercakup di sini, update dokumen ini sekaligus.*
