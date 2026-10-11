@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-400 font-mono mb-1.5">
                <span>Master Data</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700 font-medium">Supplier</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Mitra Supplier</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data vendor dan rekanan pemasok stok barang gudang.</p>
        </div>

        @if(Auth::user()->isAdmin())
            <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-medium transition-all shadow-sm self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Supplier</span>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200/80 text-slate-600 text-xs font-mono self-start sm:self-auto">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Katalog (Mode Baca)</span>
            </span>
        @endif
    </div>

    <!-- Search Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)]">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-xs sm:text-sm text-slate-800 transition-colors"
                    placeholder="Cari nama atau kontak supplier...">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-semibold transition-colors">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-medium">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table Ledger -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6 w-16">No</th>
                        <th class="py-3.5 px-6">Nama Vendor / Perusahaan</th>
                        <th class="py-3.5 px-6">Informasi Kontak</th>
                        <th class="py-3.5 px-6 text-center">Produk Disuplai</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $index => $supplier)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-6 text-xs font-mono text-slate-400">
                                {{ $suppliers->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                {{ $supplier->name }}
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs sm:text-sm">
                                {{ $supplier->contact ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $supplier->products_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $supplier->products_count }} item
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                @if(Auth::user()->isAdmin())
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50/50 transition-colors shadow-xs" title="Edit Supplier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50/50 transition-colors shadow-xs" title="Hapus Supplier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-mono">Mode Baca</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">
                                Tidak ada data supplier yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200/80">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
