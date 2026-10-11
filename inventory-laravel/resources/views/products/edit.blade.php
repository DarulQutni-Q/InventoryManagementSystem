@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
        <div>
            <div class="text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-widest mb-1">
                Katalog Master Produk
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Data Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Perbarui informasi katalog master untuk SKU:
                <span class="font-mono font-semibold text-slate-700">{{ $product->sku }}</span>
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('products.update', $product) }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)] p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Informasi Utama -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                Informasi Utama
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- SKU -->
                <div>
                    <label for="sku" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Kode SKU <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('sku') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono text-slate-800 transition-colors"
                        placeholder="Contoh: PROD-ELEC-001">
                    @error('sku')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('category_id') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors">
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nama Produk -->
            <div>
                <label for="name" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                    Nama Produk <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Stok & Penetapan Harga -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                Stok Fisik & Penetapan Harga
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Harga Jual -->
                <div>
                    <label for="price" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Harga (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="100" required
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('price') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono text-slate-800 transition-colors">
                    @error('price')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stok Fisik Saat Ini (Read-only) -->
                <div>
                    <label class="block text-[11px] font-mono font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        Stok Fisik Saat Ini
                    </label>
                    <div class="w-full px-3.5 py-2.5 rounded-lg bg-slate-100 border border-slate-200 text-sm font-bold text-slate-800 font-mono flex items-center justify-between">
                        <span>{{ number_format($product->stock) }} unit</span>
                        <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="text-xs font-normal text-indigo-600 hover:underline">
                            Mutasi &rarr;
                        </a>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Stok hanya dapat diubah melalui transaksi mutasi.</p>
                </div>

                <!-- Minimum Stok -->
                <div>
                    <label for="minimum_stock" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Batas Min. Stok <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0" step="1" required
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('minimum_stock') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono text-slate-800 transition-colors">
                    @error('minimum_stock')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Rekanan Supplier (Multi-Supplier) -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                Pemasok / Mitra Supplier (Opsional)
            </h2>

            @php
                $attachedSuppliers = $product->suppliers->keyBy('id');
            @endphp

            <div class="space-y-2 border border-slate-200/80 rounded-xl p-3 bg-slate-50/50 max-h-56 overflow-y-auto">
                @foreach($suppliers as $supplier)
                    @php
                        $isAttached = $attachedSuppliers->has($supplier->id);
                        $savedPrice = $isAttached ? $attachedSuppliers[$supplier->id]->pivot->supplier_price : null;
                    @endphp
                    <div class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-white border border-slate-200/60">
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-800">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->id }}"
                                {{ in_array($supplier->id, old('supplier_ids', $isAttached ? [$supplier->id] : [])) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="font-medium text-xs sm:text-sm">{{ $supplier->name }}</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-slate-400 font-mono">Harga Pasok Rp:</span>
                            <input type="number" name="supplier_prices[{{ $supplier->id }}]"
                                value="{{ old("supplier_prices.{$supplier->id}", $savedPrice) }}"
                                placeholder="0" min="0" step="100"
                                class="w-32 px-2.5 py-1 text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-sm">
                Perbarui Produk
            </button>
        </div>
    </form>
</div>
@endsection
