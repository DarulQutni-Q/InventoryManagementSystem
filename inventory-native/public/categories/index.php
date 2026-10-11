<?php
// Listing kategori: search LIKE, sorting allowlist, pagination 10/halaman.
// Aksi tambah/edit/hapus hanya tampil untuk Admin — proteksi backend ada di file aksinya.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();

$search = trim($_GET['search'] ?? '');

// Allowlist kolom sort — ORDER BY tidak bisa pakai prepared statement untuk nama kolom
$allowed_sort = ['name', 'created_at'];
$sort  = in_array($_GET['sort'] ?? '', $allowed_sort) ? $_GET['sort'] : 'name';
$order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));

$where  = $search !== '' ? 'WHERE name LIKE ?' : '';
$params = $search !== '' ? ["%{$search}%"] : [];

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM categories {$where}");
$total_stmt->execute($params);
$total       = (int)$total_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

// $sort/$order dari allowlist, $per_page/$offset bilangan bulat kita sendiri — aman diinterpolasi
$data_stmt = $pdo->prepare(
    "SELECT * FROM categories {$where} ORDER BY {$sort} {$order} LIMIT {$per_page} OFFSET {$offset}"
);
$data_stmt->execute($params);
$categories = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper URL sort: balik arah jika kolom yang sama diklik lagi
function sort_url(string $col, string $cur_sort, string $cur_order, string $search): string {
    $dir = ($cur_sort === $col && $cur_order === 'ASC') ? 'DESC' : 'ASC';
    return '?search=' . urlencode($search) . '&sort=' . $col . '&order=' . $dir;
}
function sort_icon(string $col, string $cur_sort, string $cur_order): string {
    if ($cur_sort !== $col) return '';
    return $cur_order === 'ASC' ? ' ↑' : ' ↓';
}

$page_title = 'Kategori';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800">Kategori</h1>
    <?php if (hasRole('admin')): ?>
    <a href="/categories/create.php"
       class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white
              hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Tambah Kategori
    </a>
    <?php endif; ?>
</div>

<!-- Form pencarian -->
<form method="GET" action="/categories/index.php" class="mb-4 flex gap-2">
    <input type="hidden" name="sort"  value="<?= e($sort) ?>">
    <input type="hidden" name="order" value="<?= e($order) ?>">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama kategori…"
           class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
    <button type="submit"
            class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
        Cari
    </button>
    <?php if ($search !== ''): ?>
    <a href="/categories/index.php"
       class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
        Reset
    </a>
    <?php endif; ?>
</form>

<!-- Tabel listing -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-200 bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 w-10">#</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">
                    <a href="<?= sort_url('name', $sort, $order, $search) ?>" class="hover:text-indigo-600">
                        Nama Kategori<?= sort_icon('name', $sort, $order) ?>
                    </a>
                </th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">
                    <a href="<?= sort_url('created_at', $sort, $order, $search) ?>" class="hover:text-indigo-600">
                        Dibuat<?= sort_icon('created_at', $sort, $order) ?>
                    </a>
                </th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php if (empty($categories)): ?>
            <tr>
                <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                    <?= $search !== '' ? 'Tidak ada kategori yang cocok.' : 'Belum ada data kategori.' ?>
                </td>
            </tr>
        <?php else: foreach ($categories as $i => $cat): ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-slate-400"><?= $offset + $i + 1 ?></td>
                <td class="px-4 py-3 font-medium text-slate-800"><?= e($cat['name']) ?></td>
                <td class="px-4 py-3 text-slate-500"><?= date('d M Y', strtotime($cat['created_at'])) ?></td>
                <td class="px-4 py-3 text-right">
                    <div class="inline-flex gap-2">
                        <?php if (hasRole('admin')): ?>
                        <a href="/categories/edit.php?id=<?= (int)$cat['id'] ?>"
                           class="rounded border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">Edit</a>
                        <form method="POST" action="/categories/delete.php" class="inline"
                              onsubmit="return confirm('Hapus kategori &quot;<?= e(addslashes($cat['name'])) ?>&quot;?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                            <button type="submit"
                                    class="rounded border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
    <span><?= $total ?> kategori &middot; halaman <?= $page ?> dari <?= $total_pages ?></span>
    <div class="flex gap-1">
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?search=<?= urlencode($search) ?>&sort=<?= e($sort) ?>&order=<?= e($order) ?>&page=<?= $p ?>"
           class="px-3 py-1 rounded-md <?= $p === $page ? 'bg-indigo-600 text-white font-semibold' : 'border border-slate-300 hover:bg-slate-50' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
