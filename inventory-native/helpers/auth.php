<?php
/**
 * helpers/auth.php
 * Helper autentikasi dan otorisasi Role-Based Access Control (RBAC).
 *
 * Mengapa proteksi backend wajib:
 * - Menyembunyikan menu di antarmuka (frontend) bukan keamanan sejati. Pengguna
 *   bisa langsung menembak URL (misal /products/create.php).
 * - Pemeriksaan role wajib dieksekusi di baris paling awal sebelum logika controller berjalan.
 * - session_regenerate_id(true) harus dipanggil saat login berhasil untuk mencegah Session Fixation.
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Mengecek apakah pengguna saat ini sudah memiliki sesi login yang sah
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

// Mengambil payload ringkas pengguna yang sedang login dari sesi aktif
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => (int)$_SESSION['user_id'],
        'username' => (string)($_SESSION['username'] ?? ''),
        'role' => (string)($_SESSION['role'] ?? 'staff'),
        'full_name' => (string)($_SESSION['full_name'] ?? ($_SESSION['username'] ?? '')),
    ];
}

// Mengecek apakah pengguna memiliki hak akses role tertentu (misal: 'admin' atau 'staff')
function hasRole(string $role): bool {
    return isLoggedIn() && (($_SESSION['role'] ?? '') === $role);
}

// Guard wajib login: melempar pengunjung anonim ke halaman login
function requireLogin(string $redirectUrl = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Silakan login terlebih dahulu untuk mengakses halaman ini.';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Guard otorisasi role: memastikan hanya role berwenang yang dapat melanjutkan eksekusi
function requireRole(string $role): void {
    requireLogin();

    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);

        $custom403 = __DIR__ . '/../views/403.php';
        if (file_exists($custom403)) {
            include $custom403;
        } else {
            echo '<h1>403 Terlarang</h1>';
            echo '<p>Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
            echo '<p><a href="/dashboard.php">Kembali ke Dashboard</a></p>';
        }
        exit;
    }
}

// Helper untuk mendaftarkan sesi login dengan proteksi session fixation
function loginUser(array $user, ?array $profile = null): void {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    // Regenerasi session ID penting untuk mencegah eksploitasi session fixation
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = (string)$user['username'];
    $_SESSION['role'] = (string)$user['role'];
    $_SESSION['full_name'] = (string)($profile['full_name'] ?? $user['username']);
}

// Helper untuk membersihkan seluruh sesi saat pengguna logout
function logoutUser(): void {

    $_SESSION = [];

    if (ini_get('session.use_cookies') && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
