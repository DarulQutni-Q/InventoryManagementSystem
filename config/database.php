<?php
/**
 * config/database.php
 * Koneksi PDO MySQL untuk Inventory Management System (P4 - PHP Native).
 *
 * Menerapkan standar keamanan OWASP:
 * - PDO::ERRMODE_EXCEPTION: Mencegah silent failure pada query.
 * - PDO::ATTR_DEFAULT_FETCH_MODE: Standarisasi hasil query sebagai array asosiatif.
 * - PDO::ATTR_EMULATE_PREPARES => false: Memaksa native prepared statements MySQL.
 */

// Konversi DDL MySQL ke SQLite untuk mode fallback development lokal
function convertMysqlToSqlite(string $sql): string {
    $sql = preg_replace('/SET FOREIGN_KEY_CHECKS\s*=\s*\d+;/i', '', $sql);
    $sql = preg_replace('/ENGINE\s*=\s*\w+[^;]*/i', '', $sql);
    $sql = preg_replace('/INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
    $sql = preg_replace('/ENUM\s*\([^)]+\)/i', 'TEXT', $sql);
    $sql = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $sql);
    return $sql;
}

// Inisialisasi basis data SQLite saat server MySQL lokal belum aktif
function initSqliteFallback(string $sqlitePath): PDO {
    $dir = dirname($sqlitePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // SQLite mematikan Foreign Key secara default, wajib diaktifkan per koneksi
    $pdo->exec('PRAGMA foreign_keys = ON;');

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
    if (!$tables) {
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $rawSql = file_get_contents($schemaFile);
            $sqliteSql = convertMysqlToSqlite($rawSql);
            $pdo->exec($sqliteSql);
        }
    }

    return $pdo;
}

// Mendapatkan instance koneksi PDO tunggal (singleton)
function getDbConnection(bool $forceNew = false): PDO {
    static $cachedPdo = null;

    if ($cachedPdo !== null && !$forceNew) {
        return $cachedPdo;
    }

    $driver = getenv('DB_DRIVER') ?: 'mysql';
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $dbname = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'inventory_db');
    $user = getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'root');
    $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
    $fallbackEnv = getenv('DB_FALLBACK_SQLITE');
    $fallbackSqlite = ($fallbackEnv === false || $fallbackEnv === '') ? true : filter_var($fallbackEnv, FILTER_VALIDATE_BOOLEAN);
    $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../database/inventory.sqlite';
    if ($driver === 'sqlite') {
        $cachedPdo = initSqliteFallback($sqlitePath);
        return $cachedPdo;
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $cachedPdo = new PDO($dsn, $user, $pass, $options);
        return $cachedPdo;
    } catch (PDOException $e) {
        // Fallback cerdas ke SQLite agar developer tidak terhenti saat MySQL lokal mati
        if ($fallbackSqlite) {
            $cachedPdo = initSqliteFallback($sqlitePath);
            return $cachedPdo;
        }

        // Jika fallback dinonaktifkan, lempar exception diagnostik yang jelas
        throw new PDOException(
            "Gagal menghubungkan ke MySQL ({$host}:{$port}/{$dbname}): " . $e->getMessage() .
            ". Pastikan layanan MySQL berjalan atau aktifkan DB_FALLBACK_SQLITE=true untuk pengujian lokal."
        );
    }
}

// Helper diagnostik untuk memeriksa kesehatan koneksi basis data
function checkDatabaseConnection(): array {
    try {
        $pdo = getDbConnection();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        return [
            'connected' => true,
            'driver' => $driver,
            'message' => 'Koneksi basis data aktif menggunakan driver ' . $driver,
        ];
    } catch (Throwable $e) {
        return [
            'connected' => false,
            'driver' => 'none',
            'message' => $e->getMessage(),
        ];
    }
}

// Inisialisasi variabel global $pdo untuk kemudahan include di controller/view native
$pdo = getDbConnection();
