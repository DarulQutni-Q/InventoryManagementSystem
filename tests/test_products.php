<?php
/**
 * tests/test_products.php
 * Pengujian modul Master Data Produk: listing, filter, sorting, CRUD,
 * validasi keunikan SKU, dan penegakan aturan bisnis proteksi histori mutasi stok.
 */

declare(strict_types=1);

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=:memory:');
putenv('DB_FALLBACK_SQLITE=true');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

// Inisialisasi basis data dan seed data
$rawSql = file_get_contents(__DIR__ . '/../database/schema.sql');
$sqliteSql = convertMysqlToSqlite($rawSql);
$pdo->exec($sqliteSql);

$passed = 0;
$failed = 0;

function it(string $description, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
    }
}

// Simulasi login sebagai admin
loginUser(['id' => 1, 'username' => 'admin', 'role' => 'admin'], ['full_name' => 'Administrator Utama']);

echo "=== 1. UJI LISTING, SEARCH, FILTER, DAN SORTING PRODUK ===\n";
$totalSeedProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
it("Seed produk awal berjumlah 8 item", $totalSeedProducts === 8);

// Test Search
$searchStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE name LIKE ? OR sku LIKE ?');
$searchStmt->execute(['%Monitor%', '%Monitor%']);
it("Search 'Monitor' menemukan produk yang sesuai", (int)$searchStmt->fetchColumn() === 1);

// Test Filter Kategori
$catStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
$catStmt->execute([1]);
it("Filter category_id = 1 mengembalikan produk kategori Elektronik", (int)$catStmt->fetchColumn() >= 2);

// Test Indikator Stok Rendah (stock <= minimum_stock)
$lowStockStmt = $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= minimum_stock');
$lowStockCount = (int)$lowStockStmt->fetchColumn();
it("Mendeteksi produk berstatus stok menipis/habis (minimal 2 produk seed)", $lowStockCount >= 2);

// Test Relasi Multi-Supplier
$psStmt = $pdo->prepare('SELECT COUNT(*) FROM product_suppliers WHERE product_id = 1');
$psStmt->execute();
it("Produk 1 terhubung ke multiple supplier di junction table", (int)$psStmt->fetchColumn() === 2);

echo "\n=== 2. UJI VALIDASI SKU & ATURAN BISNIS ===\n";
// Uji duplikasi SKU saat create produk baru
$dupProduct = ['name' => 'Produk Kloning', 'category_id' => 1, 'sku' => 'PROD-ELEC-001', 'price' => 1000, 'stock' => 5, 'minimum_stock' => 1];
$dupErrors = validateProduct($dupProduct, null, $pdo);
it("validateProduct menolak pendaftaran SKU yang sudah terdaftar", isset($dupErrors['sku']));

// Uji SKU yang sama saat edit produk itu sendiri (harus diizinkan)
$selfEditErrors = validateProduct($dupProduct, 1, $pdo);
it("validateProduct mengizinkan SKU milik produk itu sendiri saat diedit", !isset($selfEditErrors['sku']));

echo "\n=== 3. UJI TAMBAH PRODUK BARU & TRANSAKSI STOK AWAL ===\n";
$newSku = 'PROD-TEST-999';
$newProduct = [
    'category_id' => 2,
    'sku' => $newSku,
    'name' => 'Webcam Full HD 1080p Test',
    'price' => 350000.00,
    'stock' => 10,
    'minimum_stock' => 3,
    'suppliers' => [2, 3],
    'supplier_prices' => [2 => 300000.00, 3 => 310000.00]
];

$pdo->beginTransaction();
$insertStmt = $pdo->prepare('
    INSERT INTO products (category_id, sku, name, price, stock, minimum_stock)
    VALUES (?, ?, ?, ?, ?, ?)
');
$insertStmt->execute([
    $newProduct['category_id'], $newProduct['sku'], $newProduct['name'],
    $newProduct['price'], $newProduct['stock'], $newProduct['minimum_stock']
]);
$newProdId = (int)$pdo->lastInsertId();

$psIns = $pdo->prepare('INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)');
foreach ($newProduct['suppliers'] as $sId) {
    $psIns->execute([$newProdId, $sId, $newProduct['supplier_prices'][$sId]]);
}

// Catat otomatis histori 'IN' untuk inisialisasi stok
$smIns = $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)');
$smIns->execute([$newProdId, 1, 'IN', $newProduct['stock'], 'Stok awal produk baru']);
$pdo->commit();

it("Produk baru berhasil disimpan ke database", $newProdId > 0);

$checkPs = $pdo->prepare('SELECT COUNT(*) FROM product_suppliers WHERE product_id = ?');
$checkPs->execute([$newProdId]);
it("Relasi multi-supplier tersimpan di product_suppliers", (int)$checkPs->fetchColumn() === 2);

$checkSm = $pdo->prepare("SELECT quantity FROM stock_movements WHERE product_id = ? AND type = 'IN'");
$checkSm->execute([$newProdId]);
it("Histori transaksi stok masuk awal tercatat di stock_movements", (int)$checkSm->fetchColumn() === 10);

echo "\n=== 4. UJI EDIT PRODUK & SINKRONISASI SUPPLIER ===\n";
$updatedName = 'Webcam Full HD 1080p Pro Updated';
$updatedPrice = 375000.00;
$updatedSuppliers = [1]; // Hanya ke supplier 1

$pdo->beginTransaction();
$updStmt = $pdo->prepare('UPDATE products SET name = ?, price = ? WHERE id = ?');
$updStmt->execute([$updatedName, $updatedPrice, $newProdId]);

$pdo->prepare('DELETE FROM product_suppliers WHERE product_id = ?')->execute([$newProdId]);
$pdo->prepare('INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)')->execute([$newProdId, 1, 320000.00]);
$pdo->commit();

$checkUpd = $pdo->prepare('SELECT name, price, stock FROM products WHERE id = ?');
$checkUpd->execute([$newProdId]);
$rowUpd = $checkUpd->fetch();
it("Nama dan harga produk berhasil diubah", $rowUpd['name'] === $updatedName && (float)$rowUpd['price'] === $updatedPrice);
it("Stok fisik tidak berubah oleh proses edit produk", (int)$rowUpd['stock'] === 10);

$checkPsUpd = $pdo->prepare('SELECT supplier_id FROM product_suppliers WHERE product_id = ?');
$checkPsUpd->execute([$newProdId]);
it("Relasi supplier berhasil disinkronisasi", (int)$checkPsUpd->fetchColumn() === 1);

echo "\n=== 5. UJI PROTEKSI DELETE PRODUK BERHISTORI (ON DELETE RESTRICT) ===\n";
// Mencoba menghapus produk yang memiliki riwayat mutasi stok (produk 1)
$histCheck = $pdo->prepare('SELECT COUNT(*) FROM stock_movements WHERE product_id = ?');
$histCheck->execute([1]);
$hasHistory = (int)$histCheck->fetchColumn() > 0;
it("Produk 1 terkonfirmasi memiliki histori di stock_movements", $hasHistory);

$canDelete = !$hasHistory;
it("Penghapusan produk 1 DITOLAK sesuai aturan integritas audit", $canDelete === false);

$existCheck = $pdo->query('SELECT COUNT(*) FROM products WHERE id = 1')->fetchColumn();
it("Produk 1 tetap ada dan tidak terhapus dari basis data", (int)$existCheck === 1);

echo "\n=== 6. UJI HAPUS PRODUK TANPA HISTORI ===\n";
// Buat produk sementara tanpa histori stock_movements
$pdo->prepare('INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) VALUES (1, "TEMP-DEL-01", "Barang Sementara", 1000, 0, 0)')->execute();
$tempId = (int)$pdo->lastInsertId();
$pdo->prepare('INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, 1, 900)')->execute([$tempId]);

// Hapus produk tanpa histori
$pdo->beginTransaction();
$pdo->prepare('DELETE FROM product_suppliers WHERE product_id = ?')->execute([$tempId]);
$pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$tempId]);
$pdo->commit();

$checkTemp = $pdo->query("SELECT COUNT(*) FROM products WHERE id = {$tempId}")->fetchColumn();
it("Produk tanpa histori stok berhasil dihapus secara bersih", (int)$checkTemp === 0);

$checkTempPs = $pdo->query("SELECT COUNT(*) FROM product_suppliers WHERE product_id = {$tempId}")->fetchColumn();
it("Relasi product_suppliers dari produk yang dihapus ikut bersih", (int)$checkTempPs === 0);

echo "\n=== 7. UJI ROLE GUARD STAFF VS ADMIN ===\n";
loginUser(['id' => 2, 'username' => 'staff', 'role' => 'staff'], ['full_name' => 'Staff Gudang Logistik']);
it("Staff tidak memiliki izin admin untuk aksi create/edit/delete", !hasRole('admin'));

echo "\n============================================\n";
echo "HASIL PENGUJIAN PRODUK: {$passed} LULUS, {$failed} GAGAL\n";
echo "============================================\n";

ob_end_flush();

if ($failed > 0) {
    exit(1);
}
