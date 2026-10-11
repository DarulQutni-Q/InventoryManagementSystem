<?php
/**
 * tests/test_foundation.php
 * Test runner otomatis untuk memverifikasi skema database dan helper P4.
 */

declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set environment ke sqlite memory untuk pengujian terisolasi
putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=:memory:');
putenv('DB_FALLBACK_SQLITE=true');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

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

echo "=== 1. VERIFIKASI SCHEMA SQL & SEED DATA ===\n";
$schemaPath = __DIR__ . '/../database/schema.sql';
it("File database/schema.sql tersedia", file_exists($schemaPath));
$sqlContent = file_get_contents($schemaPath);

$requiredTables = ['users', 'user_profiles', 'categories', 'products', 'suppliers', 'product_suppliers', 'stock_movements'];
foreach ($requiredTables as $table) {
    it("Tabel '{$table}' terdefinisi dalam schema.sql", str_contains($sqlContent, "CREATE TABLE {$table}"));
}

it("Constraint UNIQUE pada users.username dan products.sku", 
    str_contains($sqlContent, 'username VARCHAR(100) NOT NULL UNIQUE') && 
    str_contains($sqlContent, 'sku VARCHAR(100) NOT NULL UNIQUE')
);

it("Constraint ON DELETE RESTRICT pada produk dan stock movements",
    str_contains($sqlContent, 'ON UPDATE CASCADE ON DELETE RESTRICT')
);

it("Constraint ON DELETE CASCADE pada product_suppliers",
    str_contains($sqlContent, 'ON UPDATE CASCADE ON DELETE CASCADE')
);

// Verifikasi hash bcrypt seed data
$adminHash = '$2y$12$huVRdYGG7/v3iGeaztRDbe1MTpBehNW.z5OGAKEThbT1Jvi/gbu.O';
$staffHash = '$2y$12$Q2x1GdubtlW771rq6zy5w.mdDMzzJbCqSiAR/Nth/BVYH02qFKABK';
it("Seed hash admin cocok dengan password 'admin123'", password_verify('admin123', $adminHash));
it("Seed hash staff cocok dengan password 'staff123'", password_verify('staff123', $staffHash));

echo "\n=== 2. VERIFIKASI KONEKSI DATABASE & FALLBACK ===\n";
$dbCheck = checkDatabaseConnection();
it("checkDatabaseConnection() melaporkan status terhubung", $dbCheck['connected'] === true);

$testDb = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$sqliteSql = convertMysqlToSqlite($sqlContent);
$testDb->exec($sqliteSql);

$userCount = (int)$testDb->query("SELECT COUNT(*) FROM users")->fetchColumn();
$productCount = (int)$testDb->query("SELECT COUNT(*) FROM products")->fetchColumn();
$supplierCount = (int)$testDb->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$movementCount = (int)$testDb->query("SELECT COUNT(*) FROM stock_movements WHERE type = 'IN'")->fetchColumn();

it("Seed users dimuat ke database ({$userCount} records)", $userCount === 2);
it("Seed products dimuat ke database ({$productCount} records)", $productCount >= 6);
it("Seed suppliers dimuat ke database ({$supplierCount} records)", $supplierCount >= 4);
it("Seed stock_movements IN tercatat ({$movementCount} records)", $movementCount >= 6);

echo "\n=== 3. VERIFIKASI HELPER ESCAPE (helpers/escape.php) ===\n";
it("e() meng-escape tag HTML kurung sudut", e('<script>alert("XSS")</script>') === '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;');
it("e() meng-escape tanda petik tunggal dan ganda", e("'\"") === '&#039;&quot;');
it("e() menangani null dengan aman tanpa error", e(null) === '');
it("e() mempertahankan teks normal", e('Inventaris Gudang 1') === 'Inventaris Gudang 1');

echo "\n=== 4. VERIFIKASI HELPER CSRF (helpers/csrf.php) ===\n";
$token1 = csrf_token();
$token2 = csrf_token();
it("csrf_token() menghasilkan string heksadesimal 64 karakter", strlen($token1) === 64 && ctype_xdigit($token1));
it("csrf_token() konsisten dalam sesi yang sama", $token1 === $token2);
it("csrf_field() menghasilkan tag hidden input HTML", str_contains(csrf_field(), 'type="hidden"') && str_contains(csrf_field(), $token1));
it("verify_csrf() mengembalikan true jika token cocok", verify_csrf($token1));
it("verify_csrf() mengembalikan false jika token salah", !verify_csrf('token_palsu_123'));

echo "\n=== 5. VERIFIKASI HELPER AUTH (helpers/auth.php) ===\n";
logoutUser();
it("isLoggedIn() false sebelum login", !isLoggedIn());
it("currentUser() null sebelum login", currentUser() === null);

loginUser(['id' => 1, 'username' => 'admin', 'role' => 'admin'], ['full_name' => 'Administrator Utama']);
it("isLoggedIn() true setelah login", isLoggedIn());
it("hasRole('admin') bernilai true", hasRole('admin'));
it("hasRole('staff') bernilai false untuk akun admin", !hasRole('staff'));
$user = currentUser();
it("currentUser() memuat id, username, role, dan full_name", 
    $user !== null && $user['username'] === 'admin' && $user['full_name'] === 'Administrator Utama'
);

logoutUser();
it("isLoggedIn() false setelah logout", !isLoggedIn());

echo "\n=== 6. VERIFIKASI HELPER VALIDASI (helpers/validation.php) ===\n";
it("validateSkuFormat menerima format SKU yang valid", validateSkuFormat('PROD-ELEC-001') && validateSkuFormat('SKU_123'));
it("validateSkuFormat menolak spasi dan karakter ilegal", !validateSkuFormat('SKU 001') && !validateSkuFormat('SKU#1'));
it("validatePositiveInteger memvalidasi angka positif", validatePositiveInteger(5) && validatePositiveInteger('10') && !validatePositiveInteger(0) && !validatePositiveInteger(-3));
it("validateNonNegativeInteger memvalidasi 0 dan angka positif", validateNonNegativeInteger(0) && validateNonNegativeInteger('8') && !validateNonNegativeInteger(-1));

$validProduct = ['name' => 'Monitor 24', 'category_id' => 1, 'sku' => 'MON-24-NEW', 'price' => 1500000, 'stock' => 10, 'minimum_stock' => 2];
it("validateProduct lolos pada payload valid", empty(validateProduct($validProduct, null, $testDb)));

$invalidProduct = ['name' => '', 'category_id' => 'abc', 'sku' => 'INVALID SKU!', 'price' => -500, 'stock' => -1, 'minimum_stock' => -2];
$productErrors = validateProduct($invalidProduct, null, $testDb);
it("validateProduct mendeteksi seluruh kesalahan input", 
    isset($productErrors['name'], $productErrors['category_id'], $productErrors['sku'], $productErrors['price'], $productErrors['stock'], $productErrors['minimum_stock'])
);

$validCat = ['name' => 'Kategori Baru'];
it("validateCategory lolos pada nama valid", empty(validateCategory($validCat)));
it("validateCategory mendeteksi nama kosong", !empty(validateCategory(['name' => '   '])));

$validSupplier = ['name' => 'PT Mitra Baru', 'contact' => '0812345'];
it("validateSupplier lolos pada data valid", empty(validateSupplier($validSupplier)));

$stockInValid = ['product_id' => 1, 'type' => 'IN', 'quantity' => 10];
it("validateStockMovement lolos untuk mutasi IN", empty(validateStockMovement($stockInValid, $testDb)));

$stockOutExceed = ['product_id' => 1, 'type' => 'OUT', 'quantity' => 9999];
$stockErrors = validateStockMovement($stockOutExceed, $testDb);
it("validateStockMovement mendeteksi OUT melebihi stok tersedia", !empty($stockErrors['quantity']));

echo "\n============================================\n";
echo "HASIL PENGUJIAN: {$passed} LULUS, {$failed} GAGAL\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
