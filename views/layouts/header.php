<?php
// header.php adalah titik masuk semua halaman terproteksi.
// Dipanggil paling pertama oleh setiap view agar HTML tetap valid —
// <head>, navbar, dan sidebar di-render di sini, bukan per-halaman.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title   = $page_title   ?? 'Inventoria';
$current_user = $_SESSION['username']  ?? 'Pengguna';
$current_role = $_SESSION['role']      ?? 'staff';
$full_name    = $_SESSION['full_name'] ?? $current_user;

// REQUEST_URI dipakai sidebar.php untuk highlight menu aktif
$current_uri  = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= htmlspecialchars($page_title) ?> — Inventoria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Perpanjang palet Tailwind dengan warna brand agar konsisten
        tailwind.config = {
            theme: { extend: { colors: {
                brand: { DEFAULT: '#4f46e5', dark: '#3730a3', light: '#e0e7ff' }
            }}}
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800">

<!-- navbar atas -->
<!-- Posisi fixed agar tetap terlihat saat halaman discroll -->
<nav class="fixed top-0 inset-x-0 z-30 h-14 bg-indigo-700 shadow-md flex items-center px-4 gap-3">

    <!-- Hamburger: hanya muncul di layar kecil (<lg) -->
    <button id="sidebar-toggle"
            class="lg:hidden text-indigo-100 hover:text-white rounded focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700"
            aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="sidebar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <span class="text-white font-bold text-lg tracking-tight flex-1 select-none">
        📦 Inventoria
    </span>

    <!-- Identitas pengguna aktif + role badge -->
    <div class="flex items-center gap-3">
        <span class="hidden sm:block text-indigo-100 text-sm truncate max-w-[140px]">
            <?= htmlspecialchars($full_name) ?>
        </span>

        <?php if ($current_role === 'admin'): ?>
            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-400 text-amber-900">Admin</span>
        <?php else: ?>
            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-400 text-emerald-900">Staff</span>
        <?php endif; ?>

        <a href="/logout.php"
           class="text-indigo-200 hover:text-white text-sm hover:underline underline-offset-2 rounded px-1 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">
            Logout
        </a>
    </div>
</nav>

<!-- wrapper: sidebar + konten -->
<div class="flex h-full pt-14">

<?php require_once __DIR__ . '/sidebar.php'; ?>

<!-- Area konten utama — flex-1 agar mengisi sisa lebar halaman -->
<main id="main-content" class="flex-1 overflow-y-auto p-6 lg:p-8 bg-slate-50 min-h-screen">

<?php
// Flash messages dibaca dan langsung dihapus dari session —
// kalau tidak dihapus, pesan akan muncul lagi saat halaman di-refresh
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flash_success): ?>
<div id="flash-success" role="alert"
     class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
    <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <span class="text-sm font-medium flex-1"><?= htmlspecialchars($flash_success) ?></span>
    <button onclick="document.getElementById('flash-success').remove()"
            class="text-emerald-500 hover:text-emerald-700 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500"
            aria-label="Tutup notifikasi">✕</button>
</div>
<?php endif; ?>

<?php if ($flash_error): ?>
<div id="flash-error" role="alert"
     class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
    <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 012 0v1a1 1 0 01-2 0v-1zm0-8a1 1 0 012 0v4a1 1 0 01-2 0V5z" clip-rule="evenodd"/>
    </svg>
    <span class="text-sm font-medium flex-1"><?= htmlspecialchars($flash_error) ?></span>
    <button onclick="document.getElementById('flash-error').remove()"
            class="text-red-500 hover:text-red-700 rounded focus:outline-none focus:ring-2 focus:ring-red-500"
            aria-label="Tutup notifikasi">✕</button>
</div>
<?php endif; ?>
