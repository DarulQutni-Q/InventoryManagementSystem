@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Master Data</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-700 font-medium">Supplier</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Tambah</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Tambah Supplier Baru</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Daftarkan mitra vendor pemasok logistik baru.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="px-3.5 py-2 rounded-full bg-white border border-black/[0.06] hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors shadow-2xs">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('suppliers.store') }}" method="POST" class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs p-6 sm:p-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Nama Supplier / Perusahaan <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm text-stone-800 transition-colors"
                placeholder="Contoh: PT Teknologi Maju Bersama">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                Informasi Kontak / Telepon / Email
            </label>
            <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border @error('contact') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-sm text-stone-800 transition-colors"
                placeholder="Contoh: 0812-3456-7890 / sales@vendor.co.id">
            @error('contact')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-stone-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 rounded-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                Simpan Supplier
            </button>
        </div>
    </form>
</div>
@endsection
