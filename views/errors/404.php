<?php
// 404.php: halaman tidak ditemukan.
// Bisa dipanggil dari router (include + exit) atau langsung di-serve web server.
// Self-contained — tidak butuh session aktif agar tetap bisa tampil saat segala sesuatu rusak.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
http_response_code(404);

// Cek apakah user sudah login — menentukan tombol mana yang ditampilkan
$is_logged_in = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan | Inventoria</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased px-6">

<div class="w-full max-w-md text-center">

    <div class="text-[9rem] font-black text-slate-200 leading-none select-none">404</div>

    <h1 class="mt-1 text-2xl font-bold text-slate-800">Halaman Tidak Ditemukan</h1>

    <p class="mt-3 text-slate-500 text-sm leading-relaxed">
        URL yang Anda buka tidak ada di sistem ini.<br>
        Mungkin halaman telah dipindahkan atau Anda salah ketik alamatnya.
    </p>

    <!-- Tampilkan URL yang diminta agar user tahu apa yang salah -->
    <?php
    $bad_uri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8');
    if ($bad_uri && $bad_uri !== '/'):
    ?>
    <p class="mt-3 inline-block rounded-md bg-slate-100 px-3 py-1 font-mono text-xs text-slate-500 break-all">
        <?= $bad_uri ?>
    </p>
    <?php endif; ?>

    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">

        <?php if ($is_logged_in): ?>
        <a href="/dashboard.php"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                  hover:bg-indigo-700 active:bg-indigo-800
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Ke Dashboard
        </a>
        <?php else: ?>
        <a href="/login.php"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                  hover:bg-indigo-700 active:bg-indigo-800
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Login
        </a>
        <?php endif; ?>

        <a href="javascript:history.back()"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm
                  hover:bg-slate-50 active:bg-slate-100
                  focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-colors">
            ← Kembali
        </a>
    </div>

</div>
</body>
</html>
