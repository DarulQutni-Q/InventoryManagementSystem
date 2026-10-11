<?php
/**
 * public/products/edit.php
 * Form pengubahan master data produk dan sinkronisasi rekanan supplier.
 *
 * Menerapkan business rule:
 * - Stok fisik tidak diubah sembarangan di form edit — stok hanya boleh berubah
 *   melalui transaksi resmi barang masuk/keluar di buku besar stock_movements.
 * - Keunikan SKU divalidasi terhadap seluruh produk lain (mengecualikan produk saat ini).
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/validation.php';

requireRole('admin');

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_error'] = 'ID produk tidak valid.';
    header('Location: /products/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan di basis data.';
    header('Location: /products/index.php');
    exit;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$suppliers = $pdo->query('SELECT id, name FROM suppliers ORDER BY name ASC')->fetchAll();

// Ambil supplier yang sudah terhubung dengan produk ini
$psStmt = $pdo->prepare('SELECT supplier_id, supplier_price FROM product_suppliers WHERE product_id = ?');
$psStmt->execute([$id]);
$existingSuppliers = [];
$existingPrices = [];
foreach ($psStmt->fetchAll() as $ps) {
    $existingSuppliers[] = (string)$ps['supplier_id'];
    $existingPrices[$ps['supplier_id']] = $ps['supplier_price'];
}

$errors = [];
$old = [
    'sku' => $product['sku'], 'name' => $product['name'], 'category_id' => (string)$product['category_id'],
    'price' => (string)$product['price'], 'minimum_stock' => (string)$product['minimum_stock'],
    'suppliers' => $existingSuppliers, 'supplier_prices' => $existingPrices
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['csrf'] = 'Token CSRF tidak valid atau sesi kadaluarsa.';
    }

    $old = [
        'sku' => trim((string)($_POST['sku'] ?? '')),
        'name' => trim((string)($_POST['name'] ?? '')),
        'category_id' => (string)($_POST['category_id'] ?? ''),
        'price' => (string)($_POST['price'] ?? '0'),
        'minimum_stock' => (string)($_POST['minimum_stock'] ?? '0'),
        'suppliers' => (array)($_POST['suppliers'] ?? []),
        'supplier_prices' => (array)($_POST['supplier_prices'] ?? [])
    ];

    $validationErrors = validateProduct($old, $id, $pdo);
    $errors = array_merge($errors, $validationErrors);

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $updateStmt = $pdo->prepare('
                UPDATE products
                SET category_id = ?, sku = ?, name = ?, price = ?, minimum_stock = ?
                WHERE id = ?
            ');
            $updateStmt->execute([
                (int)$old['category_id'], $old['sku'], $old['name'],
                (float)$old['price'], (int)$old['minimum_stock'], $id
            ]);

            // Sinkronisasi relasi supplier: hapus lama lalu insert relasi baru
            $pdo->prepare('DELETE FROM product_suppliers WHERE product_id = ?')->execute([$id]);
            if (!empty($old['suppliers'])) {
                $insPs = $pdo->prepare('INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)');
                foreach ($old['suppliers'] as $supId) {
                    $supPrice = isset($old['supplier_prices'][$supId]) && is_numeric($old['supplier_prices'][$supId])
                        ? (float)$old['supplier_prices'][$supId] : null;
                    $insPs->execute([$id, (int)$supId, $supPrice]);
                }
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Data produk '{$old['name']}' berhasil diperbarui.";
            header('Location: /products/index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['db'] = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Produk: ' . $product['name'];
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Produk</h1>
            <p class="text-sm text-slate-500">Perbarui rincian katalog dan relasi supplier.</p>
        </div>
        <a href="/products/index.php" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <?php if (!empty($errors['csrf']) || !empty($errors['db'])): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg"><?= e($errors['csrf'] ?? $errors['db']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/products/edit.php?id=<?= $id ?>" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">SKU (Kode Unik) *</label>
                <input type="text" name="sku" value="<?= e($old['sku']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['sku'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['sku']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kategori *</label>
                <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (string)$old['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['category_id']) ?></p><?php endif; ?>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Produk *</label>
            <input type="text" name="name" value="<?= e($old['name']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <?php if (isset($errors['name'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['name']) ?></p><?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Harga Jual (Rp) *</label>
                <input type="number" step="0.01" name="price" value="<?= e($old['price']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['price'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['price']) ?></p><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Stok Fisik Saat Ini</label>
                <div class="px-3 py-2 bg-slate-100 rounded-lg text-sm font-semibold text-slate-700"><?= (int)$product['stock'] ?> unit (Kelola di Transaksi Stok)</div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Batas Minimum Stok *</label>
                <input type="number" name="minimum_stock" value="<?= e($old['minimum_stock']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <?php if (isset($errors['minimum_stock'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['minimum_stock']) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-4">
            <h2 class="text-sm font-bold text-slate-700 mb-2">Pemasok / Rekanan Supplier</h2>
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
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
