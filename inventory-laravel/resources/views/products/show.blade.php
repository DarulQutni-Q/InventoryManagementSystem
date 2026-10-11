@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Context Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-black/[0.04]">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="font-mono text-xs px-2.5 py-0.5 rounded-md bg-stone-100 text-stone-800 font-semibold border border-black/[0.04]">
                    {{ $product->sku }}
                </span>
                @if($product->isLowStock())
                    <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-0.5 rounded-full font-semibold inline-flex items-center">
                        Perlu Reorder
                    </span>
                @else
                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-0.5 rounded-full font-semibold inline-flex items-center">
                        Stok Aman
                    </span>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">{{ $product->name }}</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Kategori: {{ $product->category->name ?? 'Tanpa Kategori' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-full bg-white border border-black/[0.06] hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors shadow-2xs">
                &larr; Kembali
            </a>
            <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Catat Masuk</span>
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 rounded-full bg-white hover:bg-stone-50 text-stone-800 text-xs sm:text-sm font-semibold transition-colors border border-black/[0.06] shadow-2xs">
                    Edit Produk
                </a>
            @endif
        </div>
    </div>

    <!-- 4-Kolom Metrics Overview Panel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Harga Jual Satuan -->
        <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
            <span class="text-[11px] font-mono font-bold text-stone-400 uppercase tracking-wider">Harga Jual Satuan</span>
            <p class="text-2xl font-extrabold text-stone-900 mt-2 font-mono tracking-tight">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
            <p class="text-xs text-stone-400 mt-1">Tarif resmi katalog</p>
        </div>

        <!-- Stok Fisik Tersedia -->
        <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
            <span class="text-[11px] font-mono font-bold text-stone-400 uppercase tracking-wider">Stok Fisik Tersedia</span>
            <p class="text-2xl font-extrabold {{ $product->isLowStock() ? 'text-rose-600' : 'text-stone-900' }} mt-2 font-mono tracking-tight">
                {{ number_format($product->stock) }} unit
            </p>
            <p class="text-xs text-stone-400 mt-1">Saldo aktual di rak gudang</p>
        </div>

        <!-- Batas Minimum Stok -->
        <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
            <span class="text-[11px] font-mono font-bold text-stone-400 uppercase tracking-wider">Batas Min. Stok</span>
            <p class="text-2xl font-extrabold text-stone-900 mt-2 font-mono tracking-tight">
                {{ number_format($product->minimum_stock) }} unit
            </p>
            <p class="text-xs text-stone-400 mt-1">Ambang peringatan reorder</p>
        </div>

        <!-- Total Nilai Persediaan -->
        <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
            <span class="text-[11px] font-mono font-bold text-stone-400 uppercase tracking-wider">Total Nilai Persediaan</span>
            <p class="text-2xl font-extrabold text-stone-900 mt-2 font-mono tracking-tight">
                Rp {{ number_format($product->stock * $product->price, 0, ',', '.') }}
            </p>
            <p class="text-xs text-stone-400 mt-1">Akumulasi stok &times; harga</p>
        </div>
    </div>

    <!-- Asymmetric Columns: Mitra Supplier & Riwayat Mutasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Mitra Pemasok (Supplier) -->
        <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs p-5 space-y-4">
            <div class="pb-3 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-bold text-stone-900 text-sm sm:text-base">Mitra Pemasok</h2>
                <span class="text-xs font-mono text-stone-400">{{ $product->suppliers->count() }} vendor</span>
            </div>

            <div class="space-y-3">
                @forelse($product->suppliers as $sup)
                    <div class="p-3.5 rounded-xl bg-stone-50/70 border border-stone-200/80">
                        <div class="font-bold text-sm text-stone-900">{{ $sup->name }}</div>
                        <div class="text-xs text-stone-500 mt-0.5">{{ $sup->contact ?? 'Kontak tidak dicantumkan' }}</div>
                        <div class="mt-2.5 pt-2 border-t border-stone-200/60 text-xs flex items-center justify-between font-mono">
                            <span class="text-stone-400">Harga Pasok:</span>
                            <span class="font-bold text-stone-900">
                                {{ $sup->pivot->supplier_price ? 'Rp ' . number_format($sup->pivot->supplier_price, 0, ',', '.') : 'Tidak tercatat' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 py-6 text-center">Belum ada mitra pemasok yang dihubungkan ke produk ini.</p>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: 10 Riwayat Mutasi Terkini -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden">
            <div class="px-6 py-4 border-b border-black/[0.04] flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-stone-900 text-sm sm:text-base">Riwayat Mutasi Stok Terkini</h2>
                    <p class="text-xs text-stone-500 mt-0.5">Catatan pergerakan fisik barang khusus produk ini</p>
                </div>
                <a href="{{ route('stock.index', ['product_id' => $product->id]) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                        <tr>
                            <th class="py-3 px-6">Waktu</th>
                            <th class="py-3 px-6 text-center">Tipe</th>
                            <th class="py-3 px-6 text-right">Volume</th>
                            <th class="py-3 px-6">Petugas</th>
                            <th class="py-3 px-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($product->stockMovements as $mov)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="py-3 px-6 font-mono text-stone-400 whitespace-nowrap">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-6 text-center whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-800' }}">
                                        {{ $mov->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-right font-mono font-bold whitespace-nowrap {{ $mov->type === 'IN' ? 'text-emerald-700' : 'text-stone-900' }}">
                                    {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                                </td>
                                <td class="py-3 px-6 whitespace-nowrap text-stone-700">
                                    {{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}
                                </td>
                                <td class="py-3 px-6 text-stone-500">
                                    {{ $mov->note ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-stone-400">
                                    Belum ada catatan mutasi untuk produk ini.
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
