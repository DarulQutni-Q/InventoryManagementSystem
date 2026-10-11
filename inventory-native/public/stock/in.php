<?php
// Catat barang masuk: update stok dan simpan ke histori dalam satu transaksi atomik.
// Semua user yang login boleh mencatat barang masuk — bukan hanya admin.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();

$errors = [];
$old    = ['product_id' => 0, 'quantity' => '', 'note' => ''];

// Dropdown produk: tampilkan stok saat ini agar user tahu kondisi sebelum mencatat
$products = $pdo->query('SELECT id, sku, name, stock FROM products ORDER BY name ASC')
               ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $old['product_id'] = (int)($_POST['product_id'] ?? 0);
    $old['quantity']   = trim($_POST['quantity'] ?? '');
    $old['note']       = trim($_POST['note'] ?? '');
    $qty               = (int)$old['quantity'];

    if ($old['product_id'] <= 0) {
        $errors[] = 'Pilih produk terlebih dahulu.';
    }
    if (!ctype_digit((string)$old['quantity']) || $qty <= 0) {
        $errors[] = 'Jumlah harus bilangan bulat positif (> 0).';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')
                ->execute([$qty, $old['product_id']]);

            $pdo->prepare(
                'INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $old['product_id'],
                $_SESSION['user_id'],
                'IN',
                $qty,
                $old['note'] !== '' ? $old['note'] : null,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Barang masuk {$qty} unit berhasil dicatat.";
            header('Location: /stock/history.php');
            exit;
        } catch (Throwable $e) {
            // Rollback juga saat Throwable, bukan hanya Exception —
            // TypeError/Error PHP pun wajib membatalkan transaksi
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Transaksi gagal disimpan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Barang Masuk';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="mb-6">
    <a href="/stock/history.php" class="text-sm text-indigo-600 hover:underline">← Riwayat Stok</a>
    <h1 class="mt-2 text-xl font-bold text-slate-800">Catat Barang Masuk</h1>
</div>

<div class="max-w-lg bg-white rounded-xl border border-slate-200 shadow-sm px-8 py-8">

    <?php if (!empty($errors)): ?>
    <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="/stock/in.php" novalidate>
        <?= csrf_field() ?>

        <div class="space-y-5 mb-6">
            <div>
                <label for="product_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Produk <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <select id="product_id" name="product_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="0">— Pilih Produk —</option>
                    <?php foreach ($products as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= (int)$old['product_id'] === (int)$p['id'] ? 'selected' : '' ?>>
                        [<?= e($p['sku']) ?>] <?= e($p['name']) ?> &nbsp;(Stok: <?= (int)$p['stock'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Jumlah Masuk <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input type="number" id="quantity" name="quantity" min="1"
                       value="<?= e($old['quantity']) ?>" placeholder="Contoh: 10"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <textarea id="note" name="note" rows="3" maxlength="500"
                          placeholder="Contoh: Penerimaan dari supplier PT Maju Bersama"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"><?= e($old['note']) ?></textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                Catat Masuk
            </button>
            <a href="/stock/history.php"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
