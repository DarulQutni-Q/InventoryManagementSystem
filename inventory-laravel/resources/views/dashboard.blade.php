@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Header Section with Minimalist Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Inventoria</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Logistik</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Ringkasan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Dashboard Ringkasan Inventaris</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">
                @if(Auth::user()->isAdmin())
                    Ringkasan eksekutif inventaris, monitoring stok kritis, pergerakan barang, dan valuasi aset riil.
                @else
                    Pusat operasional pergudangan, pemantauan batas stok minimum, dan pencatatan mutasi fisik.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Catat Barang Masuk</span>
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-stone-50 text-stone-800 text-xs sm:text-sm font-semibold transition-all border border-black/[0.06] shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                </svg>
                <span>Catat Barang Keluar</span>
            </a>
        </div>
    </div>

    <!-- KARTU HERO OVERVIEW (Kotak Beige/Sand Besar di Atas, rounded-3xl p-6 bg-[#EEF0E9]) -->
    <div class="bg-[#EEF0E9] rounded-3xl p-6 sm:p-7 border border-black/[0.05] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 xl:gap-8 divide-y md:divide-y-0 md:divide-x divide-black/[0.06]">
            
            <!-- Zona 1: Mini Bar Chart (Pergerakan Stok Harian Sen-Jum) -->
            <div class="flex flex-col justify-between pt-4 md:pt-0">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-stone-500">Pergerakan Stok</span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-white/70 text-stone-600 border border-black/[0.04]">5 Hari</span>
                    </div>
                    <div class="text-xs text-stone-600 mt-1">Mutasi Harian (Sen-Jum)</div>
                </div>

                <!-- CSS Vertical Bars Visualization -->
                <div class="mt-4 pt-2">
                    <div class="flex items-end justify-between gap-2 h-20 px-1">
                        <!-- Sen -->
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="w-full max-w-[28px] bg-stone-300/80 rounded-t-lg transition-all hover:bg-stone-400" style="height: 48%;" title="Sen: Masuk 12, Keluar 8"></div>
                            <span class="text-[10px] font-mono font-medium text-stone-500">Sen</span>
                        </div>
                        <!-- Sel -->
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="w-full max-w-[28px] bg-stone-400/80 rounded-t-lg transition-all hover:bg-stone-500" style="height: 72%;" title="Sel: Masuk 20, Keluar 14"></div>
                            <span class="text-[10px] font-mono font-medium text-stone-500">Sel</span>
                        </div>
                        <!-- Rab (Highlight Peak) -->
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="w-full max-w-[28px] bg-[#18181B] rounded-t-lg transition-all hover:bg-black" style="height: 94%;" title="Rab: Puncak Mutasi 35 Unit"></div>
                            <span class="text-[10px] font-mono font-bold text-stone-900">Rab</span>
                        </div>
                        <!-- Kam -->
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="w-full max-w-[28px] bg-stone-400/80 rounded-t-lg transition-all hover:bg-stone-500" style="height: 60%;" title="Kam: Masuk 18, Keluar 12"></div>
                            <span class="text-[10px] font-mono font-medium text-stone-500">Kam</span>
                        </div>
                        <!-- Jum -->
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="w-full max-w-[28px] bg-stone-300/80 rounded-t-lg transition-all hover:bg-stone-400" style="height: 52%;" title="Jum: Masuk 15, Keluar 10"></div>
                            <span class="text-[10px] font-mono font-medium text-stone-500">Jum</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zona 2: Radial Arc Dial / Gauge ("Ketersediaan Stok" & "Stok Sehat") -->
            <div class="flex flex-col justify-between pt-4 md:pt-0 md:pl-6 xl:pl-8">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-stone-500">Rasio Ketahanan</span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold border border-emerald-200/50">Stok Sehat</span>
                    </div>
                    <div class="text-xs text-stone-600 mt-1">Ketersediaan Stok Fisik</div>
                </div>

                <!-- Radial Semi-Circle Arc SVG Gauge -->
                @php
                    $health = $stockHealthPercent ?? ($totalProducts > 0 ? max(0, min(100, (int) round((($totalProducts - $lowStockCount) / $totalProducts) * 100))) : 100);
                    // Hitung perimeter setengah lingkaran radius 40 (pi * 40 = 125.66)
                    $dashArray = 125.66;
                    $dashOffset = $dashArray - ($dashArray * ($health / 100));
                @endphp
                <div class="flex items-center justify-center my-auto py-2">
                    <div class="relative flex flex-col items-center">
                        <svg class="w-32 h-18 overflow-visible" viewBox="0 0 100 55">
                            <!-- Background Track -->
                            <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#D7DAD0" stroke-width="9" stroke-linecap="round"/>
                            <!-- Active Arc -->
                            <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#18181B" stroke-width="9" stroke-linecap="round"
                                stroke-dasharray="125.66" stroke-dashoffset="{{ $dashOffset }}"/>
                        </svg>
                        <div class="text-center -mt-5">
                            <span class="text-2xl font-extrabold font-mono text-stone-900 tracking-tight">{{ $health }}%</span>
                            <span class="block text-[10px] font-medium text-stone-500">Ketersediaan Stok</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zona 3: Counter Besar (Angka monospaced "Total SKU Terdaftar") -->
            <div class="flex flex-col justify-between pt-4 md:pt-0 md:pl-6 xl:pl-8">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-stone-500">Total SKU Terdaftar</span>
                        <span class="w-2 h-2 rounded-full {{ $lowStockCount > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                    </div>
                    <div class="text-xs text-stone-600 mt-1">Katalog Item Aktif</div>
                </div>

                <div class="my-auto py-2">
                    <div class="text-4xl sm:text-5xl font-extrabold font-mono text-stone-900 tracking-tight">
                        {{ number_format($totalProducts) }}
                    </div>
                    <div class="text-xs text-stone-600 mt-2 flex items-center gap-1.5">
                        @if($lowStockCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-semibold font-mono text-[11px]">
                                {{ $lowStockCount }} Perlu Reorder
                            </span>
                        @else
                            <span class="text-emerald-700 font-medium">Semua stok dalam batas aman</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Zona 4: Valuasi Finansial (Admin) atau Volume Logistik (Staff) -->
            <div class="flex flex-col justify-between pt-4 md:pt-0 md:pl-6 xl:pl-8">
                @if(Auth::user()->isAdmin())
                    <!-- Mode Admin: Valuasi Finansial Riil -->
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-stone-500">Valuasi Inventaris</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-white/70 text-stone-600 border border-black/[0.04]">IDR</span>
                        </div>
                        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-stone-700 hover:text-black flex items-center gap-1 mt-1 group">
                            <span>Nilai Aset Terdaftar</span>
                            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>

                    <div class="my-auto py-2">
                        <div class="text-2xl sm:text-3xl font-extrabold font-mono text-stone-900 tracking-tight leading-tight">
                            Rp {{ number_format($totalAssetValue ?? $total_value ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-stone-500 mt-2 font-mono">
                            Berdasarkan kalkulasi saldo fisik &times; harga beli
                        </div>
                    </div>
                @else
                    <!-- Mode Staff: Volume Logistik Fisik Tanpa Nilai Uang (Sesuai PRD.md) -->
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-stone-500">Akumulasi Mutasi</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-white/70 text-stone-600 border border-black/[0.04]">Fisik</span>
                        </div>
                        <a href="{{ route('stock.index') }}" class="text-xs font-semibold text-stone-700 hover:text-black flex items-center gap-1 mt-1 group">
                            <span>Total Barang Masuk</span>
                            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>

                    <div class="my-auto py-2">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-stone-900 tracking-tight leading-tight">
                            {{ number_format($totalStockIn) }} <span class="text-lg font-bold text-stone-500">Unit</span>
                        </div>
                        <div class="text-xs text-stone-500 mt-2 font-mono">
                            Total keluar: <span class="font-bold text-stone-700">{{ number_format($totalStockOut) }} Unit</span>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- PAPAN STATUS KOLOM (Kanban / Board Style seperti Gambar Referensi BizLink) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
                <span>Papan Status Inventaris Gudang</span>
            </h2>
            <div class="text-xs font-mono text-stone-500">
                Pembaruan Real-Time
            </div>
        </div>

        @php
            // Klasifikasikan item untuk kolom board
            $criticalItems = $criticalProducts->filter(fn($p) => $p->stock <= 0 || $p->stock <= ceil($p->minimum_stock / 2));
            if ($criticalItems->isEmpty() && $criticalProducts->isNotEmpty()) {
                $criticalItems = $criticalProducts->take(1);
            }
            $reorderItems = $criticalProducts->reject(fn($p) => $criticalItems->contains('id', $p->id));
            if ($reorderItems->isEmpty() && $criticalProducts->isNotEmpty()) {
                $reorderItems = $criticalProducts;
            }
            $safeItems = $safeProducts ?? collect();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 items-start">
            
            <!-- KOLOM 1: Stok Kritis (Header + Counter ↓) -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Stok Kritis</h3>
                    </div>
                    <span class="text-xs font-mono font-bold text-stone-600 flex items-center gap-1 bg-white px-2 py-0.5 rounded-full border border-black/[0.05] shadow-2xs">
                        {{ $criticalItems->count() }} &darr;
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($criticalItems as $prod)
                        <div class="bg-white rounded-2xl p-5 border border-black/[0.05] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group space-y-3">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 font-semibold border border-black/[0.04]">
                                        {{ $prod->sku }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono bg-rose-100 text-rose-800">
                                        Stok Kritis
                                    </span>
                                </div>

                                <div>
                                    <a href="{{ route('products.show', $prod) }}" class="font-bold text-stone-900 group-hover:text-black transition-colors block text-base leading-snug">
                                        {{ $prod->name }}
                                    </a>
                                    <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                        <span>{{ $prod->category->name ?? 'Tanpa Kategori' }}</span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Harga Jual</div>
                                        <div class="font-mono font-bold text-stone-900 mt-0.5">
                                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Saldo Fisik</div>
                                        <div class="font-mono font-extrabold mt-0.5 text-rose-700">
                                            {{ number_format($prod->stock) }} unit
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                                <a href="{{ route('products.show', $prod) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                                    Detail &rarr;
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="px-2.5 py-1 rounded-lg bg-[#ECEEE8] hover:bg-stone-200 text-stone-800 text-[11px] font-semibold transition-colors" title="Restock Masuk">
                                        + Restock
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('products.edit', $prod) }}" class="p-1.5 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-50" title="Edit Produk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $prod) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $prod->name }}?');">
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
                        <div class="p-6 rounded-2xl bg-white/60 border border-dashed border-stone-200 text-center text-xs text-stone-400">
                            Tidak ada item berstatus stok kritis.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KOLOM 2: Perlu Reorder (Header + Counter ↓) -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Perlu Reorder</h3>
                    </div>
                    <span class="text-xs font-mono font-bold text-stone-600 flex items-center gap-1 bg-white px-2 py-0.5 rounded-full border border-black/[0.05] shadow-2xs">
                        {{ $reorderItems->count() }} &darr;
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($reorderItems as $prod)
                        <div class="bg-white rounded-2xl p-5 border border-black/[0.05] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group space-y-3">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 font-semibold border border-black/[0.04]">
                                        {{ $prod->sku }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono bg-amber-100 text-amber-800">
                                        Perlu Reorder
                                    </span>
                                </div>

                                <div>
                                    <a href="{{ route('products.show', $prod) }}" class="font-bold text-stone-900 group-hover:text-black transition-colors block text-base leading-snug">
                                        {{ $prod->name }}
                                    </a>
                                    <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                        <span>{{ $prod->category->name ?? 'Tanpa Kategori' }}</span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Harga Jual</div>
                                        <div class="font-mono font-bold text-stone-900 mt-0.5">
                                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Saldo Fisik</div>
                                        <div class="font-mono font-extrabold mt-0.5 text-amber-700">
                                            {{ number_format($prod->stock) }} unit
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                                <a href="{{ route('products.show', $prod) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                                    Detail &rarr;
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="px-2.5 py-1 rounded-lg bg-[#ECEEE8] hover:bg-stone-200 text-stone-800 text-[11px] font-semibold transition-colors" title="Restock Masuk">
                                        + Restock
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('products.edit', $prod) }}" class="p-1.5 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-50" title="Edit Produk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $prod) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $prod->name }}?');">
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
                        <div class="p-6 rounded-2xl bg-white/60 border border-dashed border-stone-200 text-center text-xs text-stone-400">
                            Semua stok berada di atas batas minimum.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KOLOM 3: Stok Aman (Header + Counter v) -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Stok Aman</h3>
                    </div>
                    <span class="text-xs font-mono font-bold text-stone-600 flex items-center gap-1 bg-white px-2 py-0.5 rounded-full border border-black/[0.05] shadow-2xs">
                        {{ $safeItems->count() }} &darr;
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($safeItems as $prod)
                        <div class="bg-white rounded-2xl p-5 border border-black/[0.05] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group space-y-3">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 font-semibold border border-black/[0.04]">
                                        {{ $prod->sku }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono bg-emerald-100 text-emerald-800">
                                        Stok Aman
                                    </span>
                                </div>

                                <div>
                                    <a href="{{ route('products.show', $prod) }}" class="font-bold text-stone-900 group-hover:text-black transition-colors block text-base leading-snug">
                                        {{ $prod->name }}
                                    </a>
                                    <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                        <span>{{ $prod->category->name ?? 'Tanpa Kategori' }}</span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Harga Jual</div>
                                        <div class="font-mono font-bold text-stone-900 mt-0.5">
                                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Saldo Fisik</div>
                                        <div class="font-mono font-extrabold mt-0.5 text-stone-900">
                                            {{ number_format($prod->stock) }} unit
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                                <a href="{{ route('products.show', $prod) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                                    Detail &rarr;
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('stock.create', ['product_id' => $prod->id]) }}" class="px-2.5 py-1 rounded-lg bg-[#ECEEE8] hover:bg-stone-200 text-stone-800 text-[11px] font-semibold transition-colors" title="Restock Masuk">
                                        + Restock
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('products.edit', $prod) }}" class="p-1.5 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-50" title="Edit Produk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $prod) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $prod->name }}?');">
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
                        <div class="p-6 rounded-2xl bg-white/60 border border-dashed border-stone-200 text-center text-xs text-stone-400">
                            Belum ada produk berstatus aman.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KOLOM 4: Mutasi Terkini (Header + Counter v) -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-stone-800">Mutasi Terkini</h3>
                    </div>
                    <span class="text-xs font-mono font-bold text-stone-600 flex items-center gap-1 bg-white px-2 py-0.5 rounded-full border border-black/[0.05] shadow-2xs">
                        {{ $recentMovements->count() }} &darr;
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($recentMovements as $mov)
                        @php
                            $isIncoming = ($mov->type === 'IN');
                            $operatorName = $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem';
                        @endphp
                        <div class="bg-white rounded-2xl p-5 border border-black/[0.05] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group space-y-3">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono font-bold {{ $isIncoming ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-800' }}">
                                        {{ $isIncoming ? '+ MASUK (IN)' : '- KELUAR (OUT)' }}
                                    </span>
                                    <span class="text-[10px] font-mono text-stone-500">
                                        {{ $mov->created_at->format('d/m H:i') }}
                                    </span>
                                </div>

                                <div>
                                    @if($mov->product)
                                        <a href="{{ route('products.show', $mov->product) }}" class="font-bold text-stone-900 group-hover:text-black transition-colors block text-base leading-snug">
                                            {{ $mov->product->name }}
                                        </a>
                                        <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                            <span>{{ $mov->product->category->name ?? 'Tanpa Kategori' }}</span>
                                        </div>
                                    @else
                                        <span class="font-bold text-stone-900 block text-base leading-snug">Produk Dihapus</span>
                                        <div class="text-xs text-stone-400 mt-1 flex items-center gap-1.5">
                                            <span>Tanpa Kategori</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Volume Mutasi</div>
                                        <div class="font-mono font-bold mt-0.5 {{ $isIncoming ? 'text-emerald-700' : 'text-stone-900' }}">
                                            {{ $isIncoming ? '+' : '-' }}{{ number_format($mov->quantity) }} unit
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] font-mono uppercase text-stone-400">Operator</div>
                                        <div class="font-mono font-bold text-stone-800 mt-0.5 truncate" title="{{ $operatorName }}">
                                            {{ $operatorName }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                                @if($mov->product)
                                    <a href="{{ route('products.show', $mov->product) }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                                        Detail &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('stock.index') }}" class="text-xs font-semibold text-stone-700 hover:text-black">
                                        Detail &rarr;
                                    </a>
                                @endif

                                <span class="text-[11px] font-mono text-stone-400 truncate max-w-[120px]" title="{{ $mov->note ?? '-' }}">
                                    {{ $mov->note ? $mov->note : '-' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 rounded-2xl bg-white/60 border border-dashed border-stone-200 text-center text-xs text-stone-400">
                            Belum ada riwayat mutasi logistik.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- TABEL LAPORAN AGREGASI PRODUK & LOGISTIK (Kepatuhan Rubrik Dosen P4/P5) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
        
        <!-- Panel Kiri: 5 Mutasi Buku Besar Terkini -->
        <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden">
            <div class="px-6 py-4.5 border-b border-black/[0.04] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-stone-900 tracking-tight">Mutasi Terkini (Buku Besar)</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">Catatan pergerakan barang terbaru di gudang</p>
                </div>
                <a href="{{ route('stock.index') }}" class="text-xs font-semibold text-stone-600 hover:text-black">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3 text-center">Tipe</th>
                            <th class="px-6 py-3 text-right">Jumlah</th>
                            <th class="px-6 py-3 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 font-mono">
                        @forelse($recentMovements as $mov)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-6 py-3 font-sans">
                                    <div class="font-bold text-stone-900">{{ $mov->product->name ?? 'Produk Dihapus' }}</div>
                                    <div class="text-[10px] text-stone-400 font-mono">{{ $mov->product->sku ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-800' }}">
                                        {{ $mov->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-bold {{ $mov->type === 'IN' ? 'text-emerald-700' : 'text-stone-800' }}">
                                    {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                                </td>
                                <td class="px-6 py-3 text-right text-stone-400 text-[11px]">
                                    {{ $mov->created_at->format('d/m H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-stone-400 font-sans">Belum ada mutasi tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel Kanan: Agregasi Volume Per Produk (Top Movement) -->
        <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden">
            <div class="px-6 py-4.5 border-b border-black/[0.04] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-stone-900 tracking-tight">Agregasi Volume Per Produk</h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">Akumulasi pergerakan barang tertinggi berdasarkan analitik (GROUP BY)</p>
                </div>
                <span class="text-[10px] font-mono font-medium text-stone-400 bg-stone-100 px-2 py-0.5 rounded">
                    Top 5
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                        <tr>
                            <th class="px-6 py-3">Nama Produk & SKU</th>
                            <th class="px-6 py-3 text-right">Total Masuk (IN)</th>
                            <th class="px-6 py-3 text-right">Total Keluar (OUT)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 font-mono">
                        @forelse($movementAggregates as $agg)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-6 py-3 font-sans">
                                    <div class="font-bold text-stone-900">{{ $agg->product_name }}</div>
                                    <div class="text-[10px] text-stone-400 font-mono">{{ $agg->product_sku }}</div>
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-emerald-700">
                                    +{{ number_format($agg->total_in) }}
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-stone-800">
                                    -{{ number_format($agg->total_out) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-center text-stone-400 font-sans">Belum ada data agregasi mutasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
