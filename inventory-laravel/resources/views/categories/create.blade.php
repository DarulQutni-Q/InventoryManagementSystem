@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
        <div>
            <div class="text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-widest mb-1">
                Kategori Inventaris
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Kategori Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Buat pengelompokan jenis barang inventaris baru.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('categories.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] p-6 sm:p-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Nama Kategori <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors"
                placeholder="Contoh: Elektronik & Komputer">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('categories.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold transition-all hover:scale-[1.02] active:scale-[0.98] shadow-sm">
                Simpan Kategori
            </button>
        </div>
    </form>
</div>
@endsection
