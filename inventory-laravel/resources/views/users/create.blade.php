@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
        <div>
            <div class="text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-widest mb-1">
                Administrasi Sistem
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Pengguna Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftarkan akun petugas baru dengan kontrol peran RBAC.</p>
        </div>
        <a href="{{ route('users.index') }}" class="px-3.5 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)] p-6 sm:p-8 space-y-5">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Username Akun <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-lg border @error('username') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono text-slate-800 transition-colors"
                placeholder="Contoh: staff_gudang02">
            @error('username')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Lengkap -->
        <div>
            <label for="full_name" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Nama Lengkap Petugas <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('full_name') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors"
                placeholder="Contoh: Budi Santoso">
            @error('full_name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Alamat Email (Opsional)
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors"
                placeholder="petugas@inventoria.local">
            @error('email')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role -->
        <div>
            <label for="role" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Peran Pengguna (Role) <span class="text-rose-500">*</span>
            </label>
            <select id="role" name="role" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('role') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm text-slate-800 transition-colors">
                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff Gudang (Operasional & Transaksi Stok)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh Master Data & Akun)</option>
            </select>
            @error('role')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-[11px] font-mono font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <input type="password" id="password" name="password" required
                class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-mono text-slate-800 transition-colors"
                placeholder="Minimal 6 karakter">
            @error('password')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-sm">
                Simpan Akun
            </button>
        </div>
    </form>
</div>
@endsection
