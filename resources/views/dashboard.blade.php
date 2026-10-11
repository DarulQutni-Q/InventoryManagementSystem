@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page: Role-Based Differentiation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        @if(Auth::user()->isAdmin())
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-0.5">Dashboard Ringkasan Inventaris</div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ringkasan Eksekutif Inventaris</h1>
                <p class="text-sm text-slate-500 mt-0.5">Ikhtisar manajerial valuasi aset, volume persediaan, dan indikator kesehatan stok gudang.</p>
            </div>
        @else
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-0.5">Dashboard Ringkasan Inventaris</div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Operasional Logistik</h1>
                <p class="text-sm text-slate-500 mt-0.5">Antarmuka kerja harian gudang untuk pemantauan fisik, pencatatan mutasi, dan aksi cepat restock barang.</p>
            </div>
        @endif

        <div class="flex items-center gap-2.5">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Barang Masuk
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
                Barang Keluar
            </a>
        </div>
    </div>

    <!-- Panel Status Operasional / Metrik (Utilitarian Grid, Zero Pastel Slop) -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        @if(Auth::user()->isAdmin())
            <!-- Admin Metrics Grid: 4 Kolom dengan Valuasi Aset Rp -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                <!-- Metrik 1: Valuasi Aset -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nilai Aset Terdaftar</div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 font-mono">
                        Rp {{ number_format($totalAssetValue ?? $total_value ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Kalkulasi stok fisik &times; harga jual</div>
                </div>

                <!-- Metrik 2: Total Jenis Produk -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Jenis Produk</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight text-slate-900 font-mono">{{ number_format($totalProducts) }}</span>
                        <span class="text-xs text-slate-500 font-medium">SKU aktif</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Item tercatat dalam katalog master</div>
                </div>

                <!-- Metrik 3: Status Stok Kritis -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Perlu Reorder (Stok Kritis)</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono">
                            {{ number_format($lowStockCount) }}
                        </span>
                        <span class="text-xs {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-500' }} font-medium">SKU &le; ambang batas</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ $lowStockCount > 0 ? 'Memerlukan koordinasi pengadaan' : 'Seluruh stok dalam level aman' }}
                    </div>
                </div>

                <!-- Metrik 4: Pergerakan Fisik -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pergerakan Unit Fisik</div>
                    <div class="mt-2 flex items-baseline gap-2 font-mono text-xl font-bold">
                        <span class="text-emerald-600">+{{ number_format($totalStockIn) }}</span>
                        <span class="text-slate-300 font-normal">/</span>
                        <span class="text-slate-700">-{{ number_format($totalStockOut) }}</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Akumulasi penerimaan & pengeluaran</div>
                </div>
            </div>
        @else
            <!-- Staff Metrics Grid: Fokus Operasional Unit, Tidak Ada Angka Rupiah -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                <!-- Metrik 1: Antrean Restock Mendesak -->
                <div class="p-5 {{ $lowStockCount > 0 ? 'bg-rose-50/40' : '' }}">
                    <div class="text-[11px] font-semibold {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider">
                        Perlu Reorder (Restock Segera)
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono">
                            {{ number_format($lowStockCount) }}
                        </span>
                        <span class="text-xs {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-slate-500' }} font-medium">produk kritis</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ $lowStockCount > 0 ? 'Prioritas restock fisik hari ini' : 'Semua item berada di atas batas min.' }}
                    </div>
                </div>

                <!-- Metrik 2: Total Item Fisik -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Item Fisik</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight text-slate-900 font-mono">{{ number_format($totalProducts) }}</span>
                        <span class="text-xs text-slate-500 font-medium">SKU di gudang</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Daftar barang inventaris aktif</div>
                </div>

                <!-- Metrik 3: Total Masuk (IN) -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Masuk (IN)</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight text-emerald-600 font-mono">+{{ number_format($totalStockIn) }}</span>
                        <span class="text-xs text-slate-500 font-medium">unit</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Total akumulasi barang masuk</div>
                </div>

                <!-- Metrik 4: Total Keluar (OUT) -->
                <div class="p-5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pengeluaran Keluar (OUT)</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold tracking-tight text-slate-700 font-mono">-{{ number_format($totalStockOut) }}</span>
                        <span class="text-xs text-slate-500 font-medium">unit</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Total akumulasi barang keluar</div>
                </div>
            </div>
        @endif
    </div>

    @if(!Auth::user()->isAdmin() && $lowStockCount > 0)
        <!-- Callout Operasional untuk Staff -->
        <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm">
                <span class="font-semibold">Perhatian Operasional:</span> Terdapat <span class="font-bold">{{ $lowStockCount }} produk</span> dengan saldo stok fisik pada atau di bawah batas minimum. Gunakan tombol aksi <span class="font-medium underline">Restock Masuk</span> pada tabel di bawah untuk langsung mencatat penerimaan barang baru.
            </div>
        </div>
    @endif

    <!-- Tabel Stok Kritis & Perlu Restock -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 text-base">Daftar Stok Kritis & Perlu Restock</h2>
                <p class="text-xs text-slate-500 mt-0.5">Produk dengan tingkat persediaan fisik berada di bawah ambang batas minimum pengamanan.</p>
            </div>
            @if($criticalProducts->count() > 0)
                <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded">
                    {{ $criticalProducts->count() }} Item Kritis
                </span>
            @else
                <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded">
                    Semua Stok Aman
                </span>
            @endif
        </div>

        @if($criticalProducts->isEmpty())
            <div class="p-8 text-center text-slate-500">
                <p class="font-medium text-slate-700">Semua stok barang berada di atas ambang batas minimum aman.</p>
                <p class="text-xs text-slate-400 mt-0.5">Tidak ada item yang membutuhkan reorder atau penerimaan restock mendesak saat ini.</p>
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
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-700">{{ $prod->sku }}</td>
                                <td class="px-6 py-3.5 font-medium text-slate-900">{{ $prod->name }}</td>
                                <td class="px-6 py-3.5 text-slate-600 text-xs">{{ $prod->category->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-rose-600 font-mono">{{ number_format($prod->stock) }}</td>
                                <td class="px-6 py-3.5 text-right text-slate-600 font-mono">{{ number_format($prod->minimum_stock) }}</td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-700 font-mono">
                                        -{{ max(0, $prod->minimum_stock - $prod->stock) }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Restock Masuk
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Dua Kolom: Mutasi Terkini & Agregasi Produk -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mutasi Terkini -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Mutasi Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">5 catatan mutasi barang terbaru di gudang</p>
                </div>
                <a href="{{ route('stock.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Lihat Semua Mutasi &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentMovements as $mov)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded font-mono text-xs font-bold {{ $mov->type === 'IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                {{ $mov->type }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $mov->product->name ?? 'Produk Dihapus' }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $mov->created_at->format('d M Y, H:i') }} &middot; Oleh: {{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}</p>
                            </div>
                        </div>
                        <div class="text-right font-mono">
                            <span class="font-bold text-sm {{ $mov->type === 'IN' ? 'text-emerald-600' : 'text-slate-700' }}">
                                {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }} unit
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-sm">Belum ada riwayat mutasi barang.</div>
                @endforelse
            </div>
        </div>

        <!-- Rekap Agregasi Produk -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="font-bold text-slate-900 text-base">Agregasi Mutasi Produk (Top Volume)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Akumulasi pergerakan barang tertinggi</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Nama Produk & SKU</th>
                            <th class="px-6 py-3 text-right">Total Masuk (IN)</th>
                            <th class="px-6 py-3 text-right">Total Keluar (OUT)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movementAggregates as $agg)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-slate-900">{{ $agg->product_name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $agg->product_sku }}</div>
                                </td>
                                <td class="px-6 py-3.5 text-right font-semibold text-emerald-600 font-mono">+{{ number_format($agg->total_in) }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold text-slate-700 font-mono">-{{ number_format($agg->total_out) }}</td>
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