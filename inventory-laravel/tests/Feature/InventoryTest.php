<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin',
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        UserProfile::create(['user_id' => $this->admin->id, 'full_name' => 'Admin Test']);

        $this->staff = User::create([
            'username' => 'staff',
            'name' => 'Staff Test',
            'email' => 'staff@test.local',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);
        UserProfile::create(['user_id' => $this->staff->id, 'full_name' => 'Staff Test']);

        $this->category = Category::create(['name' => 'Hardware']);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Inventoria System');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_staff_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'staff',
            'password' => 'staff123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->staff);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->staff)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Ringkasan Inventaris');
    }

    public function test_dashboard_displays_correct_metrics_and_low_stock(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'TEST-001',
            'name' => 'Barang Kritis',
            'price' => 100000,
            'stock' => 2,
            'minimum_stock' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Barang Kritis');
        $response->assertSee('Perlu Reorder');
    }

    public function test_admin_can_create_product_with_valid_sku(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'PROD-NEW-01',
            'name' => 'Produk Baru Unggulan',
            'price' => 250000,
            'stock' => 20,
            'minimum_stock' => 5,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'sku' => 'PROD-NEW-01',
            'name' => 'Produk Baru Unggulan',
            'stock' => 20,
        ]);
    }

    public function test_product_creation_fails_with_duplicate_sku(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-UNIQUE',
            'name' => 'Produk Pertama',
            'price' => 100000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'PROD-UNIQUE',
            'name' => 'Produk Duplikat',
            'price' => 120000,
            'stock' => 5,
            'minimum_stock' => 2,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_staff_cannot_create_or_delete_product_returns_403(): void
    {
        $createResponse = $this->actingAs($this->staff)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'PROD-STAFF',
            'name' => 'Percobaan Staff',
            'price' => 50000,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        $createResponse->assertStatus(403);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-TEST',
            'name' => 'Produk Uji',
            'price' => 50000,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        $deleteResponse = $this->actingAs($this->staff)->delete("/products/{$product->id}");
        $deleteResponse->assertStatus(403);
    }

    public function test_stock_in_increases_product_stock_and_creates_movement(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-STOCK-IN',
            'name' => 'Barang Stok In',
            'price' => 50000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($this->staff)->post('/stock', [
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => 15,
            'note' => 'Restock pengiriman vendor',
        ]);

        $response->assertRedirect(route('stock.index'));
        $this->assertEquals(25, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => 15,
        ]);
    }

    public function test_stock_out_decreases_product_stock_when_sufficient(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-STOCK-OUT',
            'name' => 'Barang Stok Out',
            'price' => 75000,
            'stock' => 20,
            'minimum_stock' => 5,
        ]);

        $response = $this->actingAs($this->staff)->post('/stock', [
            'product_id' => $product->id,
            'type' => 'OUT',
            'quantity' => 8,
            'note' => 'Pengeluaran untuk kantor cabang',
        ]);

        $response->assertRedirect(route('stock.index'));
        $this->assertEquals(12, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'OUT',
            'quantity' => 8,
        ]);
    }

    public function test_stock_out_fails_and_rolls_back_when_stock_is_insufficient(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-INSUFFICIENT',
            'name' => 'Barang Defisit',
            'price' => 75000,
            'stock' => 5,
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($this->staff)->post('/stock', [
            'product_id' => $product->id,
            'type' => 'OUT',
            'quantity' => 10, // Melebihi stok 5!
            'note' => 'Permintaan melebihi kapasitas',
        ]);

        $response->assertSessionHasErrors('quantity');
        // Pastikan stok tidak berubah (tetap 5) dan tidak ada movement tersimpan
        $this->assertEquals(5, $product->fresh()->stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    public function test_product_with_movements_cannot_be_deleted(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-AUDIT-LOCK',
            'name' => 'Barang Terkunci Audit',
            'price' => 50000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
            'type' => 'IN',
            'quantity' => 10,
            'note' => 'Stok awal',
        ]);

        $response = $this->actingAs($this->admin)->delete("/products/{$product->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-CAT-LOCK',
            'name' => 'Barang Kategori Terkunci',
            'price' => 50000,
            'stock' => 1,
            'minimum_stock' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete("/categories/{$this->category->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_staff_cannot_access_user_management_returns_403(): void
    {
        $response = $this->actingAs($this->staff)->get('/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_user_and_profile(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'username' => 'staff_baru',
            'full_name' => 'Staff Logistik Baru',
            'email' => 'baru@inventoria.local',
            'password' => 'secret123',
            'role' => 'staff',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'staff_baru',
            'role' => 'staff',
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'full_name' => 'Staff Logistik Baru',
        ]);
    }

    public function test_staff_cannot_create_or_modify_category_returns_403(): void
    {
        // Akses form create ditolak
        $this->actingAs($this->staff)->get(route('categories.create'))->assertStatus(403);

        // Store ditolak
        $this->actingAs($this->staff)->post(route('categories.store'), [
            'name' => 'Kategori Staff Ilegal',
        ])->assertStatus(403);

        // Akses form edit ditolak
        $this->actingAs($this->staff)->get(route('categories.edit', $this->category))->assertStatus(403);

        // Update ditolak
        $this->actingAs($this->staff)->put(route('categories.update', $this->category), [
            'name' => 'Kategori Update Ilegal',
        ])->assertStatus(403);

        // Destroy ditolak
        $this->actingAs($this->staff)->delete(route('categories.destroy', $this->category))->assertStatus(403);
    }

    public function test_staff_cannot_create_or_modify_supplier_returns_403(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Mitra Logistik',
            'contact' => '08123456789',
        ]);

        // Akses form create ditolak
        $this->actingAs($this->staff)->get(route('suppliers.create'))->assertStatus(403);

        // Store ditolak
        $this->actingAs($this->staff)->post(route('suppliers.store'), [
            'name' => 'Supplier Staff Ilegal',
            'contact' => '0811111111',
        ])->assertStatus(403);

        // Akses form edit ditolak
        $this->actingAs($this->staff)->get(route('suppliers.edit', $supplier))->assertStatus(403);

        // Update ditolak
        $this->actingAs($this->staff)->put(route('suppliers.update', $supplier), [
            'name' => 'PT Mitra Update Ilegal',
            'contact' => '0822222222',
        ])->assertStatus(403);

        // Destroy ditolak
        $this->actingAs($this->staff)->delete(route('suppliers.destroy', $supplier))->assertStatus(403);
    }

    public function test_staff_cannot_create_or_modify_product_returns_403(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-STAFF-FORBIDDEN',
            'name' => 'Produk Tes Staff',
            'price' => 50000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        // Akses form create ditolak
        $this->actingAs($this->staff)->get(route('products.create'))->assertStatus(403);

        // Store ditolak
        $this->actingAs($this->staff)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'PROD-STAFF-NEW',
            'name' => 'Produk Baru Ilegal',
            'price' => 10000,
            'stock' => 5,
            'minimum_stock' => 1,
        ])->assertStatus(403);

        // Akses form edit ditolak
        $this->actingAs($this->staff)->get(route('products.edit', $product))->assertStatus(403);

        // Update ditolak
        $this->actingAs($this->staff)->put(route('products.update', $product), [
            'category_id' => $this->category->id,
            'sku' => 'PROD-STAFF-FORBIDDEN',
            'name' => 'Produk Diubah Ilegal',
            'price' => 75000,
            'minimum_stock' => 3,
        ])->assertStatus(403);

        // Destroy ditolak
        $this->actingAs($this->staff)->delete(route('products.destroy', $product))->assertStatus(403);
    }

    public function test_staff_can_view_product_catalog_read_only(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Supplier Utama',
            'contact' => '081299998888',
        ]);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-READ-ONLY',
            'name' => 'Monitor 24 Inch',
            'price' => 1800000,
            'stock' => 15,
            'minimum_stock' => 3,
        ]);

        // Staff bisa melihat daftar produk
        $indexResponse = $this->actingAs($this->staff)->get(route('products.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('PROD-READ-ONLY');
        $indexResponse->assertSee('Monitor 24 Inch');

        // Staff bisa melihat detail produk
        $showResponse = $this->actingAs($this->staff)->get(route('products.show', $product));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('PROD-READ-ONLY');

        // Staff juga dapat melihat daftar kategori dan supplier secara read-only
        $catResponse = $this->actingAs($this->staff)->get(route('categories.index'));
        $catResponse->assertStatus(200);
        $catResponse->assertSee($this->category->name);

        $suppResponse = $this->actingAs($this->staff)->get(route('suppliers.index'));
        $suppResponse->assertStatus(200);
        $suppResponse->assertSee($supplier->name);
    }

    public function test_staff_sees_operational_dashboard_without_money_valuation(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-VAL-TEST',
            'name' => 'Server Rack Unit',
            'price' => 25000000,
            'stock' => 4,
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($this->staff)->get(route('dashboard'));

        $response->assertStatus(200);

        // Memastikan metrik operasional gudang tersedia
        $response->assertViewHas('totalProducts');
        $response->assertViewHas('lowStockCount');
        $response->assertViewHas('totalStockIn');
        $response->assertViewHas('totalStockOut');
        $response->assertViewHas('criticalProducts');
        $response->assertViewHas('recentMovements');
        $response->assertViewHas('movementAggregates');

        // Memastikan data valuasi uang finansial TIDAK dikirimkan ke view staff
        $response->assertViewMissing('total_value');
        $response->assertViewMissing('totalAssetValue');
        $response->assertViewMissing('categoryValuations');
        $response->assertViewMissing('activeUsersCount');

        // Memastikan tidak ada kartu "Nilai Aset Terdaftar"
        $response->assertDontSee('Nilai Aset Terdaftar');
    }

    public function test_admin_has_full_access_to_master_data_and_financial_dashboard(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Suplai Terverifikasi',
            'contact' => '081300001111',
        ]);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-ADMIN-FULL',
            'name' => 'Laptop ThinkPad',
            'price' => 15000000,
            'stock' => 5,
            'minimum_stock' => 2,
        ]);

        // Dashboard Admin memiliki metrik eksekutif finansial lengkap
        $dashboardResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas('total_value');
        $dashboardResponse->assertViewHas('totalAssetValue');
        $dashboardResponse->assertViewHas('categoryValuations');
        $dashboardResponse->assertViewHas('activeUsersCount');
        $dashboardResponse->assertSee('Nilai Aset Terdaftar');

        // Admin dapat mengakses form create master data
        $this->actingAs($this->admin)->get(route('categories.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('suppliers.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('products.create'))->assertStatus(200);

        // Admin dapat mengakses form edit master data
        $this->actingAs($this->admin)->get(route('categories.edit', $this->category))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('suppliers.edit', $supplier))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('products.edit', $product))->assertStatus(200);

        // Admin dapat mengupdate master data
        $this->actingAs($this->admin)->put(route('categories.update', $this->category), [
            'name' => 'Hardware Update Admin',
        ])->assertRedirect(route('categories.index'));

        $this->actingAs($this->admin)->put(route('suppliers.update', $supplier), [
            'name' => 'PT Suplai Terverifikasi Update',
            'contact' => '081399990000',
        ])->assertRedirect(route('suppliers.index'));

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'category_id' => $this->category->id,
            'sku' => 'PROD-ADMIN-FULL',
            'name' => 'Laptop ThinkPad Updated',
            'price' => 16000000,
            'minimum_stock' => 2,
        ])->assertRedirect(route('products.index'));
    }

    public function test_stock_in_supports_product_id_query_parameter_prefill(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PROD-PREFILL-01',
            'name' => 'Barang Siap Restock',
            'price' => 50000,
            'stock' => 1,
            'minimum_stock' => 10,
        ]);

        $response = $this->actingAs($this->staff)->get(route('stock.create', ['product_id' => $product->id]));
        $response->assertStatus(200);
        $response->assertViewHas('selectedProductId', (string) $product->id);
    }
}
