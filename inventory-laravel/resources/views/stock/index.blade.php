@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Mutasi Stok</h1>
            <p class="text-sm text-slate-500 mt-0.5">Buku besar audit seluruh pergerakan barang masuk dan keluar gudang.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Barang Masuk (IN)
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
                Barang Keluar (OUT)
            </a>
        </div>
    </div>

    <!-- Filter Multi-Dimensi Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('stock.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Filter Produk -->
            <div>
                <label for="product_id" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Produk</label>
                <select id="product_id" name="product_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tipe -->
            <div>
                <label for="type" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tipe Mutasi</label>
                <select id="type" name="type" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                    <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div>
                <label for="date_from" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Sampai Tanggal -->
            <div>
                <label for="date_to" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Action -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium transition-colors">
                    Terapkan
                </button>
                @if(request()->anyFilled(['product_id', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('stock.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Waktu Transaksi</th>
                        <th class="px-6 py-3.5">SKU & Produk</th>
                        <th class="px-6 py-3.5 text-center">Tipe</th>
                        <th class="px-6 py-3.5 text-right">Jumlah Unit</th>
                        <th class="px-6 py-3.5">Petugas</th>
                        <th class="px-6 py-3.5">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                                {{ $mov->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $mov->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="font-mono text-xs text-slate-500">{{ $mov->product->sku ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $mov->type === 'IN' ? 'MASUK' : 'KELUAR' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $mov->type === 'IN' ? 'text-emerald-600' : 'text-amber-600' }} whitespace-nowrap">
                                {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                            </td>
                            <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                                <span class="font-medium text-xs">{{ $mov->user->profile->full_name ?? $mov->user->name ?? $mov->user->username ?? 'Sistem' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                {{ $mov->note ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="font-medium text-slate-700">Tidak ada riwayat mutasi stok ditemukan.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Silakan lakukan pencatatan barang masuk atau keluar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
