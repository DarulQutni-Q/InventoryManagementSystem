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
                <span class="text-stone-900 font-semibold">Barang Keluar</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Pencatatan Barang Keluar (OUT)</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Pengurangan saldo persediaan untuk pengiriman atau penggunaan internal.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="px-3.5 py-2 rounded-full bg-white border border-black/[0.06] hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors shadow-2xs">
            &larr; Riwayat Mutasi
        </a>
    </div>

    <!-- Form Container (White Rounded-2xl Card) -->
    <form action="{{ route('stock.store') }}" method="POST" id="stockOutForm" class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs p-6 sm:p-8 space-y-5">
        @csrf
        <input type="hidden" name="type" value="OUT">

        <!-- Banner Info Pessimistic Locking & Proteksi Stok Negatif (20px Lock Icon) -->
        <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200/60 text-amber-950 text-xs flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-700 flex-shrink-0" style="width: 20px; height: 20px; min-width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div class="leading-relaxed">
                <span class="font-bold">Pessimistic Locking Aktif (SELECT ... FOR UPDATE):</span>
                Baris produk dikunci selama transaksi. Validasi stok non-negatif memastikan saldo tidak pernah bernilai minus.
            </div>
        </div>

        <!-- Pilih Produk -->
        <div>
            <label for="product_id" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Pilih Produk <span class="text-rose-500">*</span>
            </label>
            <select id="product_id" name="product_id" required autofocus onchange="updateStockAvailability()"
                class="w-full px-3.5 py-2.5 rounded-xl border @error('product_id') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors">
                <option value="">Pilih barang yang tersedia...</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" data-stock="{{ $prod->stock }}" {{ (string) old('product_id', request('product_id')) === (string) $prod->id ? 'selected' : '' }}>
                        {{ $prod->name }} (SKU: {{ $prod->sku }}) &mdash; Tersedia: {{ $prod->stock }} unit
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror

            <!-- Real-time Stock Display Card -->
            <div id="availableStockCard" class="mt-2.5 p-3 rounded-xl bg-stone-50 border border-stone-200/80 text-xs flex items-center justify-between hidden">
                <span class="text-stone-500 font-medium">Stok Fisik Tersedia:</span>
                <span id="availableStockVal" class="font-mono font-bold text-stone-900">0 unit</span>
            </div>
        </div>

        <!-- Jumlah Barang Keluar -->
        <div>
            <label for="quantity" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Jumlah Unit Keluar <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" step="1" required oninput="validateStockQuantity()"
                class="w-full px-3.5 py-2.5 rounded-xl border @error('quantity') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm font-mono text-stone-800 transition-colors"
                placeholder="Jumlah unit keluar">
            <div id="stockExceededWarning" class="hidden mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Jumlah melebihi saldo stok yang tersedia di rak penyimpanan!</span>
            </div>
            @error('quantity')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan / Tujuan Distribusi -->
        <div>
            <label for="note" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Keterangan / Tujuan Distribusi / No. Delivery Order
            </label>
            <textarea id="note" name="note" rows="3"
                class="w-full px-3.5 py-2.5 rounded-xl border @error('note') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-xs sm:text-sm text-stone-800 transition-colors"
                placeholder="Contoh: Pengiriman ke Divisi IT lantai 3">{{ old('note') }}</textarea>
            @error('note')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 border-t border-stone-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('stock.index') }}" class="px-4 py-2 rounded-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-5 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                Catat Barang Keluar
            </button>
        </div>
    </form>
</div>

<script>
    function updateStockAvailability() {
        const select = document.getElementById('product_id');
        const selectedOption = select.options[select.selectedIndex];
        const card = document.getElementById('availableStockCard');
        const valSpan = document.getElementById('availableStockVal');

        if (selectedOption && selectedOption.dataset.stock !== undefined) {
            const stock = parseInt(selectedOption.dataset.stock, 10);
            valSpan.textContent = stock + ' unit';
            card.classList.remove('hidden');
            validateStockQuantity();
        } else {
            card.classList.add('hidden');
        }
    }

    function validateStockQuantity() {
        const select = document.getElementById('product_id');
        const selectedOption = select.options[select.selectedIndex];
        const qtyInput = document.getElementById('quantity');
        const warning = document.getElementById('stockExceededWarning');

        if (selectedOption && selectedOption.dataset.stock !== undefined) {
            const maxStock = parseInt(selectedOption.dataset.stock, 10);
            const enteredQty = parseInt(qtyInput.value, 10) || 0;

            if (enteredQty > maxStock) {
                warning.classList.remove('hidden');
                qtyInput.classList.add('border-rose-400', 'bg-rose-50/30');
            } else {
                warning.classList.add('hidden');
                qtyInput.classList.remove('border-rose-400', 'bg-rose-50/30');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateStockAvailability();
    });
</script>
@endsection
