<?php
/**
 * public/products/create.php
 * Form penambahan master produk baru dengan relasi multi-supplier dan inisialisasi stok.
 *
 * Menerapkan prinsip ACID Transaction:
 * - Penyimpanan produk, relasi pivot supplier, dan pencatatan histori stok awal (IN)
 *   wajib berada dalam satu transaksi terisolasi (BEGIN / COMMIT / ROLLBACK).
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/validation.php';

requireRole('admin');

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$suppliers = $pdo->query('SELECT id, name FROM suppliers ORDER BY name ASC')->fetchAll();

$errors = [];
$old = [
    'sku' => '', 'name' => '', 'category_id' => '', 'price' => '0',
    'stock' => '0', 'minimum_stock' => '5', 'suppliers' => [], 'supplier_prices' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['csrf'] = 'Token CSRF tidak valid atau sesi kadaluarsa. Silakan coba lagi.';
    }

    $old = [
        'sku' => trim((string)($_POST['sku'] ?? '')),
        'name' => trim((string)($_POST['name'] ?? '')),
        'category_id' => (string)($_POST['category_id'] ?? ''),
        'price' => (string)($_POST['price'] ?? '0'),
        'stock' => (string)($_POST['stock'] ?? '0'),
        'minimum_stock' => (string)($_POST['minimum_stock'] ?? '0'),
        'suppliers' => (array)($_POST['suppliers'] ?? []),
        'supplier_prices' => (array)($_POST['supplier_prices'] ?? [])
    ];

    $validationErrors = validateProduct($old, null, $pdo);
    $errors = array_merge($errors, $validationErrors);

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('
                INSERT INTO products (category_id, sku, name, price, stock, minimum_stock)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                (int)$old['category_id'], $old['sku'], $old['name'],
                (float)$old['price'], (int)$old['stock'], (int)$old['minimum_stock']
            ]);
            $productId = (int)$pdo->lastInsertId();

            // Simpan relasi supplier yang dicentang
            if (!empty($old['suppliers'])) {
                $psStmt = $pdo->prepare('INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)');
                foreach ($old['suppliers'] as $supId) {
                    $supPrice = isset($old['supplier_prices'][$supId]) && is_numeric($old['supplier_prices'][$supId])
                        ? (float)$old['supplier_prices'][$supId] : null;
                    $psStmt->execute([$productId, (int)$supId, $supPrice]);
                }
            }

            // Catat stok awal ke buku besar stock_movements jika kuantitas > 0
            if ((int)$old['stock'] > 0) {
                $smStmt = $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)');
                $smStmt->execute([$productId, (int)$_SESSION['user_id'], 'IN', (int)$old['stock'], 'Penerimaan stok awal saat penambahan produk baru']);
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Produk '{$old['name']}' berhasil disimpan ke katalog.";
            header('Location: /products/index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['db'] = 'Gagal menyimpan transaksi produk: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Produk Baru';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Tambah Produk Baru</h1>
        <a href="/products/index.php" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <?php if (!empty($errors['csrf']) || !empty($errors['db'])): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg"><?= e($errors['csrf'] ?? $errors['db']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/products/create.php" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">SKU (Kode Unik) *</label>
                <input type="text" name="sku" value="<?= e($old['sku']) ?>" placeholder="Misal: PROD-ELEC-009" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['sku'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['sku']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kategori *</label>
                <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">Pilih Kategori...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (string)$old['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['category_id']) ?></p><?php endif; ?>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Produk *</label>
            <input type="text" name="name" value="<?= e($old['name']) ?>" placeholder="Misal: Monitor IPS 27 Inch 4K" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <?php if (isset($errors['name'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['name']) ?></p><?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Harga Jual (Rp) *</label>
                <input type="number" step="0.01" name="price" value="<?= e($old['price']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['price'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['price']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Stok Awal Fisik *</label>
                <input type="number" name="stock" value="<?= e($old['stock']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['stock'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['stock']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Batas Minimum Stok *</label>
                <input type="number" name="minimum_stock" value="<?= e($old['minimum_stock']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['minimum_stock'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['minimum_stock']) ?></p><?php endif; ?>
            </div>
        </div>

        <!-- Multi-Supplier Section -->
        <div class="border-t border-slate-200 pt-4">
            <h2 class="text-sm font-bold text-slate-700 mb-2">Pemasok / Rekanan Supplier (Opsional)</h2>
            <div class="space-y-2 max-h-48 overflow-y-auto p-2 bg-slate-50 rounded-lg border border-slate-200">
                <?php foreach ($suppliers as $s): ?>
                    <?php $checked = in_array((string)$s['id'], array_map('strval', $old['suppliers']), true); ?>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="suppliers[]" value="<?= (int)$s['id'] ?>" id="sup_<?= (int)$s['id'] ?>" <?= $checked ? 'checked' : '' ?> class="rounded text-indigo-600 focus:ring-indigo-500">
                        <label for="sup_<?= (int)$s['id'] ?>" class="text-sm flex-1 text-slate-700 select-none"><?= e($s['name']) ?></label>
                        <input type="number" step="0.01" name="supplier_prices[<?= (int)$s['id'] ?>]" value="<?= e($old['supplier_prices'][$s['id']] ?? '') ?>" placeholder="Harga pasokan (Rp)" class="w-40 rounded border border-slate-300 px-2 py-1 text-xs">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="/products/index.php" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-medium">Batal</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">Simpan Produk</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
