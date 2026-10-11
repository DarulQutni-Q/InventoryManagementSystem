<?php
// Form tambah kategori — hanya Admin yang boleh mengakses.
// GET: tampilkan form kosong. POST: validasi + simpan + redirect.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();
requireRole('admin');

$errors   = [];
$old_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(); // Abort jika token tidak cocok — bukan validasi form biasa

    $old_name = trim($_POST['name'] ?? '');

    if ($old_name === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    } elseif (mb_strlen($old_name) > 100) {
        $errors[] = 'Nama kategori maksimal 100 karakter.';
    }

    // Cek duplikat nama — nama kategori harus unik agar listing tidak membingungkan
    if (empty($errors)) {
        $dup = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
        $dup->execute([$old_name]);
        if ($dup->fetch()) {
            $errors[] = 'Kategori dengan nama tersebut sudah ada.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$old_name]);
        $_SESSION['flash_success'] = "Kategori \"{$old_name}\" berhasil ditambahkan.";
        header('Location: /categories/index.php');
        exit;
    }
}

$page_title = 'Tambah Kategori';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="mb-6">
    <a href="/categories/index.php" class="text-sm text-indigo-600 hover:underline">← Kembali ke daftar kategori</a>
    <h1 class="mt-2 text-xl font-bold text-slate-800">Tambah Kategori</h1>
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

    <form method="POST" action="/categories/create.php" novalidate>
        <?= csrf_field() ?>

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                Nama Kategori <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input type="text" id="name" name="name"
                   required maxlength="100"
                   value="<?= e($old_name) ?>"
                   placeholder="Contoh: Elektronik, Alat Tulis, Peralatan"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm
                          focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                          <?= !empty($errors) ? 'border-red-300' : '' ?>">
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Simpan
            </button>
            <a href="/categories/index.php"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600
                      hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                Batal
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
