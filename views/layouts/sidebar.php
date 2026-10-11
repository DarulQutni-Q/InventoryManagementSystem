<?php
// sidebar.php di-include oleh header.php — tidak dipanggil langsung.
// Deteksi halaman aktif pakai str_contains(URI, segment) agar tetap benar
// meskipun ada query string tambahan (?page=2, ?search=...).

// nav_class() dideklarasi sekali di sini; kalau di-include dua kali (edge case),
// function_exists() mencegah fatal error "cannot redeclare"
if (!function_exists('nav_class')) {
    function nav_class(string $segment): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $active = str_contains($uri, $segment);
        return $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-indigo-600 text-white'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 focus:ring-offset-slate-800';
    }
}

$is_admin = ($_SESSION['role'] ?? '') === 'admin';
?>

<!-- Desktop: fixed. Mobile: slide-in dari kiri, toggle via JS di footer.php -->
<aside id="sidebar"
       class="fixed top-14 left-0 bottom-0 w-64 bg-slate-800 z-20 overflow-y-auto flex flex-col justify-between
              -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out"
       aria-label="Navigasi utama">

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

        <p class="px-3 pt-1 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Utama</p>

        <a href="/dashboard.php" class="<?= nav_class('/dashboard') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <p class="px-3 pt-4 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Master Data</p>

        <a href="/categories/index.php" class="<?= nav_class('/categories') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            Kategori
        </a>

        <a href="/products/index.php" class="<?= nav_class('/products') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Produk
        </a>

        <a href="/suppliers/index.php" class="<?= nav_class('/suppliers') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-9 0a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            Supplier
        </a>

        <p class="px-3 pt-4 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Transaksi Stok</p>

        <a href="/stock/in.php" class="<?= nav_class('/stock/in') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Barang Masuk
        </a>

        <a href="/stock/out.php" class="<?= nav_class('/stock/out') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Barang Keluar
        </a>

        <a href="/stock/history.php" class="<?= nav_class('/stock/history') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Riwayat Stok
        </a>

        <?php if ($is_admin): ?>
        <!-- Disembunyikan di level view SEKALIGUS di level controller —
             defense in depth: view hide tidak cukup kalau URL langsung diakses -->
        <p class="px-3 pt-4 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Administrasi</p>

        <a href="/users/index.php" class="<?= nav_class('/users') ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Manajemen User
        </a>
        <?php endif; ?>

    </nav>

    <!-- Logout di pojok bawah sidebar: akses cepat tanpa scroll ke navbar atas -->
    <div class="px-3 py-4 border-t border-slate-700">
        <a href="/logout.php"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400
                  hover:bg-red-900/40 hover:text-red-300 transition-colors
                  focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 focus:ring-offset-slate-800">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
        </a>
    </div>
</aside>

<!-- Overlay gelap di mobile — klik untuk tutup sidebar -->
<div id="sidebar-overlay"
     class="fixed inset-0 z-10 bg-black/40 hidden lg:hidden"
     aria-hidden="true"></div>
