<?php
/**
 * public/users/create.php
 * Form registrasi akun pengguna baru (khusus Administrator).
 *
 * Menerapkan standar keamanan OWASP:
 * - Hash password menggunakan algoritma bcrypt kuat via password_hash().
 * - Validasi regex format username untuk mencegah karakter berbahaya / injeksi.
 * - Penyimpanan atomik akun dan profil dalam transaksi database.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireRole('admin');

$errors = [];
$old = ['username' => '', 'full_name' => '', 'role' => 'staff'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['csrf'] = 'Token CSRF tidak valid atau sesi kadaluarsa. Silakan coba lagi.';
    }

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $role = (string)($_POST['role'] ?? 'staff');

    $old = ['username' => $username, 'full_name' => $fullName, 'role' => $role];

    // 1. Validasi Username (alphanumeric, min 3 max 50)
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors['username'] = 'Username harus berupa 3-50 karakter alfanumerik (huruf, angka, atau underscore).';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Username ini sudah digunakan oleh pengguna lain.';
        }
    }

    // 2. Validasi Password
    if (strlen($password) < 6) {
        $errors['password'] = 'Password wajib diisi minimal 6 karakter.';
    }

    // 3. Validasi Nama Lengkap
    if ($fullName === '' || mb_strlen($fullName) > 100) {
        $errors['full_name'] = 'Nama lengkap wajib diisi dan maksimal 100 karakter.';
    }

    // 4. Validasi Role
    if (!in_array($role, ['admin', 'staff'], true)) {
        $errors['role'] = 'Role harus berupa admin atau staff.';
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Simpan akun ke tabel users
            $userStmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $userStmt->execute([$username, $hashedPassword, $role]);
            $newUserId = (int)$pdo->lastInsertId();

            // Simpan profil ke tabel user_profiles (relasi 1-to-1)
            $profStmt = $pdo->prepare('INSERT INTO user_profiles (user_id, full_name) VALUES (?, ?)');
            $profStmt->execute([$newUserId, $fullName]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Pengguna baru '{$username}' ({$fullName}) berhasil didaftarkan.";
            header('Location: /users/index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['db'] = 'Gagal menyimpan pengguna baru: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Pengguna Baru';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tambah Pengguna Baru</h1>
            <p class="text-sm text-slate-500">Daftarkan akun dan tetapkan hak akses role sistem.</p>
        </div>
        <a href="/users/index.php" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Kembali</a>
    </div>

    <?php if (!empty($errors['csrf']) || !empty($errors['db'])): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg"><?= e($errors['csrf'] ?? $errors['db']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/users/create.php" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Username *</label>
            <input type="text" name="username" value="<?= e($old['username']) ?>" placeholder="Misal: staff_gudang2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <?php if (isset($errors['username'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['username']) ?></p><?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Lengkap *</label>
            <input type="text" name="full_name" value="<?= e($old['full_name']) ?>" placeholder="Misal: Budi Santoso" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <?php if (isset($errors['full_name'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['full_name']) ?></p><?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Password * (Min. 6 Karakter)</label>
            <input type="password" name="password" placeholder="••••••••" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <?php if (isset($errors['password'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['password']) ?></p><?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Hak Akses (Role) *</label>
            <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <option value="staff" <?= $old['role'] === 'staff' ? 'selected' : '' ?>>Staff Gudang (Operasional Produk & Transaksi)</option>
                <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Administrator (Akses Penuh Seluruh Sistem)</option>
            </select>
            <?php if (isset($errors['role'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['role']) ?></p><?php endif; ?>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="/users/index.php" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-medium">Batal</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">Daftarkan Pengguna</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
