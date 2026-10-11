@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Logistik</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Mutasi</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Barang Masuk</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Pencatatan Barang Masuk (IN)</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Tambah saldo persediaan fisik melalui penerimaan barang baru.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="px-3.5 py-2 rounded-full bg-white border border-black/[0.06] hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors shadow-2xs">
            &larr; Riwayat Mutasi
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
        <!-- Contextual Guided Restock Banner -->
        <div class="p-4 rounded-2xl bg-[#EEF0E9] border border-black/[0.05] text-stone-900 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-stone-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="leading-relaxed">
                <span class="font-bold">Mode Restock Terarah:</span>
                Produk <span class="font-semibold">{{ $prefilledProduct->name }}</span> (SKU: <span class="font-mono font-bold">{{ $prefilledProduct->sku }}</span>) otomatis terpilih.
                Stok fisik saat ini <span class="font-mono font-bold text-rose-700">{{ $prefilledProduct->stock }} unit</span> &mdash; Batas minimum <span class="font-mono font-bold">{{ $prefilledProduct->minimum_stock }} unit</span>.
                @if($prefilledProduct->stock < $prefilledProduct->minimum_stock)
                    Defisit kebutuhan pemulihan: <span class="font-mono font-bold text-stone-900 underline">{{ $recommendedQty }} unit</span>.
                @endif
            </div>
        </div>
    @endif

    <!-- Form Container (White Rounded-2xl Card) -->
    <form action="{{ route('stock.store') }}" method="POST" class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs p-6 sm:p-8 space-y-5">
        @csrf
        <input type="hidden" name="type" value="IN">

        <!-- Banner Info ACID (Hairline border, 20px shield icon) -->
        <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/60 text-emerald-950 text-xs flex items-start gap-2.5">
            <svg class="w-5 h-5 text-emerald-700 flex-shrink-0" style="width: 20px; height: 20px; min-width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div class="leading-relaxed">
                <span class="font-bold">Transaksi Database Atomik (ACID):</span>
                Peningkatan saldo fisik dan penulisan buku besar mutasi dieksekusi secara terisolasi.
            </div>
        </div>

        <!-- Pilih Produk -->
        <div>
            <label for="product_id" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Pilih Produk <span class="text-rose-500">*</span>
            </label>
            <select id="product_id" name="product_id" required autofocus
                class="w-full px-3.5 py-2.5 rounded-xl border @error('product_id') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
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
            <label for="quantity" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Jumlah Unit Masuk <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $recommendedQty) }}" min="1" step="1" required
                class="w-full px-3.5 py-2.5 rounded-xl border @error('quantity') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm font-mono text-stone-800 transition-colors"
                placeholder="Jumlah unit masuk">
            @error('quantity')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan / Referensi -->
        <div>
            <label for="note" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Keterangan / Nomor Referensi / Surat Jalan
            </label>
            <textarea id="note" name="note" rows="3"
                class="w-full px-3.5 py-2.5 rounded-xl border @error('note') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors"
                placeholder="Contoh: Penerimaan PO-2026-001 dari PT Mitra">{{ old('note') }}</textarea>
            @error('note')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 border-t border-stone-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('stock.index') }}" class="px-4 py-2 rounded-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                Catat Barang Masuk
            </button>
        </div>
    </form>
</div>
@endsection
