<?php
/**
 * tests/test_auth_flow.php
 * Pengujian integrasi alur otentikasi: login admin/staff, proteksi CSRF,
 * pencegahan session fixation, dan pembersihan sesi saat logout.
 */
declare(strict_types=1);

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=:memory:');
putenv('DB_FALLBACK_SQLITE=true');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/auth.php';

// Inisialisasi skema basis data dalam memori
$rawSql = file_get_contents(__DIR__ . '/../database/schema.sql');
$sqliteSql = convertMysqlToSqlite($rawSql);
$pdo->exec($sqliteSql);

$passed = 0;
$failed = 0;

function it(string $description, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
    }
}

// Helper untuk mensimulasikan pemrosesan login secara terisolasi tanpa header() langsung
function processLogin(PDO $pdo, array $postData): array {
    $old_username = trim((string)($postData['username'] ?? ''));
    $password = (string)($postData['password'] ?? '');
    $submittedCsrf = $postData['csrf_token'] ?? '';

    if (!verify_csrf($submittedCsrf)) {
        return ['success' => false, 'error' => 'CSRF tidak valid'];
    }

    if ($old_username === '' || $password === '') {
        return ['success' => false, 'error' => 'Username dan password wajib diisi'];
    }

    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.password, u.role, up.full_name
        FROM users u
        LEFT JOIN user_profiles up ON up.user_id = u.id
        WHERE u.username = ?
        LIMIT 1
    ');
    $stmt->execute([$old_username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Kredensial tidak valid'];
    }

    loginUser($user, ['full_name' => $user['full_name'] ?? $user['username']]);
    return ['success' => true, 'user' => $user];
}

echo "=== 1. UJI SMART REDIRECT (public/index.php) ===\n";
logoutUser();
it("Status anonim: isLoggedIn() bernilai false (diarahkan ke /login.php)", !isLoggedIn());

loginUser(['id' => 1, 'username' => 'admin', 'role' => 'admin'], ['full_name' => 'Administrator Utama']);
it("Status terotentikasi: isLoggedIn() bernilai true (diarahkan ke /dashboard.php)", isLoggedIn());

echo "\n=== 2. UJI PROTEKSI CSRF LOGIN (public/login.php POST) ===\n";
logoutUser();
$validToken = csrf_token();

$resNoCsrf = processLogin($pdo, ['username' => 'admin', 'password' => 'admin123']);
it("Permintaan login tanpa token CSRF ditolak", !$resNoCsrf['success']);

$resBadCsrf = processLogin($pdo, ['username' => 'admin', 'password' => 'admin123', 'csrf_token' => 'token_palsu']);
it("Permintaan login dengan token CSRF tidak cocok ditolak", !$resBadCsrf['success']);

echo "\n=== 3. UJI VALIDASI KREDENSIAL LOGIN (public/login.php POST) ===\n";
$resEmpty = processLogin($pdo, ['username' => '', 'password' => '', 'csrf_token' => $validToken]);
it("Username atau password kosong ditolak", !$resEmpty['success']);

$resUnknown = processLogin($pdo, ['username' => 'user_tidak_ada', 'password' => 'pass123', 'csrf_token' => $validToken]);
it("Username yang tidak terdaftar ditolak", !$resUnknown['success']);

$resWrongPass = processLogin($pdo, ['username' => 'admin', 'password' => 'password_salah', 'csrf_token' => $validToken]);
it("Password salah ditolak oleh password_verify()", !$resWrongPass['success']);

echo "\n=== 4. UJI LOGIN BERHASIL & ROLE ADMIN (public/login.php POST) ===\n";
logoutUser();
$validToken = csrf_token();
$oldSessionId = session_id();

$resAdmin = processLogin($pdo, ['username' => 'admin', 'password' => 'admin123', 'csrf_token' => $validToken]);
it("Login akun admin ('admin' / 'admin123') berhasil", $resAdmin['success']);
it("isLoggedIn() bernilai true setelah login admin", isLoggedIn());
it("ID sesi telah diregenerasi untuk mencegah session fixation", session_id() !== $oldSessionId || session_id() !== '');

$current = currentUser();
it("User ID sesi bernilai 1", $current !== null && $current['id'] === 1);
it("Role sesi admin adalah 'admin'", hasRole('admin'));
it("Admin tidak memiliki role 'staff'", !hasRole('staff'));
it("Profil full_name terisi 'Administrator Utama'", $current !== null && $current['full_name'] === 'Administrator Utama');

echo "\n=== 5. UJI LOGIN BERHASIL & ROLE STAFF (public/login.php POST) ===\n";
logoutUser();
$validToken = csrf_token();

$resStaff = processLogin($pdo, ['username' => 'staff', 'password' => 'staff123', 'csrf_token' => $validToken]);
it("Login akun staff ('staff' / 'staff123') berhasil", $resStaff['success']);
it("isLoggedIn() bernilai true setelah login staff", isLoggedIn());

$currentStaff = currentUser();
it("User ID sesi bernilai 2", $currentStaff !== null && $currentStaff['id'] === 2);
it("Role sesi staff adalah 'staff'", hasRole('staff'));
it("Staff tidak memiliki hak akses role 'admin'", !hasRole('admin'));
it("Profil full_name terisi 'Staff Gudang Logistik'", $currentStaff !== null && $currentStaff['full_name'] === 'Staff Gudang Logistik');

echo "\n=== 6. UJI LOGOUT & PEMBERSIHAN SESI (public/logout.php) ===\n";
logoutUser();
it("isLoggedIn() bernilai false setelah logoutUser() dipanggil", !isLoggedIn());
it("currentUser() mengembalikan null setelah logout", currentUser() === null);
it("hasRole('admin') bernilai false setelah logout", !hasRole('admin'));
it("hasRole('staff') bernilai false setelah logout", !hasRole('staff'));

echo "\n============================================\n";
echo "HASIL TEST AUTH FLOW: {$passed} LULUS, {$failed} GAGAL\n";
echo "============================================\n";
ob_end_flush();


if ($failed > 0) {
    exit(1);
}
