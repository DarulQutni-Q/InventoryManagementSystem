@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pencatatan Barang Masuk (IN)</h1>
            <p class="text-sm text-slate-500 mt-0.5">Tambah saldo stok fisik inventaris melalui penerimaan barang baru.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
            &larr; Riwayat
        </a>
    </div>
 
    @php
        $prefilledId = old('product_id', $selectedProductId ?? request('product_id'));
        $prefilledProduct = $prefilledId ? $products->firstWhere('id', $prefilledId) : null;
        $recommendedQty = ($prefilledProduct && $prefilledProduct->stock < $prefilledProduct->minimum_stock)
            ? max(1, $prefilledProduct->minimum_stock - $prefilledProduct->stock)
            : 1;
    @endphp

    @if($prefilledProduct)
        <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <span class="font-bold">Mode Restock Terarah:</span>
                Produk <span class="font-semibold">{{ $prefilledProduct->name }}</span> (SKU: {{ $prefilledProduct->sku }}) terpilih otomatis.
                Stok fisik saat ini <span class="font-bold text-rose-700">{{ $prefilledProduct->stock }} unit</span> &mdash; Batas minimum <span class="font-bold">{{ $prefilledProduct->minimum_stock }} unit</span>.
                @if($prefilledProduct->stock < $prefilledProduct->minimum_stock)
                    Defisit kebutuhan pemulihan: <span class="font-bold text-indigo-700">{{ $recommendedQty }} unit</span>.
                @endif
            </div>
        </div>
    @endif

     <form action="{{ route('stock.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5">
         @csrf
         <input type="hidden" name="type" value="IN">
 
         <!-- Banner Info ACID -->
         <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-2.5">
             <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
             </svg>
             <div>
                 <span class="font-bold">Transaksi Database Atomik (ACID):</span>
                 Peningkatan stok produk dan log mutasi dijalankan di dalam blok transaksi terisolasi.
             </div>
         </div>
 
         <!-- Pilih Produk -->
        <div>
            <label for="product_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Pilih Produk <span class="text-rose-500">*</span>
            </label>
            <select id="product_id" name="product_id" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('product_id') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                <option value="">Pilih barang...</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ (string) old('product_id', $selectedProductId ?? request('product_id')) === (string) $prod->id ? 'selected' : '' }}>
                        {{ $prod->name }} (SKU: {{ $prod->sku }}) &mdash; Stok Saat Ini: {{ $prod->stock }} unit
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jumlah Barang Masuk -->
        <div>
            <label for="quantity" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Jumlah Unit Masuk <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $recommendedQty) }}" min="1" step="1" required
                 class="w-full px-3.5 py-2.5 rounded-lg border @error('quantity') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                 placeholder="Jumlah unit masuk">
            @error('quantity')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan / Nomor Surat Jalan -->
        <div>
            <label for="note" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Keterangan / Nomor Referensi / Surat Jalan
            </label>
            <textarea id="note" name="note" rows="3"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('note') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Contoh: Penerimaan PO-2026-001 dari PT Mitra">{{ old('note') }}</textarea>
            @error('note')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Submit -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('stock.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm">
                Catat Barang Masuk
            </button>
        </div>
    </form>
</div>
@endsection
