<?php
// 403.php: halaman akses ditolak.
// Ditampilkan ketika user sudah login tapi rolenya kurang — bukan saat belum login.
// Sengaja self-contained (tidak include layout utama) supaya
// halaman error tidak bergantung pada sidebar atau session yang mungkin rusak.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak | Inventoria</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased px-6">

<div class="w-full max-w-md text-center">

    <!-- Kode status sebagai focal point visual — bukan ilustrasi abstrak yang tidak bermakna -->
    <div class="text-[9rem] font-black text-slate-200 leading-none select-none">403</div>

    <h1 class="mt-1 text-2xl font-bold text-slate-800">Akses Ditolak</h1>

    <p class="mt-3 text-slate-500 text-sm leading-relaxed">
        Anda tidak memiliki izin untuk membuka halaman ini.<br>
        Halaman ini hanya dapat diakses oleh <strong class="text-slate-700">Admin</strong>.
    </p>

    <!-- Strip konteks: tunjukkan siapa yang sedang login agar tidak bingung -->
    <?php if (!empty($_SESSION['username'])): ?>
    <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500">
        <span>Login sebagai</span>
        <strong class="text-slate-700"><?= htmlspecialchars($_SESSION['username']) ?></strong>
        <span class="px-1.5 py-0.5 rounded text-xs font-semibold
            <?= ($_SESSION['role'] ?? '') === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
            <?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'staff')) ?>
        </span>
    </p>
    <?php endif; ?>

    <!-- Beri user langkah konkret: jangan biarkan buntu di halaman error -->
    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="/dashboard.php"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                  hover:bg-indigo-700 active:bg-indigo-800
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kembali ke Dashboard
        </a>

        <a href="javascript:history.back()"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm
                  hover:bg-slate-50 active:bg-slate-100
                  focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-colors">
            ← Halaman Sebelumnya
        </a>
    </div>

    <p class="mt-8 text-xs text-slate-400">
        Jika Anda yakin ini keliru, hubungi Admin untuk mengubah hak akses Anda.
        &nbsp;·&nbsp;
        <a href="/logout.php" class="underline underline-offset-2 hover:text-slate-600">Ganti akun</a>
    </p>

</div>
</body>
</html>
