<?php
// views/auth/login.php — murni tampilan, TIDAK memproses form.
// Semua logika POST (cek password, regenerate session) ada di root login.php
// yang meng-include file ini setelah menyiapkan $error dan $old_username.
//
// Asumsi saat file ini di-include:
//   - session sudah aktif (dilakukan helpers/auth.php)
//   - csrf_field() tersedia (dari helpers/csrf.php)
//   - $error berisi string pesan error, atau null
//   - $old_username berisi nilai input username yang gagal (sticky form)

// Ambil error dari session flash jika root login.php pakai redirect-back
// (beberapa implementasi lebih suka flash daripada pass variable langsung)
if (!isset($error)) {
    $error = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_error']);
}
if (!isset($old_username)) {
    $old_username = $_SESSION['flash_old_username'] ?? '';
    unset($_SESSION['flash_old_username']);
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventoria</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased px-4">

<div class="w-full max-w-sm">

    <!-- Brand header — identitas aplikasi sebelum user masuk -->
    <div class="text-center mb-8">
        <span class="text-4xl leading-none">📦</span>
        <h1 class="mt-3 text-2xl font-bold text-slate-800 tracking-tight">Inventoria</h1>
        <p class="mt-1 text-sm text-slate-500">Sistem Manajemen Inventori</p>
    </div>

    <!-- Kartu form: border tipis + shadow ringan — bukan glassmorphism atau gradien -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-8 py-8">

        <h2 class="text-base font-semibold text-slate-700 mb-6">Masuk ke Akun Anda</h2>

        <?php if ($error): ?>
        <!-- Pesan error login: role="alert" agar screen reader membacanya otomatis -->
        <div role="alert"
             class="mb-5 flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 012 0v1a1 1 0 01-2 0v-1zm0-8a1 1 0 012 0v4a1 1 0 01-2 0V5z"
                      clip-rule="evenodd"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login.php" novalidate>

            <!-- CSRF wajib di setiap form POST — cegah cross-site request forgery -->
            <?= csrf_field() ?>

            <div class="space-y-5">

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Username
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        autofocus
                        value="<?= htmlspecialchars($old_username) ?>"
                        placeholder="Masukkan username"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 shadow-sm
                               placeholder-slate-400
                               focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                               transition-colors"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 shadow-sm
                               placeholder-slate-400
                               focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                               transition-colors"
                    >
                </div>
            </div>

            <button
                type="submit"
                class="mt-6 w-full flex justify-center items-center gap-2 rounded-lg
                       bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm
                       hover:bg-indigo-700 active:bg-indigo-800 active:scale-[0.99]
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition-all">
                Masuk
            </button>
        </form>
    </div>

    <!-- Hint akun demo: membantu dosen/penguji tanpa perlu buka dokumen lain -->
    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
        <p class="text-xs font-semibold text-amber-800 mb-2.5">🎓 Akun Demo untuk Penguji</p>
        <dl class="space-y-1 text-xs text-amber-700">
            <div class="flex gap-3">
                <dt class="w-12 font-medium text-amber-600 shrink-0">Admin</dt>
                <dd class="font-mono">admin &nbsp;/&nbsp; <strong>admin123</strong></dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-12 font-medium text-amber-600 shrink-0">Staff</dt>
                <dd class="font-mono">staff &nbsp;/&nbsp; <strong>staff123</strong></dd>
            </div>
        </dl>
    </div>

</div>
</body>
</html>
