@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Context Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold border border-slate-200/80">
                    {{ $product->sku }}
                </span>
                @if($product->isLowStock())
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 border border-rose-200/80 text-rose-700 font-mono">
                        Stok Kritis
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 border border-emerald-200/80 text-emerald-700 font-mono">
                        Stok Aman
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $product->name }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kategori: {{ $product->category->name ?? 'Tanpa Kategori' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                &larr; Kembali
            </a>
            <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs sm:text-sm font-medium transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Stok</span>
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-semibold transition-colors shadow-sm">
                    Edit Produk
                </a>
            @endif
        </div>
    </div>

    <!-- Bento Metrics Panel (Hairline 4-Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Harga Jual Satuan -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <span class="text-[11px] font-mono font-semibold text-slate-500 uppercase tracking-wider">Harga Jual Satuan</span>
            <p class="text-xl font-bold text-slate-900 mt-2 font-mono">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Tarif resmi katalog</p>
        </div>

        <!-- Stok Fisik Tersedia -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <span class="text-[11px] font-mono font-semibold text-slate-500 uppercase tracking-wider">Stok Fisik Tersedia</span>
            <p class="text-xl font-bold {{ $product->isLowStock() ? 'text-rose-600' : 'text-slate-900' }} mt-2 font-mono">
                {{ number_format($product->stock) }} unit
            </p>
            <p class="text-xs text-slate-400 mt-1">Saldo aktual di rak gudang</p>
        </div>

        <!-- Batas Minimum Stok -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <span class="text-[11px] font-mono font-semibold text-slate-500 uppercase tracking-wider">Batas Minimum Stok</span>
            <p class="text-xl font-bold text-slate-900 mt-2 font-mono">
                {{ number_format($product->minimum_stock) }} unit
            </p>
            <p class="text-xs text-slate-400 mt-1">Ambang peringatan reorder</p>
        </div>

        <!-- Total Nilai Aset -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <span class="text-[11px] font-mono font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Persediaan</span>
            <p class="text-xl font-bold text-slate-900 mt-2 font-mono">
                Rp {{ number_format($product->stock * $product->price, 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Akumulasi stok &times; harga</p>
        </div>
    </div>

    <!-- Asymmetric Columns: Mitra Supplier & Riwayat Mutasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Mitra Pemasok (Supplier) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] p-5 space-y-4">
            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 text-sm sm:text-base">Mitra Pemasok</h2>
                <span class="text-xs font-mono text-slate-500">{{ $product->suppliers->count() }} vendor</span>
            </div>

            <div class="space-y-3">
                @forelse($product->suppliers as $sup)
                    <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/80">
                        <div class="font-semibold text-sm text-slate-900">{{ $sup->name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $sup->contact ?? 'Kontak tidak dicantumkan' }}</div>
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 text-xs flex items-center justify-between font-mono">
                            <span class="text-slate-400">Harga Pasok:</span>
                            <span class="font-bold text-slate-800">
                                {{ $sup->pivot->supplier_price ? 'Rp ' . number_format($sup->pivot->supplier_price, 0, ',', '.') : 'Tidak tercatat' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada mitra pemasok yang dihubungkan ke produk ini.</p>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: 10 Riwayat Mutasi Terkini -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">Riwayat Mutasi Stok Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan pergerakan fisik barang khusus produk ini</p>
                </div>
                <a href="{{ route('stock.index', ['product_id' => $product->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="px-6 py-3.5">Waktu</th>
                            <th class="px-6 py-3.5 text-center">Tipe</th>
                            <th class="px-6 py-3.5 text-right">Jumlah</th>
                            <th class="px-6 py-3.5">Petugas</th>
                            <th class="px-6 py-3.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->stockMovements as $mov)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3.5 text-xs text-slate-500 font-mono whitespace-nowrap">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold font-mono {{ $mov->type === 'IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200/80' }}">
                                        {{ $mov->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold {{ $mov->type === 'IN' ? 'text-emerald-600' : 'text-slate-700' }} font-mono whitespace-nowrap">
                                    {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-700 whitespace-nowrap">
                                    {{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-600">
                                    {{ $mov->note ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Belum ada catatan mutasi stok untuk produk ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
