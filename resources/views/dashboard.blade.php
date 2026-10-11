@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Ringkasan Inventaris</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pemantauan metrik aset logistik, pergerakan stok, dan peringatan dini secara real-time.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Barang Masuk
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
                Barang Keluar
            </a>
        </div>
    </div>

    <!-- 4 Widget Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Widget 1: Total Produk -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Jenis Produk</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($totalProducts) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Item terdaftar di katalog</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>

        <!-- Widget 2: Nilai Aset -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Aset Stok</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h3>
                <p class="text-xs text-slate-500 mt-1">Total valuasi barang aktif</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Widget 3: Stok Kritis -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perlu Reorder</p>
                <h3 class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-2">
                    {{ number_format($lowStockCount) }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">Stok &le; batas minimum</p>
            </div>
            <div class="w-10 h-10 rounded-lg {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <!-- Widget 4: Mutasi Masuk vs Keluar -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pergerakan Unit</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-lg font-bold text-emerald-600">+{{ number_format($totalStockIn) }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-lg font-bold text-amber-600">-{{ number_format($totalStockOut) }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Total Unit Masuk vs Keluar</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabel Peringatan Stok Kritis (Bila Ada) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full {{ $criticalProducts->count() > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></div>
                <h2 class="font-bold text-slate-900 text-base">Peringatan Stok Rendah & Perlu Restock</h2>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                {{ $criticalProducts->count() }} item kritis
            </span>
        </div>

        @if($criticalProducts->isEmpty())
            <div class="p-8 text-center text-slate-500">
                <svg class="w-10 h-10 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium text-slate-700">Semua stok barang berada di atas ambang minimum aman.</p>
                <p class="text-xs text-slate-400 mt-0.5">Tidak ada item yang membutuhkan reorder mendesak saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3">Nama Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3 text-right">Stok Fisik</th>
                            <th class="px-6 py-3 text-right">Batas Min.</th>
                            <th class="px-6 py-3 text-center">Defisit</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($criticalProducts as $prod)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-700">{{ $prod->sku }}</td>
                                <td class="px-6 py-3.5 font-medium text-slate-900">{{ $prod->name }}</td>
                                <td class="px-6 py-3.5 text-slate-600 text-xs">{{ $prod->category->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-rose-600">{{ number_format($prod->stock) }}</td>
                                <td class="px-6 py-3.5 text-right text-slate-600">{{ number_format($prod->minimum_stock) }}</td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800">
                                        -{{ max(0, $prod->minimum_stock - $prod->stock) }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-xs transition-colors">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Dua Kolom: Mutasi Terakhir & Rekap Agregasi -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mutasi Terakhir -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 text-base">5 Mutasi Terkini</h2>
                <a href="{{ route('stock.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Lihat Semua &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentMovements as $mov)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-xs {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $mov->type }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $mov->product->name ?? 'Produk Dihapus' }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $mov->created_at->format('d M Y, H:i') }} &middot; Oleh: {{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-sm {{ $mov->type === 'IN' ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-sm">Belum ada riwayat mutasi barang.</div>
                @endforelse
            </div>
        </div>

        <!-- Rekap Agregasi Produk -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80">
                <h2 class="font-bold text-slate-900 text-base">Agregasi Mutasi Per Produk (Top Volume)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Nama Produk</th>
                            <th class="px-6 py-3 text-right">Total Masuk</th>
                            <th class="px-6 py-3 text-right">Total Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movementAggregates as $agg)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-slate-900">{{ $agg->product_name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $agg->product_sku }}</div>
                                </td>
                                <td class="px-6 py-3.5 text-right font-semibold text-emerald-600">+{{ number_format($agg->total_in) }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold text-amber-600">-{{ number_format($agg->total_out) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-center text-slate-400 text-sm">Belum ada data agregasi mutasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
