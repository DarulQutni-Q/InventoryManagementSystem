<?php
/**
 * public/index.php
 * Entry point utama aplikasi dengan pengalihan rute cerdas (smart redirect).
 *
 * Mengapa logika ini diperlukan:
 * - Pengunjung yang membuka root URL (/) langsung diarahkan sesuai status otentikasi.
 * - Pengguna yang sudah login diarahkan ke dashboard kerja (/dashboard.php).
 * - Pengunjung anonim diarahkan ke form otentikasi (/login.php).
 */

require_once __DIR__ . '/../helpers/auth.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

header('Location: /login.php');
exit;
