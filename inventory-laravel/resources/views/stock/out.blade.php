@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pencatatan Barang Keluar (OUT)</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pengurangan stok fisik gudang untuk pengiriman atau penggunaan internal.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
            &larr; Riwayat
        </a>
    </div>

    <form action="{{ route('stock.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5">
        @csrf
        <input type="hidden" name="type" value="OUT">

        <!-- Banner Info Pessimistic Locking & Proteksi Stok Negatif -->
        <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div>
                <span class="font-bold">Pessimistic Locking Aktif (SELECT ... FOR UPDATE):</span>
                Baris produk dikunci selama transaksi berlangsung untuk mencegah race condition (TOCTOU). Transaksi otomatis di-rollback bila stok tidak mencukupi.
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
                    <option value="{{ $prod->id }}" {{ old('product_id', request('product_id')) == $prod->id ? 'selected' : '' }}>
                        {{ $prod->name }} (SKU: {{ $prod->sku }}) &mdash; Tersedia: {{ $prod->stock }} unit
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jumlah Barang Keluar -->
        <div>
            <label for="quantity" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Jumlah Unit Keluar <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" step="1" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('quantity') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Jumlah unit keluar">
            @error('quantity')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan / Nomor Surat Jalan -->
        <div>
            <label for="note" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Keterangan / Tujuan Distribusi / No. Delivery Order
            </label>
            <textarea id="note" name="note" rows="3"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('note') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Contoh: Pengiriman ke Divisi IT lantai 3">{{ old('note') }}</textarea>
            @error('note')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Submit -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('stock.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors shadow-sm">
                Catat Barang Keluar
            </button>
        </div>
    </form>
</div>
@endsection
