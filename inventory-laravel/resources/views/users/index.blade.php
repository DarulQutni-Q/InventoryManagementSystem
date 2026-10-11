@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-black/[0.04]">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-stone-400 font-mono mb-1">
                <span>Administrasi</span>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-semibold">Pengguna</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Kelola akun dan otorisasi hak akses sistem (Role-Based Access Control).</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    <!-- Data Table Ledger -->
    <div class="bg-white rounded-2xl border border-black/[0.05] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-mono text-[10px] uppercase tracking-wider border-b border-black/[0.04]">
                    <tr>
                        <th class="py-3.5 px-6 w-16">ID</th>
                        <th class="py-3.5 px-6">Nama Lengkap (Profil)</th>
                        <th class="py-3.5 px-6">Username</th>
                        <th class="py-3.5 px-6">Email</th>
                        <th class="py-3.5 px-6 text-center">Peran (Role)</th>
                        <th class="py-3.5 px-6">Terdaftar Sejak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-stone-50/70 transition-colors">
                            <td class="py-3.5 px-6 text-xs font-mono text-stone-400">
                                #{{ $u->id }}
                            </td>
                            <td class="py-3.5 px-6 font-bold text-stone-900 text-sm">
                                {{ $u->profile->full_name ?? $u->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 font-mono text-xs text-stone-700">
                                {{ $u->username }}
                            </td>
                            <td class="py-3.5 px-6 text-stone-500">
                                {{ $u->email ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider {{ $u->role === 'admin' ? 'bg-stone-200 text-stone-900' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-stone-400 font-mono whitespace-nowrap">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-stone-400">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-black/[0.04]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
