<?php
/**
 * public/logout.php
 * Handler penghentian sesi login (Logout) secara aman.
 *
 * Mengapa penghancuran sesi menyeluruh wajib:
 * - Menghapus array $_SESSION saja tidak menghapus file sesi di disk server.
 * - Cookie sesi klien wajib dikadaluarsakan agar browser tidak memakai ID lama.
 * - Sesi baru dibuat sejenak untuk menampung flash_success ramah pengguna.
 */

require_once __DIR__ . '/../helpers/auth.php';

// Bersihkan data sesi dan hancurkan sesi yang sedang aktif
logoutUser();

// Buka sesi baru yang bersih khusus untuk membawa notifikasi flash logout
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$_SESSION['flash_success'] = 'Anda telah berhasil keluar dari sistem.';

header('Location: /login.php');
exit;
