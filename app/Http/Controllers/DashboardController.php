<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 4 Metrik Statistik Utama
        $totalProducts = Product::count();
        $totalAssetValue = Product::selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0;
        $lowStockCount = Product::lowStock()->count();
        
        $totalStockIn = StockMovement::where('type', 'IN')->sum('quantity');
        $totalStockOut = StockMovement::where('type', 'OUT')->sum('quantity');

        // Daftar produk yang menyentuh batas minimum stok
        $criticalProducts = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // 5 mutasi inventaris terbaru untuk monitoring cepat
        $recentMovements = StockMovement::with(['product', 'user.profile'])
            ->latest('id')
            ->limit(5)
            ->get();

        // Agregasi pergerakan barang per produk (GROUP BY)
        $movementAggregates = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'products.sku as product_sku',
                DB::raw("SUM(CASE WHEN stock_movements.type = 'IN' THEN stock_movements.quantity ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN stock_movements.type = 'OUT' THEN stock_movements.quantity ELSE 0 END) as total_out")
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_in')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalAssetValue',
            'lowStockCount',
            'totalStockIn',
            'totalStockOut',
            'criticalProducts',
            'recentMovements',
            'movementAggregates'
        ));
    }
}
