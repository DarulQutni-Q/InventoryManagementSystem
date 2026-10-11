<?php
/**
 * public/users/index.php
 * Manajemen pengguna sistem inventaris (khusus Administrator).
 *
 * Mengapa proteksi RBAC ketat di sini (FR-AUTH-06 & FR-AUTH-07):
 * - Staff operasional gudang sama sekali tidak boleh melihat daftar kredensial,
 *   role, maupun profil pengguna lain untuk mencegah eskalasi hak akses (privilege escalation).
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';

// Verifikasi role wajib di baris paling awal sebelum pembacaan basis data
requireRole('admin');

$search = trim((string)($_GET['search'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereClause = '';
$params = [];

if ($search !== '') {
    $whereClause = 'WHERE (u.username LIKE ? OR up.full_name LIKE ?)';
    $params = ["%{$search}%", "%{$search}%"];
}

// 1. Hitung total pengguna untuk paginasi
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM users u 
    LEFT JOIN user_profiles up ON up.user_id = u.id 
    {$whereClause}
");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// 2. Ambil data pengguna beserta relasi profil (1-to-1)
$sql = "
    SELECT u.id, u.username, u.role, u.created_at, up.full_name
    FROM users u
    LEFT JOIN user_profiles up ON up.user_id = u.id
    {$whereClause}
    ORDER BY u.id ASC
    LIMIT ? OFFSET ?
";

$stmt = $pdo->prepare($sql);
$execParams = array_merge($params, [$perPage, $offset]);
$stmt->execute($execParams);
$users = $stmt->fetchAll();

$page_title = 'Manajemen Pengguna';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500">Kelola akun personel, pembagian role otorisasi, dan detail profil.</p>
        </div>
        <a href="/users/create.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            <span>+ Tambah Pengguna Baru</span>
        </a>
    </div>

    <!-- Filter Pencarian -->
    <form method="GET" action="/users/index.php" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari username atau nama lengkap..." class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        <div class="flex gap-2">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Cari</button>
            <a href="/users/index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">Reset</a>
        </div>
    </form>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Nama Lengkap</th>
                        <th class="px-4 py-3 text-center">Hak Akses (Role)</th>
                        <th class="px-4 py-3">Tanggal Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data pengguna yang sesuai.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= (int)$u['id'] ?></td>
                                <td class="px-4 py-3 font-mono font-medium text-slate-900"><?= e($u['username']) ?></td>
                                <td class="px-4 py-3 font-medium text-slate-800"><?= e($u['full_name'] ?: '-') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Admin</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Staff Gudang</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <span>Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= $totalRows ?> pengguna)</span>
                <div class="flex gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $queryStr = http_build_query(array_merge($_GET, ['page' => $i])); ?>
                        <a href="/users/index.php?<?= $queryStr ?>" class="px-2.5 py-1 rounded <?= $i === $page ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
