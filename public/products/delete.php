<?php
/**
 * public/products/delete.php
 * Handler penghapusan produk dengan proteksi integritas data histori stok.
 *
 * Menerapkan business rule (Non-Negotiable):
 * - Produk yang memiliki histori mutasi di stock_movements DILARANG KERAS dihapus.
 *   Ini menjamin konsistensi pembukuan audit inventaris gudang (ON DELETE RESTRICT).
 * - Penghapusan hanya diizinkan untuk request POST dengan validasi token CSRF.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireRole('admin');

// Batasi hanya request POST untuk mencegah penghapusan tak sengaja lewat crawler/GET URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /products/index.php');
    exit;
}

if (!verify_csrf()) {
    $_SESSION['flash_error'] = 'Gagal menghapus: token CSRF tidak valid atau sesi kadaluarsa.';
    header('Location: /products/index.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_error'] = 'ID produk tidak valid.';
    header('Location: /products/index.php');
    exit;
}

// 1. Ambil data produk untuk menampilkan namanya pada flash message
$stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan atau sudah dihapus.';
    header('Location: /products/index.php');
    exit;
}

// 2. Periksa apakah produk memiliki catatan histori di buku besar stock_movements
$histStmt = $pdo->prepare('SELECT COUNT(*) FROM stock_movements WHERE product_id = ?');
$histStmt->execute([$id]);
$hasHistory = (int)$histStmt->fetchColumn() > 0;

if ($hasHistory) {
    // Sesuai aturan bisnis: tolak penghapusan untuk menjaga jejak audit mutasi
    $_SESSION['flash_error'] = "Produk '{$product['name']}' tidak dapat dihapus karena memiliki riwayat pergerakan stok.";
    header('Location: /products/index.php');
    exit;
}

// 3. Jika tidak memiliki histori pergerakan stok, lakukan penghapusan secara transaksional
$pdo->beginTransaction();
try {
    $pdo->prepare('DELETE FROM product_suppliers WHERE product_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    $pdo->commit();

    $_SESSION['flash_success'] = "Produk '{$product['name']}' berhasil dihapus dari sistem.";
} catch (Throwable $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Gagal menghapus produk: ' . $e->getMessage();
}

header('Location: /products/index.php');
exit;
