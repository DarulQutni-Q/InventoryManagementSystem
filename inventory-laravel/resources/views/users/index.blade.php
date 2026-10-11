@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-400 font-mono mb-1.5">
                <span>Administrasi</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700 font-medium">Pengguna</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola akun dan otorisasi hak akses sistem (Role-Based Access Control).</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-medium transition-all shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    <!-- Data Table Ledger -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 text-slate-500 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6 w-16">ID</th>
                        <th class="py-3.5 px-6">Nama Lengkap (Profil)</th>
                        <th class="py-3.5 px-6">Username</th>
                        <th class="py-3.5 px-6">Email</th>
                        <th class="py-3.5 px-6 text-center">Peran (Role)</th>
                        <th class="py-3.5 px-6">Terdaftar Sejak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-6 text-xs font-mono text-slate-400">
                                #{{ $u->id }}
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                {{ $u->profile->full_name ?? $u->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 font-mono text-xs text-slate-700">
                                {{ $u->username }}
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs">
                                {{ $u->email ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider {{ $u->role === 'admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60' : 'bg-slate-100 text-slate-700 border border-slate-200/60' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-500 font-mono whitespace-nowrap">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-sm">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200/80">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
