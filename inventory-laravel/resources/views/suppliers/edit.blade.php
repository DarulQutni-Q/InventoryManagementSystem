@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
        <div>
            <div class="text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-widest mb-1">
                Mitra Supplier
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Supplier</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Perbarui profil dan kontak mitra supplier.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)] p-6 sm:p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Nama Supplier / Perusahaan <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Informasi Kontak / Telepon / Email
            </label>
            <input type="text" id="contact" name="contact" value="{{ old('contact', $supplier->contact) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('contact') border-rose-400 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors">
            @error('contact')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-sm">
                Perbarui Supplier
            </button>
        </div>
    </form>
</div>
@endsection
