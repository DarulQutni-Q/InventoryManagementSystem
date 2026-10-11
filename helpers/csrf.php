<?php
/**
 * helpers/csrf.php
 * Proteksi Cross-Site Request Forgery (CSRF) berbasis sinkronisasi token sesi.
 *
 * Mengapa mekanisme ini penting:
 * - random_bytes(32) menghasilkan entropy kriptografis tinggi yang mustahil ditebak pihak ketiga.
 * - hash_equals() mencegah timing attack saat perbandingan string dilakukan di memori.
 * - Menjaga form POST tetap terlindungi dari eksploitasi cross-origin request jahat.
 */

require_once __DIR__ . '/escape.php';

// Memastikan sesi PHP aktif sebelum manipulasi token CSRF
function ensureSessionActive(): void {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}

// Menghasilkan atau mengambil token CSRF yang tersimpan pada sesi saat ini
function csrf_token(): string {
    ensureSessionActive();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Menghasilkan elemen input tersembunyi HTML siap pakai di dalam form POST
function csrf_field(): string {
    $token = e(csrf_token());
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Memverifikasi kecocokan token CSRF kiriman klien dengan token di sesi server
function verify_csrf(?string $token = null): bool {
    ensureSessionActive();

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if ($sessionToken === '') {
        return false;
    }

    $submittedToken = $token ?? ($_POST['csrf_token'] ?? '');
    if (!is_string($submittedToken) || $submittedToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $submittedToken);
}

// Guard CSRF untuk endpoint POST — langsung menghentikan request jika token tidak valid
function require_csrf(): void {
    if (!verify_csrf()) {
        http_response_code(403);
        echo 'Permintaan tidak valid: token CSRF kadaluarsa atau tidak cocok.';
        exit;
    }
}
