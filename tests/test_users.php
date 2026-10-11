<?php
/**
 * tests/test_users.php
 * Pengujian modul Manajemen Pengguna: pembuatan user baru, hashing password,
 * relasi user_profiles, dan pembatasan hak akses staff (FR-AUTH-06 & FR-AUTH-07).
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
require_once __DIR__ . '/../helpers/validation.php';

// Inisialisasi basis data dan seed data
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

echo "=== 1. UJI SEED PENGGUNA AWAL & RELASI PROFIL ===\n";
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
it("Seed awal memuat 2 akun pengguna (admin & staff)", $userCount === 2);

$stmt = $pdo->query('
    SELECT u.username, u.role, up.full_name
    FROM users u
    JOIN user_profiles up ON up.user_id = u.id
    ORDER BY u.id ASC
');
$seedRows = $stmt->fetchAll();

it("Akun 1 adalah admin dengan profil 'Administrator Utama'", 
    $seedRows[0]['username'] === 'admin' && $seedRows[0]['role'] === 'admin' && $seedRows[0]['full_name'] === 'Administrator Utama'
);
it("Akun 2 adalah staff dengan profil 'Staff Gudang Logistik'", 
    $seedRows[1]['username'] === 'staff' && $seedRows[1]['role'] === 'staff' && $seedRows[1]['full_name'] === 'Staff Gudang Logistik'
);

echo "\n=== 2. UJI VALIDASI PEMBUATAN PENGGUNA BARU ===\n";
// Uji validasi format username
it("Format username valid (huruf/angka/underscore)", (bool)preg_match('/^[A-Za-z0-9_]{3,50}$/', 'staff_gudang_02'));
it("Format username tidak valid (mengandung spasi atau simbol)", !preg_match('/^[A-Za-z0-9_]{3,50}$/', 'user name!'));

// Uji pencegahan duplikasi username
$dupCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
$dupCheck->execute(['admin']);
it("Pemeriksaan mendeteksi username 'admin' sudah digunakan", (int)$dupCheck->fetchColumn() > 0);

echo "\n=== 3. UJI TRANSAKSI PEMBUATAN USER & PROFIL ===\n";
$newUsername = 'staff_budi';
$rawPass = 'rahasia123';
$newFullName = 'Budi Hartono Gudang';
$newRole = 'staff';

$pdo->beginTransaction();
$hash = password_hash($rawPass, PASSWORD_DEFAULT);
$insUser = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
$insUser->execute([$newUsername, $hash, $newRole]);
$createdId = (int)$pdo->lastInsertId();

$insProf = $pdo->prepare('INSERT INTO user_profiles (user_id, full_name) VALUES (?, ?)');
$insProf->execute([$createdId, $newFullName]);
$pdo->commit();

it("User baru berhasil disimpan dengan ID valid", $createdId > 2);

// Verifikasi integritas data akun & profil
$verifyStmt = $pdo->prepare('
    SELECT u.id, u.username, u.password, u.role, up.full_name
    FROM users u
    JOIN user_profiles up ON up.user_id = u.id
    WHERE u.id = ?
');
$verifyStmt->execute([$createdId]);
$saved = $verifyStmt->fetch();

it("Username dan role tersimpan sesuai input", $saved['username'] === $newUsername && $saved['role'] === $newRole);
it("Profil full_name tersimpan di user_profiles via foreign key", $saved['full_name'] === $newFullName);
it("Password di-hash dengan aman menggunakan bcrypt", password_verify($rawPass, $saved['password']));

echo "\n=== 4. UJI PENCARIAN PENGGUNA ===\n";
$searchStmt = $pdo->prepare('
    SELECT COUNT(*) 
    FROM users u 
    LEFT JOIN user_profiles up ON up.user_id = u.id 
    WHERE (u.username LIKE ? OR up.full_name LIKE ?)
');
$searchStmt->execute(['%Budi%', '%Budi%']);
it("Pencarian berdasarkan nama 'Budi' menemukan user yang baru dibuat", (int)$searchStmt->fetchColumn() === 1);

echo "\n=== 5. UJI ROLE-BASED ACCESS CONTROL (FR-AUTH-06 & FR-AUTH-07) ===\n";
// Skenario 1: Login sebagai Staff
loginUser(['id' => 2, 'username' => 'staff', 'role' => 'staff'], ['full_name' => 'Staff Gudang Logistik']);
it("Staff teridentifikasi login (isLoggedIn() === true)", isLoggedIn());
it("Staff TIDAK memiliki hak akses role 'admin' (hasRole('admin') === false)", !hasRole('admin'));
it("Staff memiliki hak akses role 'staff' (hasRole('staff') === true)", hasRole('staff'));

// Skenario 2: Login sebagai Admin
loginUser(['id' => 1, 'username' => 'admin', 'role' => 'admin'], ['full_name' => 'Administrator Utama']);
it("Admin memiliki hak akses penuh role 'admin' (hasRole('admin') === true)", hasRole('admin'));

echo "\n============================================\n";
echo "HASIL PENGUJIAN USER ADMIN: {$passed} LULUS, {$failed} GAGAL\n";
echo "============================================\n";

ob_end_flush();

if ($failed > 0) {
    exit(1);
}
