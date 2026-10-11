<?php
// Catat barang keluar dengan pesimistic locking.
// FOR UPDATE mengunci baris produk selama transaksi agar dua request bersamaan
// tidak sama-sama lolos validasi stok (TOCTOU race condition).
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();

$errors     = [];
$old        = ['product_id' => 0, 'quantity' => '', 'note' => ''];
$stock_info = null; // diisi saat validasi gagal agar pesan error kaya konteks

$products = $pdo->query('SELECT id, sku, name, stock FROM products ORDER BY name ASC')
               ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $old['product_id'] = (int)($_POST['product_id'] ?? 0);
    $old['quantity']   = trim($_POST['quantity'] ?? '');
    $old['note']       = trim($_POST['note'] ?? '');
    $qty               = (int)$old['quantity'];

    if ($old['product_id'] <= 0)                                $errors[] = 'Pilih produk terlebih dahulu.';
    if (!ctype_digit((string)$old['quantity']) || $qty <= 0)    $errors[] = 'Jumlah harus bilangan bulat positif (> 0).';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // FOR UPDATE: kunci baris agar tidak ada transaksi lain yang
            // baca/ubah stok produk ini sebelum kita commit — mencegah stok negatif
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $suffix = $driver === 'mysql' ? ' FOR UPDATE' : '';
            $lock   = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?{$suffix}");
            $lock->execute([$old['product_id']]);
            $product = $lock->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new RuntimeException('Produk tidak ditemukan di database.');
            }

            if ((int)$product['stock'] < $qty) {
                $stock_info = (int)$product['stock'];
                throw new RuntimeException(
                    "Stok \"{$product['name']}\" tidak mencukupi. "
                    . "Tersedia: {$product['stock']} unit, diminta: {$qty} unit."
                );
            }

            $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')
                ->execute([$qty, $old['product_id']]);

            $pdo->prepare(
                'INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $old['product_id'],
                $_SESSION['user_id'],
                'OUT',
                $qty,
                $old['note'] !== '' ? $old['note'] : null,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Barang keluar {$qty} unit berhasil dicatat.";
            header('Location: /stock/history.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

$page_title = 'Barang Keluar';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="mb-6">
    <a href="/stock/history.php" class="text-sm text-indigo-600 hover:underline">← Riwayat Stok</a>
    <h1 class="mt-2 text-xl font-bold text-slate-800">Catat Barang Keluar</h1>
</div>

<div class="max-w-lg bg-white rounded-xl border border-slate-200 shadow-sm px-8 py-8">

    <?php if (!empty($errors)): ?>
    <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
        <?php if ($stock_info !== null): ?>
        <p class="mt-2 font-semibold">Stok saat ini: <?= $stock_info ?> unit. Kurangi jumlah yang diminta.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/stock/out.php" novalidate>
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
                    Jumlah Keluar <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input type="number" id="quantity" name="quantity" min="1"
                       value="<?= e($old['quantity']) ?>" placeholder="Contoh: 5"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <textarea id="note" name="note" rows="3" maxlength="500"
                          placeholder="Contoh: Pemakaian divisi teknis ruang server"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"><?= e($old['note']) ?></textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                Catat Keluar
            </button>
            <a href="/stock/history.php"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
