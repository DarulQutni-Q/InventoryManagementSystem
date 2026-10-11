@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Master Data</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Produk</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Edit</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Edit Data Produk</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">
                Perbarui informasi katalog master untuk SKU:
                <span class="font-mono font-bold text-stone-900">{{ $product->sku }}</span>
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-full bg-white border border-black/[0.06] hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors shadow-2xs">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('products.update', $product) }}" method="POST" class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Informasi Utama -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-stone-400 uppercase tracking-wider pb-2 border-b border-stone-100">
                Informasi Utama
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- SKU -->
                <div>
                    <label for="sku" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Kode SKU <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('sku') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm font-mono text-stone-800 transition-colors"
                        placeholder="Contoh: PROD-ELEC-001">
                    @error('sku')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('category_id') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm text-stone-800 transition-colors">
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
                <label for="name" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                    Nama Produk <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm text-stone-800 transition-colors">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Stok & Penetapan Harga -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-stone-400 uppercase tracking-wider pb-2 border-b border-stone-100">
                Stok Fisik & Penetapan Harga
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Harga Jual -->
                <div>
                    <label for="price" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Harga (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="100" required
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('price') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm font-mono text-stone-800 transition-colors">
                    @error('price')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stok Fisik Saat Ini (Read-only) -->
                <div>
                    <label class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                        Stok Fisik Saat Ini
                    </label>
                    <div class="w-full px-3.5 py-2.5 rounded-xl bg-stone-100 border border-stone-200 text-sm font-bold text-stone-900 font-mono flex items-center justify-between">
                        <span>{{ number_format($product->stock) }} unit</span>
                        <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="text-xs font-semibold text-stone-900 hover:underline">
                            Mutasi &rarr;
                        </a>
                    </div>
                    <p class="text-[11px] text-stone-400 mt-1">Hanya dapat diubah melalui mutasi stok.</p>
                </div>

                <!-- Minimum Stok -->
                <div>
                    <label for="minimum_stock" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Batas Min. Stok <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0" step="1" required
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('minimum_stock') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm font-mono text-stone-800 transition-colors">
                    @error('minimum_stock')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Rekanan Supplier (Multi-Supplier) -->
        <div class="space-y-4">
            <h2 class="text-xs font-mono font-semibold text-stone-400 uppercase tracking-wider pb-2 border-b border-stone-100">
                Pemasok / Mitra Supplier (Opsional)
            </h2>

            @php
                $attachedSuppliers = $product->suppliers->keyBy('id');
            @endphp

            <div class="space-y-2 border border-stone-200/80 rounded-xl p-3 bg-stone-50/50 max-h-56 overflow-y-auto">
                @foreach($suppliers as $supplier)
                    @php
                        $isAttached = $attachedSuppliers->has($supplier->id);
                        $savedPrice = $isAttached ? $attachedSuppliers[$supplier->id]->pivot->supplier_price : null;
                    @endphp
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white border border-stone-200/60">
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm text-stone-800">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->id }}"
                                {{ in_array($supplier->id, old('supplier_ids', $isAttached ? [$supplier->id] : [])) ? 'checked' : '' }}
                                class="rounded border-stone-300 text-stone-900 focus:ring-black/20">
                            <span class="font-medium text-xs sm:text-sm">{{ $supplier->name }}</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-stone-400 font-mono">Harga Pasok Rp:</span>
                            <input type="number" name="supplier_prices[{{ $supplier->id }}]"
                                value="{{ old("supplier_prices.{$supplier->id}", $savedPrice) }}"
                                placeholder="0" min="0" step="100"
                                class="w-32 px-2.5 py-1 text-xs rounded-lg border border-stone-200 focus:border-stone-400 focus:ring-1 focus:ring-black/10 font-mono">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 border-t border-stone-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                Perbarui Produk
            </button>
        </div>
    </form>
</div>
@endsection
