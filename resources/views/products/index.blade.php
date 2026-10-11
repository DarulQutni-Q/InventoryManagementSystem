@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Master Produk</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola seluruh item barang inventaris, tingkat stok fisik, dan valuasi aset.</p>
        </div>
        @if(Auth::user()->isAdmin())
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Produk
            </a>
        @endif
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Cari Produk / SKU</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                        placeholder="Contoh: Monitor, PROD-...">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label for="category_id" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Kategori</label>
                <select id="category_id" name="category_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort Column -->
            <div>
                <label for="sort" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Urutkan</label>
                <select id="sort" name="sort" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="name" {{ ($sort ?? 'name') === 'name' ? 'selected' : '' }}>Nama Produk</option>
                    <option value="sku" {{ ($sort ?? '') === 'sku' ? 'selected' : '' }}>SKU</option>
                    <option value="price" {{ ($sort ?? '') === 'price' ? 'selected' : '' }}>Harga</option>
                    <option value="stock" {{ ($sort ?? '') === 'stock' ? 'selected' : '' }}>Stok</option>
                    <option value="created_at" {{ ($sort ?? '') === 'created_at' ? 'selected' : '' }}>Waktu Dibuat</option>
                </select>
            </div>

            <!-- Direction & Action Buttons -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="direction" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Arah</label>
                    <select id="direction" name="direction" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                        <option value="asc" {{ ($direction ?? 'asc') === 'asc' ? 'selected' : '' }}>Naik (A-Z / Min)</option>
                        <option value="desc" {{ ($direction ?? '') === 'desc' ? 'selected' : '' }}>Turun (Z-A / Max)</option>
                    </select>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'sort', 'direction']))
                    <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">SKU</th>
                        <th class="px-6 py-3.5">Nama Produk</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5 text-right">Harga Jual</th>
                        <th class="px-6 py-3.5 text-right">Stok Fisik</th>
                        <th class="px-6 py-3.5 text-right">Min. Stok</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-700 whitespace-nowrap">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                                    {{ $product->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $product->isLowStock() ? 'text-rose-600' : 'text-slate-900' }} whitespace-nowrap">
                                {{ number_format($product->stock) }}
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">
                                {{ number_format($product->minimum_stock) }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($product->isLowStock())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Stok Kritis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Aman
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('products.edit', $product) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="font-medium text-slate-700">Tidak ada produk yang sesuai dengan kriteria pencarian.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci atau reset filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
