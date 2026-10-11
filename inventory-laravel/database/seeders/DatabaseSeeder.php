<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna & Profil
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Administrator Utama',
            'email' => 'admin@inventoria.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Administrator Utama',
        ]);

        $staff = User::create([
            'username' => 'staff',
            'name' => 'Staff Gudang Logistik',
            'email' => 'staff@inventoria.local',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);
        UserProfile::create([
            'user_id' => $staff->id,
            'full_name' => 'Staff Gudang Logistik',
        ]);

        // 2. Kategori Barang
        $categoriesData = [
            'Elektronik & Komputer',
            'Aksesoris & Periferal',
            'Peralatan Kantor',
            'Jaringan & Server',
        ];
        $categories = [];
        foreach ($categoriesData as $catName) {
            $categories[] = Category::create(['name' => $catName]);
        }

        // 3. Rekanan Supplier
        $suppliersData = [
            ['name' => 'PT Teknologi Maju Bersama', 'contact' => '0812-3456-7890 / sales@tekmaju.co.id'],
            ['name' => 'CV Berkah Digital Sukses', 'contact' => '0813-9876-5432 / info@berkahdigital.com'],
            ['name' => 'PT Logistik Komputindo Jaya', 'contact' => '0821-4455-6677 / supply@logistikkomp.id'],
            ['name' => 'CV Mitra Sentosa Abadi', 'contact' => '0852-1122-3344 / order@mitrasentosa.com'],
            ['name' => 'PT Global Jaringan Nusantara', 'contact' => '0878-3322-1100 / contact@globaljaringan.id'],
        ];
        $suppliers = [];
        foreach ($suppliersData as $sup) {
            $suppliers[] = Supplier::create($sup);
        }

        // 4. Katalog Produk
        $productsData = [
            [
                'category_id' => $categories[0]->id,
                'sku' => 'PROD-ELEC-001',
                'name' => 'Monitor LED 24 Inch Full HD',
                'price' => 1750000.00,
                'stock' => 15,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $categories[0]->id,
                'sku' => 'PROD-ELEC-002',
                'name' => 'Mini PC Core i5 16GB RAM',
                'price' => 5400000.00,
                'stock' => 8,
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $categories[1]->id,
                'sku' => 'PROD-ACC-001',
                'name' => 'Mouse Wireless Ergonomis',
                'price' => 185000.00,
                'stock' => 25,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $categories[1]->id,
                'sku' => 'PROD-ACC-002',
                'name' => 'Mechanical Keyboard RGB TKL',
                'price' => 450000.00,
                'stock' => 4,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $categories[2]->id,
                'sku' => 'PROD-OFF-001',
                'name' => 'Kertas HVS A4 80gr 1 Rim',
                'price' => 55000.00,
                'stock' => 60,
                'minimum_stock' => 20,
            ],
            [
                'category_id' => $categories[2]->id,
                'sku' => 'PROD-OFF-002',
                'name' => 'Printer Laser Monochrome',
                'price' => 1950000.00,
                'stock' => 2,
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $categories[3]->id,
                'sku' => 'PROD-NET-001',
                'name' => 'Router Wi-Fi 6 Gigabit Dual Band',
                'price' => 820000.00,
                'stock' => 12,
                'minimum_stock' => 4,
            ],
            [
                'category_id' => $categories[3]->id,
                'sku' => 'PROD-NET-002',
                'name' => 'Switch Hub 16 Port Gigabit',
                'price' => 1150000.00,
                'stock' => 6,
                'minimum_stock' => 3,
            ],
        ];

        $products = [];
        foreach ($productsData as $prod) {
            $products[] = Product::create($prod);
        }

        // 5. Relasi Produk - Supplier (Pivot dengan supplier_price)
        $products[0]->suppliers()->attach([
            $suppliers[0]->id => ['supplier_price' => 1600000.00],
            $suppliers[1]->id => ['supplier_price' => 1620000.00],
        ]);
        $products[1]->suppliers()->attach([
            $suppliers[0]->id => ['supplier_price' => 5100000.00],
        ]);
        $products[2]->suppliers()->attach([
            $suppliers[1]->id => ['supplier_price' => 160000.00],
            $suppliers[2]->id => ['supplier_price' => 165000.00],
        ]);
        $products[3]->suppliers()->attach([
            $suppliers[2]->id => ['supplier_price' => 410000.00],
        ]);
        $products[4]->suppliers()->attach([
            $suppliers[3]->id => ['supplier_price' => 48000.00],
        ]);
        $products[5]->suppliers()->attach([
            $suppliers[0]->id => ['supplier_price' => 1800000.00],
            $suppliers[3]->id => ['supplier_price' => 1820000.00],
        ]);
        $products[6]->suppliers()->attach([
            $suppliers[4]->id => ['supplier_price' => 750000.00],
        ]);
        $products[7]->suppliers()->attach([
            $suppliers[4]->id => ['supplier_price' => 1050000.00],
        ]);

        // 6. Mutasi Awal Inventaris
        $movementsData = [
            ['product' => $products[0], 'user' => $admin, 'type' => 'IN', 'qty' => 15, 'note' => 'Penerimaan stok awal inventaris kantor'],
            ['product' => $products[1], 'user' => $admin, 'type' => 'IN', 'qty' => 8, 'note' => 'Penerimaan stok awal workstation'],
            ['product' => $products[2], 'user' => $staff, 'type' => 'IN', 'qty' => 25, 'note' => 'Restock awal periferal mouse kantor'],
            ['product' => $products[3], 'user' => $staff, 'type' => 'IN', 'qty' => 4, 'note' => 'Penerimaan keyboard mekanikal divisi teknis'],
            ['product' => $products[4], 'user' => $admin, 'type' => 'IN', 'qty' => 60, 'note' => 'Penerimaan pasokan kertas ATK semester 1'],
            ['product' => $products[5], 'user' => $admin, 'type' => 'IN', 'qty' => 2, 'note' => 'Penerimaan printer divisi administrasi'],
            ['product' => $products[6], 'user' => $staff, 'type' => 'IN', 'qty' => 12, 'note' => 'Penerimaan perangkat jaringan wireless'],
            ['product' => $products[7], 'user' => $staff, 'type' => 'IN', 'qty' => 6, 'note' => 'Penerimaan switch hub server room'],
        ];

        foreach ($movementsData as $m) {
            StockMovement::create([
                'product_id' => $m['product']->id,
                'user_id' => $m['user']->id,
                'type' => $m['type'],
                'quantity' => $m['qty'],
                'note' => $m['note'],
            ]);
        }
    }
}
