<?php
// Form edit kategori — hanya Admin.
// GET: ambil data dari DB, prefill form. POST: validasi + update + redirect.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID kategori tidak valid.';
    header('Location: /categories/index.php');
    exit;
}

// Ambil data kategori yang akan diedit — 404 jika tidak ditemukan
$row_stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$row_stmt->execute([$id]);
$category = $row_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['flash_error'] = 'Kategori tidak ditemukan.';
    header('Location: /categories/index.php');
    exit;
}

$errors   = [];
$old_name = $category['name']; // prefill dari DB, bisa di-overwrite saat POST gagal

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $old_name = trim($_POST['name'] ?? '');

    if ($old_name === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    } elseif (mb_strlen($old_name) > 100) {
        $errors[] = 'Nama kategori maksimal 100 karakter.';
    }

    // Cek duplikat: nama sudah dipakai kategori LAIN (bukan diri sendiri)
    if (empty($errors)) {
        $dup = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
        $dup->execute([$old_name, $id]);
        if ($dup->fetch()) {
            $errors[] = 'Kategori dengan nama tersebut sudah ada.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $stmt->execute([$old_name, $id]);
        $_SESSION['flash_success'] = "Kategori \"{$old_name}\" berhasil diperbarui.";
        header('Location: /categories/index.php');
        exit;
    }
}

$page_title = 'Edit Kategori';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="mb-6">
    <a href="/categories/index.php" class="text-sm text-indigo-600 hover:underline">← Kembali ke daftar kategori</a>
    <h1 class="mt-2 text-xl font-bold text-slate-800">Edit Kategori</h1>
</div>

<div class="max-w-lg bg-white rounded-xl border border-slate-200 shadow-sm px-8 py-8">

    <?php if (!empty($errors)): ?>
    <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="/categories/edit.php" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                Nama Kategori <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="name" name="name"
                   required maxlength="100"
                   value="<?= e($old_name) ?>"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm
                          focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                          <?= !empty($errors) ? 'border-red-300' : '' ?>">
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Perbarui
            </button>
            <a href="/categories/index.php"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600
                      hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                Batal
            </a>
        </div>
    </form>

    <!-- Metadata bawaan — berguna saat debugging atau audit -->
    <p class="mt-6 text-xs text-slate-400">
        Dibuat: <?= date('d M Y H:i', strtotime($category['created_at'])) ?>
        &nbsp;·&nbsp;
        Diperbarui: <?= date('d M Y H:i', strtotime($category['updated_at'])) ?>
    </p>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
