@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Minimalist Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-zinc-200/80">
        <div>
            <!-- Minimalist Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-zinc-400 font-mono mb-1.5" aria-label="Breadcrumb">
                <span>Inventori</span>
                <span class="text-zinc-300">/</span>
                <span class="text-zinc-700 font-medium">Dashboard</span>
                <span class="sr-only">Dashboard Ringkasan Inventaris</span>
            </nav>

            @if(Auth::user()->isAdmin())
                <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 tracking-tight">Ringkasan Eksekutif Inventaris</h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                    Ikhtisar manajerial valuasi aset, volume persediaan, dan indikator kesehatan stok gudang.
                </p>
            @else
                <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 tracking-tight">Pusat Operasional Logistik</h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                    Antarmuka kerja harian gudang untuk pemantauan fisik, pencatatan mutasi, dan aksi cepat restock barang.
                </p>
            @endif
        </div>

        <!-- Quick Action Buttons: Crisp, Precise, 1 SVG Icon -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-medium transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Catat Barang Masuk</span>
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs sm:text-sm font-medium transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
                <span>Catat Barang Keluar</span>
            </a>
        </div>
    </div>

    <!-- Bento Inventory Ledger Ribbon (Hairline Grid, Zero Pastel Slop) -->
    <div class="bg-white rounded-xl border border-zinc-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
        @if(Auth::user()->isAdmin())
            <!-- Admin Metrics: Nilai Aset Terdaftar & Metrik Manajerial -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200/80">
                <!-- Cell 1: Nilai Aset Terdaftar -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Nilai Aset Terdaftar
                    </div>
                    <div class="mt-2 text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900 font-mono">
                        Rp {{ number_format($totalAssetValue ?? $total_value ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">Berdasarkan kalkulasi stok &times; harga</div>
                </div>

                <!-- Cell 2: Sebaran Katalog -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Sebaran Katalog
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($totalProducts) }}</span>
                        <span class="text-xs text-zinc-500 font-sans font-medium">SKU Aktif</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">{{ isset($categoryValuations) ? $categoryValuations->count() . ' kategori produk' : 'Item aktif dalam katalog' }}</div>
                </div>

                <!-- Cell 3: Indikator Defisit -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Indikator Defisit
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($lowStockCount) }}</span>
                        <span class="text-xs font-sans font-medium {{ $lowStockCount > 0 ? 'text-amber-700 font-semibold' : 'text-zinc-500' }}">SKU Menipis</span>
                    </div>
                    <div class="mt-1 text-xs {{ $lowStockCount > 0 ? 'text-amber-600 font-medium' : 'text-zinc-400' }}">
                        {{ $lowStockCount > 0 ? 'Perlu Reorder: butuh restock pengadaan' : 'Perlu Reorder: nihil (seluruh stok aman)' }}
                    </div>
                </div>

                <!-- Cell 4: Arus Barang Fisik -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Arus Barang Fisik
                    </div>
                    <div class="mt-2 flex items-baseline gap-2 font-mono text-xl lg:text-2xl font-bold">
                        <span class="text-emerald-700">+{{ number_format($totalStockIn) }}</span>
                        <span class="text-zinc-300 font-normal">/</span>
                        <span class="text-zinc-800">-{{ number_format($totalStockOut) }}</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">+{{ number_format($totalStockIn) }} unit masuk / -{{ number_format($totalStockOut) }} unit keluar</div>
                </div>
            </div>
        @else
            <!-- Staff Metrics: Mode Pusat Operasional Logistik (TANPA Nilai Rupiah) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200/80">
                <!-- Cell 1: Antrean Restock Mendesak -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Antrean Restock Mendesak
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($lowStockCount) }}</span>
                        <span class="text-xs font-sans font-medium {{ $lowStockCount > 0 ? 'text-amber-700 font-semibold' : 'text-zinc-500' }}">SKU Kritis</span>
                    </div>
                    <div class="mt-1 text-xs {{ $lowStockCount > 0 ? 'text-amber-600 font-medium' : 'text-zinc-400' }}">
                        {{ $lowStockCount > 0 ? 'Perlu Reorder: prioritas penerimaan hari ini' : 'Perlu Reorder: nihil (stok aman)' }}
                    </div>
                </div>

                <!-- Cell 2: Total Fisik Barang di Gudang -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Total Fisik Barang di Gudang
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($totalProducts) }}</span>
                        <span class="text-xs text-zinc-500 font-sans font-medium">SKU di gudang</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">Total katalog item fisik aktif</div>
                </div>

                <!-- Cell 3: Arus Unit Masuk (+) -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Arus Unit Masuk (+)
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-emerald-700">+{{ number_format($totalStockIn) }}</span>
                        <span class="text-xs text-zinc-500 font-sans font-medium">unit</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">Akumulasi fisik barang masuk</div>
                </div>

                <!-- Cell 4: Arus Unit Keluar (-) -->
                <div class="p-5 lg:p-6 bg-white">
                    <div class="text-[11px] font-mono font-medium text-zinc-500 uppercase tracking-wider">
                        Arus Unit Keluar (-)
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5 font-mono">
                        <span class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">-{{ number_format($totalStockOut) }}</span>
                        <span class="text-xs text-zinc-500 font-sans font-medium">unit</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-400">Akumulasi fisik barang keluar</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Tabel Stok Kritis & Perlu Restock -->
    <div class="bg-white rounded-xl border border-zinc-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200/80 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-zinc-900 text-sm sm:text-base tracking-tight">Daftar Stok Kritis &amp; Perlu Restock</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Produk dengan tingkat persediaan fisik berada pada atau di bawah batas minimum.</p>
            </div>
            @if($criticalProducts->count() > 0)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-medium text-zinc-700 bg-zinc-100 border border-zinc-200/80 rounded-md">
                    {{ $criticalProducts->count() }} item butuh restock
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-medium text-emerald-700 bg-emerald-500/10 border border-emerald-500/20 rounded-md">
                    Semua stok aman
                </span>
            @endif
        </div>

        @if($criticalProducts->isEmpty())
            <div class="p-8 text-center text-zinc-500">
                <p class="font-medium text-zinc-700 text-sm">Semua stok barang berada di atas ambang batas minimum aman.</p>
                <p class="text-xs text-zinc-400 mt-0.5">Tidak ada item yang membutuhkan reorder atau penerimaan restock mendesak saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/75 text-zinc-500 text-[11px] font-semibold uppercase tracking-wider border-b border-zinc-200/80">
                        <tr>
                            <th class="px-6 py-3.5">SKU</th>
                            <th class="px-6 py-3.5">Nama Produk</th>
                            <th class="px-6 py-3.5">Kategori</th>
                            <th class="px-6 py-3.5 text-right">Stok Fisik</th>
                            <th class="px-6 py-3.5 text-right">Batas Min.</th>
                            <th class="px-6 py-3.5 text-center">Defisit</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($criticalProducts as $prod)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-xs font-medium px-2 py-0.5 bg-zinc-100 rounded text-zinc-700 border border-zinc-200/70 inline-block">
                                        {{ $prod->sku }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-zinc-900">
                                    <a href="{{ route('products.show', $prod) }}" class="hover:text-[#5e6ad2] transition-colors">
                                        {{ $prod->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 text-xs">
                                    {{ $prod->category->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-rose-600 font-mono">
                                    {{ number_format($prod->stock) }}
                                </td>
                                <td class="px-6 py-4 text-right text-zinc-500 font-mono">
                                    {{ number_format($prod->minimum_stock) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium font-mono text-rose-600 bg-rose-500/10 border border-rose-500/20">
                                        -{{ max(0, $prod->minimum_stock - $prod->stock) }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-medium transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Restock</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section Asimetris: Log Mutasi Terkini & Agregasi Produk -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Panel Kiri: 5 Mutasi Terkini -->
        <div class="bg-white rounded-xl border border-zinc-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200/80 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-zinc-900 text-sm sm:text-base tracking-tight">Mutasi Terkini</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">5 catatan pergerakan barang terbaru di gudang</p>
                </div>
                <a href="{{ route('stock.index') }}" class="text-xs font-medium text-[#5e6ad2] hover:text-[#4f5ac4] transition-colors">
                    Lihat Semua Mutasi &rarr;
                </a>
            </div>

            <div class="divide-y divide-zinc-100">
                @forelse($recentMovements as $mov)
                    <div class="p-4 flex items-center justify-between hover:bg-zinc-50/60 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-mono text-xs font-semibold {{ $mov->type === 'IN' ? 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20' : 'bg-zinc-100 text-zinc-700 border border-zinc-200/80' }}">
                                {{ $mov->type }}
                            </span>
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-zinc-900 leading-tight">
                                    {{ $mov->product->name ?? 'Produk Dihapus' }}
                                </p>
                                <p class="text-[11px] text-zinc-400 font-mono mt-0.5">
                                    {{ $mov->created_at->format('d M Y, H:i') }} &middot; Oleh: {{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right font-mono">
                            <span class="font-bold text-xs sm:text-sm {{ $mov->type === 'IN' ? 'text-emerald-700' : 'text-zinc-800' }}">
                                {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }} unit
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-zinc-400 text-xs sm:text-sm">Belum ada riwayat mutasi barang.</div>
                @endforelse
            </div>
        </div>

        <!-- Panel Kanan: Agregasi Mutasi Per Produk (Top Volume) -->
        <div class="bg-white rounded-xl border border-zinc-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200/80">
                <h2 class="font-semibold text-zinc-900 text-sm sm:text-base tracking-tight">Agregasi Volume Per Produk</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Akumulasi pergerakan barang tertinggi berdasarkan query analitik</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/75 text-zinc-500 text-[11px] font-semibold uppercase tracking-wider border-b border-zinc-200/80">
                        <tr>
                            <th class="px-6 py-3.5">Nama Produk &amp; SKU</th>
                            <th class="px-6 py-3.5 text-right">Total Masuk (IN)</th>
                            <th class="px-6 py-3.5 text-right">Total Keluar (OUT)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($movementAggregates as $agg)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-zinc-900 text-xs sm:text-sm">{{ $agg->product_name }}</div>
                                    <div class="text-[11px] text-zinc-400 font-mono">{{ $agg->product_sku }}</div>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-emerald-700 font-mono text-xs sm:text-sm">
                                    +{{ number_format($agg->total_in) }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-zinc-800 font-mono text-xs sm:text-sm">
                                    -{{ number_format($agg->total_out) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-center text-zinc-400 text-xs sm:text-sm">Belum ada data agregasi mutasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
