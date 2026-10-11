<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Metrik Operasional Gudang (tersedia untuk Staff & Admin)
        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $totalStockIn = (int) StockMovement::where('type', 'IN')->sum('quantity');
        $totalStockOut = (int) StockMovement::where('type', 'OUT')->sum('quantity');

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

        $stockHealthPercent = $totalProducts > 0
            ? max(0, min(100, (int) round((($totalProducts - $lowStockCount) / $totalProducts) * 100)))
            : 100;

        $safeProducts = Product::with('category')
            ->whereColumn('stock', '>', 'minimum_stock')
            ->orderBy('stock', 'desc')
            ->limit(8)
            ->get();

        $data = [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'stockHealthPercent' => $stockHealthPercent,
            'totalStockIn' => $totalStockIn,
            'totalStockOut' => $totalStockOut,
            'criticalProducts' => $criticalProducts,
            'safeProducts' => $safeProducts,
            'recentMovements' => $recentMovements,
            'movementAggregates' => $movementAggregates,
        ];

        // Metrik Eksekutif Finansial & Manajerial (HANYA untuk Administrator)
        if ($user && $user->isAdmin()) {
            $totalAssetValue = (float) (Product::selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0);
            $data['total_value'] = $totalAssetValue;
            $data['totalAssetValue'] = $totalAssetValue;
            $data['activeUsersCount'] = User::count();
            $data['categoryValuations'] = Category::query()
                ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                ->select(
                    'categories.id',
                    'categories.name',
                    DB::raw('COUNT(products.id) as products_count'),
                    DB::raw('COALESCE(SUM(products.stock * products.price), 0) as total_value')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_value')
                ->get();
        }

        return view('dashboard', $data);
    }
}
