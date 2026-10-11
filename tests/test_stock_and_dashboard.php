<?php
/**
 * tests/test_stock_and_dashboard.php
 * Uji integrasi transaksi stok IN, OUT (sukses & gagal), dan query dashboard.
 * Jalankan dari CLI: php tests/test_stock_and_dashboard.php
 *
 * Strategi: buat data uji sementara, jalankan semua test, cleanup di akhir.
 * register_shutdown_function menjamin cleanup jalan meski test tengah gagal.
 */

define('ROOT', dirname(__DIR__) . '/');
require_once ROOT . 'config/database.php';

// ── Infrastruktur Test ────────────────────────────────────────────────────
$pass = 0;
$fail = 0;

function assert_true(string $name, bool $cond): void {
    global $pass, $fail;
    if ($cond) { echo "[PASS] {$name}\n"; $pass++; }
    else        { echo "[FAIL] {$name}\n"; $fail++; }
}

function assert_equal(string $name, $expected, $actual): void {
    assert_true("{$name} (expected={$expected}, got={$actual})", $expected === $actual);
}

// ── Setup: buat data uji terisolasi ──────────────────────────────────────
$test_cat_id  = null;
$test_prod_id = null;
$test_user_id = null;

// Cleanup otomatis saat script selesai/crash — urutan penting karena ada FK
register_shutdown_function(function () use ($pdo, &$test_cat_id, &$test_prod_id, &$test_user_id) {
    if ($test_prod_id) {
        $pdo->prepare('DELETE FROM stock_movements WHERE product_id = ?')->execute([$test_prod_id]);
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$test_prod_id]);
    }
    if ($test_cat_id) {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$test_cat_id]);
    }
    if ($test_user_id) {
        $pdo->prepare('DELETE FROM user_profiles WHERE user_id = ?')->execute([$test_user_id]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$test_user_id]);
    }
    echo "\n[CLEANUP] Data uji dihapus.\n";
});

try {
    // Buat kategori uji
    $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute(['__TEST_KATEGORI__']);
    $test_cat_id = (int)$pdo->lastInsertId();

    // Buat produk uji dengan stok awal 10
    $pdo->prepare(
        'INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$test_cat_id, '__TEST-SKU-001__', '__TEST Produk__', 100000, 10, 5]);
    $test_prod_id = (int)$pdo->lastInsertId();

    // Buat user uji
    $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)')->execute([
        '__testuser__', password_hash('testpass', PASSWORD_BCRYPT), 'staff'
    ]);
    $test_user_id = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO user_profiles (user_id, full_name) VALUES (?, ?)')->execute([$test_user_id, 'Test User']);

} catch (Throwable $e) {
    echo "[ERROR] Setup data uji gagal: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== TEST: Transaksi Stok Masuk (IN) ===\n";

// Test 1: Stok bertambah setelah INSERT IN + UPDATE stock
try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([5, $test_prod_id]);
    $pdo->prepare(
        'INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)'
    )->execute([$test_prod_id, $test_user_id, 'IN', 5, 'test masuk']);
    $pdo->commit();

    $stock_after = (int)$pdo->prepare('SELECT stock FROM products WHERE id = ?')
                             ->execute([$test_prod_id]) ?: 0;
    // Re-fetch
    $s = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $s->execute([$test_prod_id]);
    $stock_after = (int)$s->fetchColumn();
    assert_equal('Stok bertambah 5 setelah IN', 15, $stock_after);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    assert_true('Transaksi IN tidak boleh throw: ' . $e->getMessage(), false);
}

// Test 2: stock_movements menyimpan record IN dengan benar
$mv = $pdo->prepare('SELECT * FROM stock_movements WHERE product_id = ? AND type = ? ORDER BY id DESC LIMIT 1');
$mv->execute([$test_prod_id, 'IN']);
$latest = $mv->fetch(PDO::FETCH_ASSOC);
assert_true('Record IN tersimpan di stock_movements', $latest !== false);
assert_equal('Kolom type = IN', 'IN', $latest['type'] ?? null);
assert_equal('Kolom quantity = 5', 5, (int)($latest['quantity'] ?? 0));

echo "\n=== TEST: Transaksi Stok Keluar (OUT) — Sukses ===\n";

// Test 3: Stok berkurang setelah OUT valid (stok=15, ambil 3)
try {
    $pdo->beginTransaction();
    $driver  = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $suffix  = $driver === 'mysql' ? ' FOR UPDATE' : '';
    $lk      = $pdo->prepare("SELECT stock FROM products WHERE id = ?{$suffix}");
    $lk->execute([$test_prod_id]);
    $cur_stock = (int)$lk->fetchColumn();

    assert_true('Stok sebelum OUT = 15', $cur_stock === 15);

    if ($cur_stock < 3) throw new RuntimeException("Stok tidak mencukupi.");
    $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([3, $test_prod_id]);
    $pdo->prepare(
        'INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)'
    )->execute([$test_prod_id, $test_user_id, 'OUT', 3, 'test keluar valid']);
    $pdo->commit();

    $s2 = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $s2->execute([$test_prod_id]);
    assert_equal('Stok berkurang 3 setelah OUT', 12, (int)$s2->fetchColumn());
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    assert_true('Transaksi OUT sukses tidak boleh throw: ' . $e->getMessage(), false);
}

echo "\n=== TEST: Transaksi Stok Keluar (OUT) — Gagal & Rollback ===\n";

// Test 4: Coba ambil lebih dari stok (12), harus throw dan rollback
$stock_before_fail = (int)$pdo->prepare('SELECT stock FROM products WHERE id = ?')
                               ->execute([$test_prod_id]) ?: 12;
$sf = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$sf->execute([$test_prod_id]);
$stock_before_fail = (int)$sf->fetchColumn();

$threw = false;
try {
    $pdo->beginTransaction();
    $driver2 = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $suffix2 = $driver2 === 'mysql' ? ' FOR UPDATE' : '';
    $lk2     = $pdo->prepare("SELECT stock FROM products WHERE id = ?{$suffix2}");
    $lk2->execute([$test_prod_id]);
    $avail = (int)$lk2->fetchColumn();

    if ($avail < 999) {
        throw new RuntimeException("Stok tidak mencukupi. Tersedia: {$avail}, diminta: 999.");
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $threw = true;
    assert_true('Exception terlempar saat stok kurang', str_contains($e->getMessage(), 'tidak mencukupi'));
}

assert_true('Exception terlempar (flag $threw)', $threw);

// Stok harus tetap sama setelah rollback
$sa = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$sa->execute([$test_prod_id]);
$stock_after_fail = (int)$sa->fetchColumn();
assert_equal('Stok tidak berubah setelah rollback', $stock_before_fail, $stock_after_fail);

echo "\n=== TEST: Query Dashboard ===\n";

// Test 5: Query total produk mengembalikan integer >= 0
$total_prod = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
assert_true('COUNT products >= 0', $total_prod >= 0);

// Test 6: Query nilai inventaris mengembalikan float >= 0
$total_val = (float)$pdo->query('SELECT COALESCE(SUM(stock * price), 0) FROM products')->fetchColumn();
assert_true('SUM nilai inventaris >= 0', $total_val >= 0.0);

// Test 7: Query stok kritis tidak error
$low = $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= minimum_stock')->fetchColumn();
assert_true('COUNT stok kritis tidak error', is_numeric($low));

// Test 8: Query agregat mengembalikan kolom yang benar
$agg = $pdo->query(
    "SELECT p.id, p.sku, p.name, p.stock, c.name AS category_name,
            SUM(CASE WHEN sm.type = 'IN'  THEN sm.quantity ELSE 0 END) AS total_in,
            SUM(CASE WHEN sm.type = 'OUT' THEN sm.quantity ELSE 0 END) AS total_out
     FROM products p
     JOIN categories c ON c.id = p.category_id
     LEFT JOIN stock_movements sm ON sm.product_id = p.id
     GROUP BY p.id, p.sku, p.name, p.stock, c.name
     ORDER BY p.name ASC LIMIT 10"
)->fetchAll(PDO::FETCH_ASSOC);
assert_true('Query agregat mengembalikan array', is_array($agg));
if (!empty($agg)) {
    $first = $agg[0];
    assert_true('Kolom total_in ada', array_key_exists('total_in', $first));
    assert_true('Kolom total_out ada', array_key_exists('total_out', $first));
    assert_true('Kolom category_name ada', array_key_exists('category_name', $first));
}

// ── Ringkasan ─────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 40) . "\n";
echo "Hasil: {$pass} PASS, {$fail} FAIL\n";
exit($fail > 0 ? 1 : 0);
