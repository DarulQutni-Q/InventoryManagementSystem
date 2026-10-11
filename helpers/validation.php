<?php
/**
 * helpers/validation.php
 * Validasi input formulir dan penegakan aturan bisnis (Business Rules).
 *
 * Mengapa validasi wajib ganda (frontend + backend):
 * - Validasi HTML5 di browser mudah di-bypass dengan curl, Postman, atau inspect element.
 * - Format SKU alphanumeric dan stok non-negatif adalah aturan mutlak di level sistem
 *   untuk mencegah inkonsistensi fisik pada inventaris gudang.
 */

// Memvalidasi format SKU agar hanya memuat huruf, angka, tanda strip, atau garis bawah
function validateSkuFormat(string $sku): bool {
    return (bool)preg_match('/^[A-Za-z0-9\-_]{1,100}$/', $sku);
}

// Memvalidasi bilangan bulat positif (> 0)
function validatePositiveInteger(mixed $val): bool {
    if (is_int($val)) {
        return $val > 0;
    }
    return is_string($val) && ctype_digit($val) && (int)$val > 0;
}

// Memvalidasi bilangan bulat non-negatif (>= 0)
function validateNonNegativeInteger(mixed $val): bool {
    if (is_int($val)) {
        return $val >= 0;
    }
    return is_string($val) && ctype_digit($val) && (int)$val >= 0;
}

// Memvalidasi angka desimal/pecahan non-negatif (>= 0)
function validateNonNegativeNumber(mixed $val): bool {
    return is_numeric($val) && (float)$val >= 0;
}

// Validasi master data produk termasuk keunikan SKU dan relasi kategori
function validateProduct(array $data, ?int $productId = null, ?PDO $pdo = null): array {
    $errors = [];

    $name = trim((string)($data['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 150) {
        $errors['name'] = 'Nama produk wajib diisi dan maksimal 150 karakter.';
    }

    if (!validatePositiveInteger($data['category_id'] ?? '')) {
        $errors['category_id'] = 'Kategori produk harus dipilih dengan benar.';
    } elseif ($pdo !== null) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $stmt->execute([(int)$data['category_id']]);
        if (!$stmt->fetch()) {
            $errors['category_id'] = 'Kategori yang dipilih tidak terdaftar di sistem.';
        }
    }

    $sku = trim((string)($data['sku'] ?? ''));
    if (!validateSkuFormat($sku)) {
        $errors['sku'] = 'SKU harus berupa alphanumeric (huruf/angka/-/_), maksimal 100 karakter.';
    } elseif ($pdo !== null) {
        $sql = 'SELECT id FROM products WHERE sku = ?' . ($productId ? ' AND id != ?' : '');
        $params = $productId ? [$sku, $productId] : [$sku];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetch()) {
            $errors['sku'] = 'SKU ini sudah terdaftar untuk produk lain.';
        }
    }

    if (!validateNonNegativeNumber($data['price'] ?? '')) {
        $errors['price'] = 'Harga harus berupa angka dan tidak boleh bernilai negatif.';
    }

    if (isset($data['stock']) && !validateNonNegativeInteger($data['stock'])) {
        $errors['stock'] = 'Stok barang harus berupa bilangan bulat non-negatif.';
    }

    if (!validateNonNegativeInteger($data['minimum_stock'] ?? '')) {
        $errors['minimum_stock'] = 'Batas minimum stok harus berupa bilangan bulat non-negatif.';
    }

    return $errors;
}

// Validasi master data kategori barang
function validateCategory(array $data): array {
    $errors = [];
    $name = trim((string)($data['name'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) {
        $errors['name'] = 'Nama kategori wajib diisi dan maksimal 100 karakter.';
    }

    return $errors;
}

// Validasi data rekanan pemasok (supplier)
function validateSupplier(array $data): array {
    $errors = [];
    $name = trim((string)($data['name'] ?? ''));
    $contact = trim((string)($data['contact'] ?? ''));

    if ($name === '' || mb_strlen($name) > 150) {
        $errors['name'] = 'Nama supplier wajib diisi dan maksimal 150 karakter.';
    }

    if ($contact !== '' && mb_strlen($contact) > 100) {
        $errors['contact'] = 'Kontak supplier maksimal 100 karakter.';
    }

    return $errors;
}

// Validasi transaksi mutasi stok barang (IN / OUT)
function validateStockMovement(array $data, ?PDO $pdo = null): array {
    $errors = [];

    if (!validatePositiveInteger($data['product_id'] ?? '')) {
        $errors['product_id'] = 'Produk harus dipilih dari daftar.';
    }

    $type = strtoupper(trim((string)($data['type'] ?? '')));
    if (!in_array($type, ['IN', 'OUT'], true)) {
        $errors['type'] = 'Tipe transaksi harus bernilai IN (masuk) atau OUT (keluar).';
    }

    if (!validatePositiveInteger($data['quantity'] ?? '')) {
        $errors['quantity'] = 'Jumlah kuantitas harus berupa bilangan bulat lebih dari 0.';
    }

    // Pengecekan kecukupan stok saat pengeluaran barang (OUT)
    if ($type === 'OUT' && empty($errors['quantity']) && empty($errors['product_id']) && $pdo !== null) {
        $stmt = $pdo->prepare('SELECT stock, name FROM products WHERE id = ?');
        $stmt->execute([(int)$data['product_id']]);
        $product = $stmt->fetch();

        if (!$product) {
            $errors['product_id'] = 'Produk tidak ditemukan.';
        } elseif ((int)$product['stock'] < (int)$data['quantity']) {
            $errors['quantity'] = "Stok {$product['name']} tidak mencukupi (tersedia: {$product['stock']}).";
        }
    }

    return $errors;
}
