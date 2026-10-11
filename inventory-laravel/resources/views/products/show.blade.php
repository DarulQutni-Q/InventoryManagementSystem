@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-semibold">{{ $product->sku }}</span>
                @if($product->isLowStock())
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800">Stok Kritis</span>
                @else
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Stok Aman</span>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $product->name }}</h1>
            <p class="text-sm text-slate-500">Kategori: {{ $product->category->name ?? 'Tanpa Kategori' }}</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
                &larr; Kembali
            </a>
            <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm">
                + Tambah Stok
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="px-3.5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors shadow-sm">
                    Edit Produk
                </a>
            @endif
        </div>
    </div>

    <!-- Info Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Harga Jual Satuan</span>
            <p class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Fisik Tersedia</span>
            <p class="text-xl font-bold {{ $product->isLowStock() ? 'text-rose-600' : 'text-slate-900' }} mt-1">
                {{ number_format($product->stock) }} unit
            </p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Batas Minimum Stok</span>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($product->minimum_stock) }} unit</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Aset</span>
            <p class="text-xl font-bold text-emerald-600 mt-1">
                Rp {{ number_format($product->stock * $product->price, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Rekanan Supplier & Riwayat Mutasi Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Mitra Supplier -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 space-y-4">
            <h2 class="font-bold text-slate-900 text-base pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Mitra Pemasok (Supplier)</span>
                <span class="text-xs font-normal text-slate-500">{{ $product->suppliers->count() }} vendor</span>
            </h2>

            <div class="space-y-3">
                @forelse($product->suppliers as $sup)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="font-semibold text-sm text-slate-900">{{ $sup->name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $sup->contact ?? 'Kontak tidak dicantumkan' }}</div>
                        <div class="mt-2 text-xs flex items-center justify-between font-mono">
                            <span class="text-slate-500">Harga Pasok:</span>
                            <span class="font-bold text-slate-800">
                                {{ $sup->pivot->supplier_price ? 'Rp ' . number_format($sup->pivot->supplier_price, 0, ',', '.') : 'Tidak tercatat' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada mitra pemasok yang dihubungkan ke produk ini.</p>
                @endforelse
            </div>
        </div>

        <!-- 10 Riwayat Mutasi Terkini -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <h2 class="font-bold text-slate-900 text-base">Riwayat Mutasi Stok Terkini</h2>
                <a href="{{ route('stock.index', ['product_id' => $product->id]) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    Lihat Seluruh Log &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3 text-center">Tipe</th>
                            <th class="px-6 py-3 text-right">Jumlah</th>
                            <th class="px-6 py-3">Petugas</th>
                            <th class="px-6 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->stockMovements as $mov)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5 text-xs text-slate-500 font-mono whitespace-nowrap">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $mov->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold {{ $mov->type === 'IN' ? 'text-emerald-600' : 'text-amber-600' }} whitespace-nowrap">
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
