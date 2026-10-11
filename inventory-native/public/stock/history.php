<?php
// Riwayat pergerakan stok: filter produk, tipe IN/OUT, rentang tanggal, pagination.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

requireLogin();

// filter parameter
$filter_product = (int)($_GET['product_id'] ?? 0);
$filter_type    = in_array($_GET['type'] ?? '', ['IN', 'OUT']) ? $_GET['type'] : '';
$filter_from    = $_GET['date_from'] ?? '';
$filter_to      = $_GET['date_to']   ?? '';
$order          = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$per_page       = 15;
$page           = max(1, (int)($_GET['page'] ?? 1));

// Kondisi WHERE dinamis — hanya bind parameter dari user input, bukan kolom/nilai statis
$conditions = [];
$params     = [];
if ($filter_product > 0) { $conditions[] = 'sm.product_id = ?';          $params[] = $filter_product; }
if ($filter_type !== '')  { $conditions[] = 'sm.type = ?';                $params[] = $filter_type;    }
if ($filter_from !== '')  { $conditions[] = 'DATE(sm.created_at) >= ?';   $params[] = $filter_from;    }
if ($filter_to   !== '')  { $conditions[] = 'DATE(sm.created_at) <= ?';   $params[] = $filter_to;      }

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_movements sm {$where}");
$count_stmt->execute($params);
$total       = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

$sql = "SELECT sm.id, sm.type, sm.quantity, sm.note, sm.created_at,
               p.name AS product_name, p.sku,
               u.username,
               COALESCE(up.full_name, u.username) AS actor
        FROM stock_movements sm
        JOIN products p      ON p.id  = sm.product_id
        JOIN users u         ON u.id  = sm.user_id
        LEFT JOIN user_profiles up ON up.user_id = u.id
        {$where}
        ORDER BY sm.created_at {$order}
        LIMIT {$per_page} OFFSET {$offset}";
$data_stmt = $pdo->prepare($sql);
$data_stmt->execute($params);
$movements = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Dropdown produk untuk filter
$all_products = $pdo->query('SELECT id, sku, name FROM products ORDER BY name ASC')
                    ->fetchAll(PDO::FETCH_ASSOC);

// Helper URL paginasi — pertahankan semua filter aktif saat pindah halaman
function hist_page_url(int $p): string {
    $q = $_GET; $q['page'] = $p;
    return '?' . http_build_query($q);
}

$page_title = 'Riwayat Stok';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800">Riwayat Pergerakan Stok</h1>
    <div class="flex gap-2">
        <a href="/stock/in.php"
           class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
            + Barang Masuk
        </a>
        <a href="/stock/out.php"
           class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
            − Barang Keluar
        </a>
    </div>
</div>

<!-- Panel filter -->
<form method="GET" action="/stock/history.php" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Produk</label>
        <select name="product_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="0">Semua Produk</option>
            <?php foreach ($all_products as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $filter_product === (int)$p['id'] ? 'selected' : '' ?>>
                [<?= e($p['sku']) ?>] <?= e($p['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
        <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="">Semua</option>
            <option value="IN"  <?= $filter_type === 'IN'  ? 'selected' : '' ?>>Masuk (IN)</option>
            <option value="OUT" <?= $filter_type === 'OUT' ? 'selected' : '' ?>>Keluar (OUT)</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
        <input type="date" name="date_from" value="<?= e($filter_from) ?>"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tanggal</label>
        <input type="date" name="date_to" value="<?= e($filter_to) ?>"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>
    <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Filter</button>
    <a href="/stock/history.php" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
</form>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-200 bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">
                    <a href="?<?= http_build_query(array_merge($_GET, ['order' => $order === 'DESC' ? 'ASC' : 'DESC'])) ?>"
                       class="hover:text-indigo-600">Waktu <?= $order === 'DESC' ? '↓' : '↑' ?></a>
                </th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Produk (SKU)</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Tipe</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Jumlah</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Dicatat Oleh</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php if (empty($movements)): ?>
            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Tidak ada riwayat untuk filter ini.</td></tr>
        <?php else: foreach ($movements as $mv): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?= date('d M Y H:i', strtotime($mv['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <span class="font-medium text-slate-800"><?= e($mv['product_name']) ?></span>
                    <span class="ml-1 text-xs text-slate-400"><?= e($mv['sku']) ?></span>
                </td>
                <td class="px-4 py-3 text-center">
                    <?php if ($mv['type'] === 'IN'): ?>
                    <span class="inline-block rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">MASUK</span>
                    <?php else: ?>
                    <span class="inline-block rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">KELUAR</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right font-semibold <?= $mv['type'] === 'IN' ? 'text-emerald-700' : 'text-red-600' ?>">
                    <?= $mv['type'] === 'IN' ? '+' : '−' ?><?= (int)$mv['quantity'] ?>
                </td>
                <td class="px-4 py-3 text-slate-600"><?= e($mv['actor']) ?></td>
                <td class="px-4 py-3 text-slate-400 max-w-xs truncate"><?= $mv['note'] ? e($mv['note']) : '—' ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
    <span><?= $total ?> transaksi &middot; halaman <?= $page ?> dari <?= $total_pages ?></span>
    <div class="flex gap-1">
        <?php for ($p = max(1, $page - 3); $p <= min($total_pages, $page + 3); $p++): ?>
        <a href="<?= hist_page_url($p) ?>"
           class="px-3 py-1 rounded-md <?= $p === $page ? 'bg-indigo-600 text-white font-semibold' : 'border border-slate-300 hover:bg-slate-50' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
