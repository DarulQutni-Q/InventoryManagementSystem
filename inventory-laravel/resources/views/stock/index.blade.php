@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Logistik</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Buku Besar</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Mutasi Stok</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Riwayat Mutasi Stok</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Buku besar audit seluruh pergerakan barang masuk dan keluar gudang.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Barang Masuk (IN)</span>
            </a>
            <a href="{{ route('stock.createOut') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-stone-50 text-stone-800 text-xs sm:text-sm font-semibold transition-all border border-black/[0.06] shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                </svg>
                <span>Barang Keluar (OUT)</span>
            </a>
        </div>
    </div>

    <!-- Filter Multi-Dimensi Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-black/[0.05] shadow-2xs">
        <form method="GET" action="{{ route('stock.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Filter Produk -->
            <div>
                <label for="product_id" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Produk Barang
                </label>
                <select id="product_id" name="product_id" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tipe -->
            <div>
                <label for="type" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Tipe Mutasi
                </label>
                <select id="type" name="type" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                    <option value="">Semua Tipe</option>
                    <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                    <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div>
                <label for="date_from" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Dari Tanggal
                </label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 font-mono transition-colors">
            </div>

            <!-- Sampai Tanggal -->
            <div>
                <label for="date_to" class="block text-[11px] font-mono font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                    Sampai Tanggal
                </label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 font-mono transition-colors">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-colors">
                    Terapkan
                </button>
                @if(request()->anyFilled(['product_id', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('stock.index') }}" class="px-3 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs sm:text-sm font-medium transition-colors" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Ledger -->
    <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                    <tr>
                        <th class="py-3.5 px-6">Waktu Transaksi</th>
                        <th class="py-3.5 px-6">SKU & Produk</th>
                        <th class="py-3.5 px-6 text-center">Tipe</th>
                        <th class="py-3.5 px-6 text-right">Jumlah Unit</th>
                        <th class="py-3.5 px-6">Petugas</th>
                        <th class="py-3.5 px-6">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-stone-50/70 transition-colors">
                            <td class="py-3.5 px-6 font-mono text-xs text-stone-400 whitespace-nowrap">
                                {{ $mov->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="font-bold text-stone-900">{{ $mov->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="font-mono text-xs text-stone-400 mt-0.5">{{ $mov->product->sku ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-800' }}">
                                    {{ $mov->type === 'IN' ? 'MASUK' : 'KELUAR' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right font-mono font-bold whitespace-nowrap text-sm {{ $mov->type === 'IN' ? 'text-emerald-700' : 'text-stone-900' }}">
                                {{ $mov->type === 'IN' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                            </td>
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="text-stone-800 font-medium">{{ $mov->user->profile->full_name ?? $mov->user->username ?? 'Sistem' }}</span>
                            </td>
                            <td class="py-3.5 px-6 text-stone-500">
                                {{ $mov->note ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-stone-400">
                                Tidak ada data mutasi yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-black/[0.04]">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
