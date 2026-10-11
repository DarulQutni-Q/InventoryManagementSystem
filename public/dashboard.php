<?php
// Dashboard: 4 widget statistik bisnis + alert stok kritis + laporan agregat per produk.
// Query agregat di sini adalah yang dicontohkan dosen di slide P4 — bukan dekorasi.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';

requireLogin();

// ── 4 Stat Queries ────────────────────────────────────────────────────────
$total_products = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

$total_value = (float)$pdo->query('SELECT COALESCE(SUM(stock * price), 0) FROM products')->fetchColumn();

$low_stock_count = (int)$pdo->query(
    'SELECT COUNT(*) FROM products WHERE stock <= minimum_stock'
)->fetchColumn();

// Pisahkan total IN dan OUT agar bisa ditampilkan berdampingan sebagai perbandingan
$movement_row = $pdo->query(
    "SELECT
        COALESCE(SUM(CASE WHEN type = 'IN'  THEN quantity ELSE 0 END), 0) AS total_in,
        COALESCE(SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END), 0) AS total_out
     FROM stock_movements"
)->fetch(PDO::FETCH_ASSOC);
$total_in  = (int)$movement_row['total_in'];
$total_out = (int)$movement_row['total_out'];

// ── Produk Stok Kritis ────────────────────────────────────────────────────
$low_products = $pdo->query(
    'SELECT p.id, p.sku, p.name, p.stock, p.minimum_stock, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.stock <= p.minimum_stock
     ORDER BY p.stock ASC
     LIMIT 10'
)->fetchAll(PDO::FETCH_ASSOC);

// ── Laporan Agregat (persis query dari slide P4 dosen) ───────────────────
$report = $pdo->query(
    "SELECT p.id, p.sku, p.name, p.stock, c.name AS category_name,
            SUM(CASE WHEN sm.type = 'IN'  THEN sm.quantity ELSE 0 END) AS total_in,
            SUM(CASE WHEN sm.type = 'OUT' THEN sm.quantity ELSE 0 END) AS total_out
     FROM products p
     JOIN categories c ON c.id = p.category_id
     LEFT JOIN stock_movements sm ON sm.product_id = p.id
     GROUP BY p.id, p.sku, p.name, p.stock, c.name
     ORDER BY p.name ASC
     LIMIT 10"
)->fetchAll(PDO::FETCH_ASSOC);

function fmt_rupiah(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

$page_title = 'Dashboard';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<h1 class="text-xl font-bold text-slate-800 mb-6">Dashboard Inventori</h1>

<!-- ── 4 Widget Statistik ────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Produk</p>
        <p class="mt-2 text-3xl font-bold text-slate-800"><?= $total_products ?></p>
        <p class="mt-1 text-xs text-slate-400">jenis produk terdaftar</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nilai Inventaris</p>
        <p class="mt-2 text-2xl font-bold text-emerald-700 leading-tight"><?= fmt_rupiah($total_value) ?></p>
        <p class="mt-1 text-xs text-slate-400">total aset stok (stok × harga)</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stok Kritis</p>
        <p class="mt-2 text-3xl font-bold <?= $low_stock_count > 0 ? 'text-red-600' : 'text-slate-800' ?>">
            <?= $low_stock_count ?>
        </p>
        <p class="mt-1 text-xs text-slate-400">produk ≤ minimum stok</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pergerakan Stok</p>
        <p class="mt-2 text-sm font-bold text-slate-800">
            <span class="text-emerald-600">+<?= $total_in ?></span>
            <span class="text-slate-300 mx-1">/</span>
            <span class="text-red-600">−<?= $total_out ?></span>
        </p>
        <p class="mt-1 text-xs text-slate-400">unit masuk / keluar (lifetime)</p>
    </div>

</div>

<!-- ── Alert Stok Kritis ──────────────────────────────────────────────── -->
<?php if (!empty($low_products)): ?>
<div class="mb-8">
    <h2 class="text-base font-semibold text-slate-700 mb-3">
        ⚠️ Produk Perlu Restock
        <span class="ml-2 inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700"><?= count($low_products) ?></span>
    </h2>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-red-100 bg-red-50">
                <tr>
                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600">Produk</th>
                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600">Kategori</th>
                    <th class="px-4 py-2.5 text-right font-semibold text-slate-600">Stok</th>
                    <th class="px-4 py-2.5 text-right font-semibold text-slate-600">Minimum</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-red-50">
            <?php foreach ($low_products as $lp): ?>
                <tr class="hover:bg-red-50/50">
                    <td class="px-4 py-3">
                        <span class="font-medium text-slate-800"><?= e($lp['name']) ?></span>
                        <span class="block text-xs text-slate-400"><?= e($lp['sku']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-slate-500"><?= e($lp['category_name']) ?></td>
                    <td class="px-4 py-3 text-right font-bold text-red-600"><?= (int)$lp['stock'] ?></td>
                    <td class="px-4 py-3 text-right text-slate-500"><?= (int)$lp['minimum_stock'] ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Perlu Restock</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="/stock/in.php?product_id=<?= (int)$lp['id'] ?>"
                           class="rounded border border-emerald-300 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50">
                            + Stok Masuk
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Laporan Agregat Inventaris ─────────────────────────────────────── -->
<div>
    <h2 class="text-base font-semibold text-slate-700 mb-3">Laporan Agregat Inventaris</h2>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Produk (SKU)</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Kategori</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Stok</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-emerald-700">Total Masuk</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-red-600">Total Keluar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($report)): ?>
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada data produk.</td></tr>
            <?php else: foreach ($report as $row): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <span class="font-medium text-slate-800"><?= e($row['name']) ?></span>
                        <span class="block text-xs text-slate-400"><?= e($row['sku']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-slate-500"><?= e($row['category_name']) ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800"><?= (int)$row['stock'] ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-700">+<?= (int)$row['total_in'] ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-red-600">−<?= (int)$row['total_out'] ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-slate-400">Menampilkan 10 produk pertama · <a href="/products/index.php" class="underline hover:text-slate-600">Lihat semua produk</a></p>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
