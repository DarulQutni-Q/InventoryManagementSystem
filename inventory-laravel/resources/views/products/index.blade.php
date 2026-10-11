@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Master Data</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Katalog</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Produk</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Katalog Master Produk</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">
                Kelola seluruh item barang inventaris, tingkat stok fisik, dan valuasi aset.
            </p>
        </div>

        @if(Auth::user()->isAdmin())
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs self-start sm:self-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Produk</span>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-black/[0.06] text-stone-600 text-xs font-mono self-start sm:self-auto shadow-2xs">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Katalog (Mode Baca)</span>
            </span>
        @endif
    </div>

    <!-- Filter & Search Toolbar (White Rounded Card) -->
    <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Cari Produk / SKU
                </label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors"
                        placeholder="Contoh: Monitor, PROD-...">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label for="category_id" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Kategori Barang
                </label>
                <select id="category_id" name="category_id" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label for="sort" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Urutkan Berdasarkan
                </label>
                <select id="sort" name="sort" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                    <option value="name" {{ ($sort ?? 'name') === 'name' ? 'selected' : '' }}>Nama Produk</option>
                    <option value="sku" {{ ($sort ?? '') === 'sku' ? 'selected' : '' }}>Kode SKU</option>
                    <option value="price" {{ ($sort ?? '') === 'price' ? 'selected' : '' }}>Harga Jual</option>
                    <option value="stock" {{ ($sort ?? '') === 'stock' ? 'selected' : '' }}>Saldo Stok</option>
                    <option value="created_at" {{ ($sort ?? '') === 'created_at' ? 'selected' : '' }}>Waktu Pendaftaran</option>
                </select>
            </div>

            <!-- Direction & Action Buttons -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="direction" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                        Urutan
                    </label>
                    <select id="direction" name="direction" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                        <option value="asc" {{ ($direction ?? 'asc') === 'asc' ? 'selected' : '' }}>Naik (A-Z / Min)</option>
                        <option value="desc" {{ ($direction ?? '') === 'desc' ? 'selected' : '' }}>Turun (Z-A / Max)</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'sort', 'direction']))
                    <a href="{{ route('products.index') }}" class="px-3 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs sm:text-sm font-medium transition-colors" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Product Card Grid (BizLink / Ronas IT Aesthetic Showcase) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold uppercase tracking-wider font-mono text-stone-600">
                Tampilan Grid Kartu &middot; {{ $products->total() }} Produk
            </h2>
            <span class="text-xs text-stone-400 font-mono">Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($products as $product)
                @php $isLow = $product->isLowStock(); @endphp
                <div class="bg-white rounded-2xl p-5 border border-black/[0.05] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 font-semibold border border-black/[0.04]">
                                {{ $product->sku }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono {{ $isLow ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $isLow ? 'Perlu Reorder' : 'Stok Aman' }}
                            </span>
                        </div>

                        <div>
                            <a href="{{ route('products.show', $product) }}" class="font-bold text-stone-900 group-hover:text-black transition-colors block text-base leading-snug">
                                {{ $product->name }}
                            </a>
                            <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                <span>{{ $product->category->name ?? 'Tanpa Kategori' }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <div class="text-[10px] font-mono uppercase text-stone-400">Harga Jual</div>
                                <div class="font-mono font-bold text-stone-900 mt-0.5">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-mono uppercase text-stone-400">Saldo Fisik</div>
                                <div class="font-mono font-extrabold mt-0.5 {{ $isLow ? 'text-amber-700' : 'text-stone-900' }}">
                                    {{ number_format($product->stock) }} unit
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                        <a href="{{ route('products.show', $product) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                            Detail &rarr;
                        </a>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('stock.create', ['product_id' => $product->id]) }}" class="px-2.5 py-1 rounded-lg bg-[#ECEEE8] hover:bg-stone-200 text-stone-800 text-[11px] font-semibold transition-colors" title="Restock Masuk">
                                + Restock
                            </a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-50" title="Edit Produk">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg border border-stone-200 text-stone-600 hover:text-rose-600 hover:bg-rose-50" title="Hapus Produk">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-black/[0.05] text-stone-400 text-sm">
                    Tidak ada produk ditemukan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Data Table Ledger (Pelengkap Rapi untuk Audit & Kepatuhan) -->
    <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-black/[0.04] flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-stone-900 tracking-tight">Tabel Buku Besar Produk</h3>
            <span class="text-xs text-stone-400 font-mono">Format Tabel Lengkap</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                    <tr>
                        <th class="py-3.5 px-6">SKU</th>
                        <th class="py-3.5 px-6">Nama Produk</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6 text-right">Harga Jual</th>
                        <th class="py-3.5 px-6 text-right">Stok Fisik</th>
                        <th class="py-3.5 px-6 text-right">Min. Stok</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-stone-50/70 transition-colors">
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-800 font-semibold inline-block">
                                    {{ $product->sku }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <a href="{{ route('products.show', $product) }}" class="font-bold text-stone-900 hover:text-black transition-colors">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="py-3.5 px-6 text-stone-600 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-stone-100 text-stone-700">
                                    {{ $product->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right font-mono font-bold text-stone-900 whitespace-nowrap">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-6 text-right font-bold {{ $product->isLowStock() ? 'text-amber-700' : 'text-stone-900' }} font-mono whitespace-nowrap">
                                {{ number_format($product->stock) }}
                            </td>
                            <td class="py-3.5 px-6 text-right text-stone-500 font-mono whitespace-nowrap">
                                {{ number_format($product->minimum_stock) }}
                            </td>
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                @if($product->isLowStock())
                                    <span class="bg-amber-100 text-amber-800 text-[11px] px-2.5 py-0.5 rounded-full font-semibold inline-flex items-center">
                                        Perlu Reorder
                                    </span>
                                @else
                                    <span class="bg-emerald-100 text-emerald-800 text-[11px] px-2.5 py-0.5 rounded-full font-semibold inline-flex items-center">
                                        Stok Aman
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- View Detail -->
                                    <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-stone-200 bg-white text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition-colors shadow-2xs" title="Lihat Detail Produk">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if(Auth::user()->isAdmin())
                                        <!-- Edit Product (Admin Only) -->
                                        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-stone-200 bg-white text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition-colors shadow-2xs" title="Edit Data Produk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Product (Admin Only) -->
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-stone-200 bg-white text-stone-600 hover:text-rose-600 hover:bg-rose-50 transition-colors shadow-2xs" title="Hapus Produk">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-stone-400">
                                Tidak ada produk yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-black/[0.04]">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
