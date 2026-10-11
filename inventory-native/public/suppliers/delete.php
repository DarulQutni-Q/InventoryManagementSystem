<?php
// Hapus supplier — POST-only, tidak ada tampilan GET.
// Proteksi relasi: cek product_suppliers sebelum hapus.
// Supplier yang masih terhubung ke produk manapun ditolak dengan pesan jelas.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /suppliers/index.php');
    exit;
}

require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID supplier tidak valid.';
    header('Location: /suppliers/index.php');
    exit;
}

$row_stmt = $pdo->prepare('SELECT name FROM suppliers WHERE id = ?');
$row_stmt->execute([$id]);
$supplier = $row_stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    $_SESSION['flash_error'] = 'Supplier tidak ditemukan.';
    header('Location: /suppliers/index.php');
    exit;
}

// Cek apakah supplier ini masih terdaftar pada produk manapun
// via tabel junction product_suppliers — hapus paksa akan rusak relasi produk
$used_stmt = $pdo->prepare('SELECT COUNT(*) FROM product_suppliers WHERE supplier_id = ?');
$used_stmt->execute([$id]);
$product_count = (int)$used_stmt->fetchColumn();

if ($product_count > 0) {
    $_SESSION['flash_error'] = sprintf(
        'Supplier "%s" tidak dapat dihapus karena masih terhubung dengan %d produk. '
        . 'Lepaskan hubungan supplier dari produk terlebih dahulu.',
        $supplier['name'],
        $product_count
    );
    header('Location: /suppliers/index.php');
    exit;
}

$del_stmt = $pdo->prepare('DELETE FROM suppliers WHERE id = ?');
$del_stmt->execute([$id]);

$_SESSION['flash_success'] = "Supplier \"{$supplier['name']}\" berhasil dihapus.";
header('Location: /suppliers/index.php');
exit;
