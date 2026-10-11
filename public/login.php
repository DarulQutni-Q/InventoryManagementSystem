<?php
/**
 * public/login.php
 * Controller otentikasi: menangani tampilan formulir (GET) dan pemrosesan kredensial (POST).
 *
 * Menerapkan standar keamanan OWASP:
 * - Proteksi Session Fixation melalui session_regenerate_id(true) di helpers/auth.php.
 * - Proteksi CSRF menggunakan token tersinkronisasi via verify_csrf().
 * - Proteksi SQL Injection menggunakan 100% Prepared Statement PDO.
 * - Proteksi Timing Attack dan Password Hash melalui password_verify().
 * - Pesan error generik untuk mencegah kebocoran informasi user enumeration.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/escape.php';

// Jika pengguna sudah memiliki sesi login aktif, langsung alihkan ke dashboard
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
$old_username = '';

// Proses form saat metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // 1. Verifikasi token CSRF wajib lolos sebelum menyentuh basis data
    if (!verify_csrf()) {
        $error = 'Permintaan tidak valid: token CSRF kadaluarsa atau tidak cocok. Silakan coba lagi.';
    } elseif ($old_username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // 2. Ambil data akun dan profil menggunakan Prepared Statement murni
        $stmt = $pdo->prepare('
            SELECT u.id, u.username, u.password, u.role, up.full_name
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE u.username = ?
            LIMIT 1
        ');
        $stmt->execute([$old_username]);
        $user = $stmt->fetch();

        // 3. Verifikasi hash bcrypt — pesan error dibuat seragam agar tidak membocorkan keberadaan username
        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Kombinasi username atau password yang Anda masukkan salah.';
        } else {
            // 4. Regenerasi ID sesi dan daftarkan hak akses ke dalam sesi
            loginUser($user, [
                'full_name' => $user['full_name'] ?? $user['username'],
            ]);

            $_SESSION['flash_success'] = 'Selamat datang kembali, ' . ($user['full_name'] ?? $user['username']) . '!';
            header('Location: /dashboard.php');
            exit;
        }
    }
}

// Tampilkan view form login (meneruskan variabel $error dan $old_username)
require __DIR__ . '/../views/auth/login.php';
