@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Pengguna Baru</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan akun petugas baru dengan kontrol peran RBAC.</p>
        </div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Username Akun <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('username') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono"
                placeholder="Contoh: staff_gudang02">
            @error('username')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="full_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Nama Lengkap Petugas <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('full_name') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Contoh: Budi Santoso">
            @error('full_name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Alamat Email (Opsional)
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('email') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="petugas@inventoria.local">
            @error('email')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Peran Pengguna (Role) <span class="text-rose-500">*</span>
            </label>
            <select id="role" name="role" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('role') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff Gudang (Operasional & Transaksi Stok)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh Master Data & Akun)</option>
            </select>
            @error('role')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <input type="password" id="password" name="password" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-rose-400 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                placeholder="Minimal 6 karakter">
            @error('password')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors shadow-sm">
                Simpan Akun
            </button>
        </div>
    </form>
</div>
@endsection
