-- schema.sql — DDL skema relasional & seed data (MySQL InnoDB)

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS product_suppliers;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel users: kredensial otentikasi & kontrol hak akses RBAC
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel user_profiles: relasi 1-to-1 dengan users untuk detail profil personal
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel categories: klasifikasi barang inventaris
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel products: katalog barang utama
-- RESTRICT mencegah kategori dihapus jika masih ada produk aktif di dalamnya
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel suppliers: rekanan pemasok barang
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel product_suppliers: relasi Many-to-Many antara produk dan supplier
-- CASCADE di sini aman karena ini tabel pivot murni tanpa histori transaksi keuangan
CREATE TABLE product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    supplier_price DECIMAL(12, 2) NULL,
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel stock_movements: buku besar audit mutasi stok barang (masuk/keluar)
-- RESTRICT wajib agar histori audit tidak hilang saat data produk atau user dicoba hapus
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data awal

-- Password hash bcrypt: admin123 & staff123 (keamanan OWASP tidak simpan plaintext)
INSERT INTO users (id, username, password, role) VALUES
(1, 'admin', '$2y$12$huVRdYGG7/v3iGeaztRDbe1MTpBehNW.z5OGAKEThbT1Jvi/gbu.O', 'admin'),
(2, 'staff', '$2y$12$Q2x1GdubtlW771rq6zy5w.mdDMzzJbCqSiAR/Nth/BVYH02qFKABK', 'staff');

INSERT INTO user_profiles (user_id, full_name) VALUES
(1, 'Administrator Utama'),
(2, 'Staff Gudang Logistik');

INSERT INTO categories (id, name) VALUES
(1, 'Elektronik & Komputer'),
(2, 'Aksesoris & Periferal'),
(3, 'Peralatan Kantor'),
(4, 'Jaringan & Server');

INSERT INTO suppliers (id, name, contact) VALUES
(1, 'PT Teknologi Maju Bersama', '0812-3456-7890 / sales@tekmaju.co.id'),
(2, 'CV Berkah Digital Sukses', '0813-9876-5432 / info@berkahdigital.com'),
(3, 'PT Logistik Komputindo Jaya', '0821-4455-6677 / supply@logistikkomp.id'),
(4, 'CV Mitra Sentosa Abadi', '0852-1122-3344 / order@mitrasentosa.com'),
(5, 'PT Global Jaringan Nusantara', '0878-3322-1100 / contact@globaljaringan.id');

-- Produk bervariasi harga, stok, dan minimum_stock (beberapa mendekati/di bawah batas minimum)
INSERT INTO products (id, category_id, sku, name, price, stock, minimum_stock) VALUES
(1, 1, 'PROD-ELEC-001', 'Monitor LED 24 Inch Full HD', 1750000.00, 15, 5),
(2, 1, 'PROD-ELEC-002', 'Mini PC Core i5 16GB RAM', 5400000.00, 8, 3),
(3, 2, 'PROD-ACC-001', 'Mouse Wireless Ergonomis', 185000.00, 25, 10),
(4, 2, 'PROD-ACC-002', 'Mechanical Keyboard RGB TKL', 450000.00, 4, 5),
(5, 3, 'PROD-OFF-001', 'Kertas HVS A4 80gr 1 Rim', 55000.00, 60, 20),
(6, 3, 'PROD-OFF-002', 'Printer Laser Monochrome', 1950000.00, 2, 3),
(7, 4, 'PROD-NET-001', 'Router Wi-Fi 6 Gigabit Dual Band', 820000.00, 12, 4),
(8, 4, 'PROD-NET-002', 'Switch Hub 16 Port Gigabit', 1150000.00, 6, 3);

-- Relasi multi-supplier dengan harga pasokan masing-masing
INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES
(1, 1, 1600000.00),
(1, 2, 1620000.00),
(2, 1, 5100000.00),
(3, 2, 160000.00),
(3, 3, 165000.00),
(4, 3, 410000.00),
(5, 4, 48000.00),
(6, 1, 1800000.00),
(6, 4, 1820000.00),
(7, 5, 750000.00),
(8, 5, 1050000.00);

-- Riwayat awal mutasi barang masuk (IN) mencerminkan saldo stok fisik awal
INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES
(1, 1, 'IN', 15, 'Penerimaan stok awal inventaris kantor'),
(2, 1, 'IN', 8, 'Penerimaan stok awal workstation'),
(3, 2, 'IN', 25, 'Restock awal periferal mouse kantor'),
(4, 2, 'IN', 4, 'Penerimaan keyboard mekanikal divisi teknis'),
(5, 1, 'IN', 60, 'Penerimaan pasokan kertas ATK semester 1'),
(6, 1, 'IN', 2, 'Penerimaan printer divisi administrasi'),
(7, 2, 'IN', 12, 'Penerimaan perangkat jaringan wireless'),
(8, 2, 'IN', 6, 'Penerimaan switch hub server room');
