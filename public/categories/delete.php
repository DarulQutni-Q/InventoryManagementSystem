<?php
// Hapus kategori — POST-only, tidak ada tampilan GET.
// Proteksi relasi: cek dulu apakah ada produk yang menggunakan kategori ini.
// Kalau ada, tolak dengan pesan ramah — jangan biarkan FK violation crash tanpa penjelasan.
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/csrf.php';

requireLogin();
requireRole('admin');

// GET ke endpoint ini tidak masuk akal — redirect saja
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /categories/index.php');
    exit;
}

require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID kategori tidak valid.';
    header('Location: /categories/index.php');
    exit;
}

// Ambil nama dulu untuk pesan konfirmasi yang informatif
$row_stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
$row_stmt->execute([$id]);
$category = $row_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['flash_error'] = 'Kategori tidak ditemukan.';
    header('Location: /categories/index.php');
    exit;
}

// Cek referential integrity: tolak hapus jika kategori masih dipakai produk.
// Mengandalkan DB error (FK violation) lebih sulit diberi pesan ramah ke user.
$used_stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
$used_stmt->execute([$id]);
$product_count = (int)$used_stmt->fetchColumn();

if ($product_count > 0) {
    $_SESSION['flash_error'] = sprintf(
        'Kategori "%s" tidak dapat dihapus karena masih digunakan oleh %d produk. '
        . 'Pindahkan atau hapus produk tersebut terlebih dahulu.',
        $category['name'],
        $product_count
    );
    header('Location: /categories/index.php');
    exit;
}

$del_stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
$del_stmt->execute([$id]);

$_SESSION['flash_success'] = "Kategori \"{$category['name']}\" berhasil dihapus.";
header('Location: /categories/index.php');
exit;
