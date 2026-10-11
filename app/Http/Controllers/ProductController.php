<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $allowedSort = ['name', 'sku', 'price', 'stock', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'name';
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Product::with('category');

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-_]+$/', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
            'supplier_prices' => ['nullable', 'array'],
        ]);

        $product = Product::create($validated);
        $this->syncSuppliers($product, $request);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'suppliers', 'stockMovements' => fn ($q) => $q->latest()->limit(10)->with('user')]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorizeAdmin();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $product->load('suppliers');

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-_]+$/', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
            'supplier_prices' => ['nullable', 'array'],
        ]);

        $product->update($validated);
        $this->syncSuppliers($product, $request);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeAdmin();

        // Proteksi integritas audit: produk dengan riwayat mutasi dilarang dihapus
        if ($product->stockMovements()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena memiliki riwayat mutasi stok.');
        }

        $product->suppliers()->detach();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function syncSuppliers(Product $product, Request $request): void
    {
        $supplierIds = $request->input('supplier_ids', []);
        $supplierPrices = $request->input('supplier_prices', []);
        $syncData = [];

        foreach ($supplierIds as $supplierId) {
            $price = isset($supplierPrices[$supplierId]) && is_numeric($supplierPrices[$supplierId])
                ? (float) $supplierPrices[$supplierId]
                : null;
            $syncData[$supplierId] = ['supplier_price' => $price];
        }

        $product->suppliers()->sync($syncData);
    }

    private function authorizeAdmin(): void
    {
        if (! request()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Administrator.');
        }
    }
}
