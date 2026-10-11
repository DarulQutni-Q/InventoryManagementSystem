<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockMovement::with(['product.category', 'user.profile']);

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->input('type')) {
            if (in_array($type, ['IN', 'OUT'], true)) {
                $query->where('type', $type);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $movements = $query->latest('id')->paginate(10)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('stock.index', compact('movements', 'products'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        return view('stock.in', compact('products'));
    }

    public function createOut(): View
    {
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();

        return view('stock.out', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:IN,OUT'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Pessimistic Locking: SELECT ... FOR UPDATE untuk mengeliminasi race condition TOCTOU
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

                if ($validated['type'] === 'OUT') {
                    if ($product->stock < $validated['quantity']) {
                        throw ValidationException::withMessages([
                            'quantity' => "Stok tidak mencukupi! Stok saat ini: {$product->stock}, permintaan keluar: {$validated['quantity']}.",
                        ]);
                    }
                    $product->decrement('stock', $validated['quantity']);
                } else {
                    $product->increment('stock', $validated['quantity']);
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id() ?? 1,
                    'type' => $validated['type'],
                    'quantity' => $validated['quantity'],
                    'note' => $validated['note'] ?? null,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal memproses mutasi: ' . $e->getMessage());
        }

        $typeName = $validated['type'] === 'IN' ? 'masuk' : 'keluar';

        return redirect()->route('stock.index')->with('success', "Transaksi barang {$typeName} berhasil dicatat.");
    }
}
