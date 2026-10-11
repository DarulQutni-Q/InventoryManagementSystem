<?php
/**
 * public/products/index.php
 * Listing katalog produk inventaris dengan search, filter kategori, sorting, dan paginasi.
 *
 * Menerapkan prinsip pertahanan keamanan:
 * - Allowlist ketat pada ORDER BY kolom dan arah urutan untuk mencegah SQL Injection.
 * - Paginasi LIMIT & OFFSET dengan parameter numerik yang divalidasi.
 * - Proteksi CSRF pada tombol hapus produk (khusus role admin).
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();

$search = trim((string)($_GET['search'] ?? ''));
$categoryId = filter_var($_GET['category_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

// Allowlist nama kolom ORDER BY wajib diterapkan karena prepared statement tidak bisa mengamankan pengenal SQL
$allowedSort = ['name', 'sku', 'price', 'stock', 'created_at'];
$sort = in_array($_GET['sort'] ?? '', $allowedSort, true) ? $_GET['sort'] : 'name';
$dir = strtoupper((string)($_GET['dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Membangun kondisi WHERE dinamis dengan parameterized array
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($categoryId !== null) {
    $whereClauses[] = 'p.category_id = ?';
    $params[] = $categoryId;
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 1. Hitung total data untuk kalkulasi jumlah halaman
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p {$whereSql}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// 2. Ambil produk beserta nama kategori dan daftar rekanan supplier
$sql = "
    SELECT p.*, c.name AS category_name,
           GROUP_CONCAT(s.name) AS supplier_names
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_suppliers ps ON ps.product_id = p.id
    LEFT JOIN suppliers s ON s.id = ps.supplier_id
    {$whereSql}
    GROUP BY p.id
    ORDER BY p.{$sort} {$dir}
    LIMIT ? OFFSET ?
";

$dataStmt = $pdo->prepare($sql);
$dataParams = array_merge($params, [$perPage, $offset]);
$dataStmt->execute($dataParams);
$products = $dataStmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$page_title = 'Katalog Produk';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Katalog Produk</h1>
            <p class="text-sm text-slate-500">Kelola informasi barang, stok fisik, dan rekanan pemasok.</p>
        </div>
        <?php if (hasRole('admin')): ?>
            <a href="/products/create.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                <span>+ Tambah Produk</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="/products/index.php" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama produk / SKU..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        <select name="category_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Urutkan: Nama</option>
            <option value="sku" <?= $sort === 'sku' ? 'selected' : '' ?>>Urutkan: SKU</option>
            <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Urutkan: Harga</option>
            <option value="stock" <?= $sort === 'stock' ? 'selected' : '' ?>>Urutkan: Stok</option>
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Urutkan: Terbaru</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg text-sm font-medium transition">Terapkan</button>
            <a href="/products/index.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">Reset</a>
        </div>
    </form>

    <!-- Tabel Produk -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Nama Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-center">Stok Fisik</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada produk yang sesuai kriteria pencarian.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-mono text-xs font-medium text-slate-600"><?= e($p['sku']) ?></td>
                                <td class="px-4 py-3 font-medium text-slate-900"><?= e($p['name']) ?></td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700"><?= e($p['category_name']) ?></span></td>
                                <td class="px-4 py-3 text-right font-medium">Rp <?= number_format((float)$p['price'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ((int)$p['stock'] <= 0): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">Habis (0)</span>
                                    <?php elseif ((int)$p['stock'] <= (int)$p['minimum_stock']): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="Batas minimum: <?= (int)$p['minimum_stock'] ?>">Menipis (<?= (int)$p['stock'] ?>)</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><?= (int)$p['stock'] ?> unit</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate"><?= e($p['supplier_names'] ?: 'Belum ada') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (hasRole('admin')): ?>
                                        <div class="inline-flex items-center gap-2">
                                            <a href="/products/edit.php?id=<?= (int)$p['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Edit</a>
                                            <form method="POST" action="/products/delete.php" onsubmit="return confirm('Yakin ingin menghapus produk <?= e($p['name']) ?>?');" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-semibold">Hapus</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Lihat Saja</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <span>Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= $totalRows ?> produk)</span>
                <div class="flex gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $queryStr = http_build_query(array_merge($_GET, ['page' => $i])); ?>
                        <a href="/products/index.php?<?= $queryStr ?>" class="px-2.5 py-1 rounded <?= $i === $page ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
