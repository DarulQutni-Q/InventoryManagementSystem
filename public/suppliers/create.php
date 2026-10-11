<?php
// Form tambah supplier — hanya Admin yang boleh mengakses.
// GET: tampilkan form kosong. POST: validasi + simpan + redirect.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();
requireRole('admin');

$errors   = [];
$old      = ['name' => '', 'contact' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $old['name']    = trim($_POST['name']    ?? '');
    $old['contact'] = trim($_POST['contact'] ?? '');

    if ($old['name'] === '') {
        $errors[] = 'Nama supplier wajib diisi.';
    } elseif (mb_strlen($old['name']) > 150) {
        $errors[] = 'Nama supplier maksimal 150 karakter.';
    }

    if ($old['contact'] !== '' && mb_strlen($old['contact']) > 100) {
        $errors[] = 'Kontak maksimal 100 karakter.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO suppliers (name, contact) VALUES (?, ?)');
        $stmt->execute([
            $old['name'],
            $old['contact'] !== '' ? $old['contact'] : null,
        ]);
        $_SESSION['flash_success'] = "Supplier \"{$old['name']}\" berhasil ditambahkan.";
        header('Location: /suppliers/index.php');
        exit;
    }
}

$page_title = 'Tambah Supplier';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="mb-6">
    <a href="/suppliers/index.php" class="text-sm text-indigo-600 hover:underline">← Kembali ke daftar supplier</a>
    <h1 class="mt-2 text-xl font-bold text-slate-800">Tambah Supplier</h1>
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

    <form method="POST" action="/suppliers/create.php" novalidate>
        <?= csrf_field() ?>

        <div class="space-y-5 mb-6">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Supplier <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input type="text" id="name" name="name"
                       required maxlength="150"
                       value="<?= e($old['name']) ?>"
                       placeholder="Contoh: PT Sumber Makmur"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm
                              focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>

            <div>
                <label for="contact" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Kontak
                    <span class="text-slate-400 font-normal">(opsional — telepon, email, atau nama PIC)</span>
                </label>
                <input type="text" id="contact" name="contact"
                       maxlength="100"
                       value="<?= e($old['contact']) ?>"
                       placeholder="Contoh: 021-5551234 / sumber@email.com"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm
                              focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Simpan
            </button>
            <a href="/suppliers/index.php"
               class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600
                      hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                Batal
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
