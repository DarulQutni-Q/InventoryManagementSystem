<?php
/**
 * config/database.example.php
 * Contoh konfigurasi basis data untuk Mini Project 3.
 *
 * Salin file ini menjadi konfigurasi lokal atau atur environment variable
 * pada web server / file .env sesuai lingkungan deployment Anda.
 */

return [
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'inventory_db'),
    'username' => getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'root'),
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ''),
    'charset' => 'utf8mb4',

    // Pengaturan fallback SQLite saat server MySQL belum menyala di mesin dev
    'fallback_sqlite' => filter_var(getenv('DB_FALLBACK_SQLITE') ?: true, FILTER_VALIDATE_BOOLEAN),
    'sqlite_path' => getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../database/inventory.sqlite',

    // Opsi PDO standar keamanan & konsistensi data
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
