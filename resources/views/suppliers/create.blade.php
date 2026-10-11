@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Supplier Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan mitra vendor pemasok logistik baru.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('suppliers.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Nama Supplier / Perusahaan <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Contoh: PT Teknologi Maju Bersama">
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Informasi Kontak / Telepon / Email
            </label>
            <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('contact') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Contoh: 0812-3456-7890 / sales@vendor.co.id">
            @error('contact')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors shadow-sm">
                Simpan Supplier
            </button>
        </div>
    </form>
</div>
@endsection
