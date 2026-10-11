@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola akun dan otorisasi hak akses sistem (Role-Based Access Control).</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Tambah Pengguna
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 w-16">ID</th>
                        <th class="px-6 py-3.5">Nama Lengkap (Profil)</th>
                        <th class="px-6 py-3.5">Username</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5 text-center">Peran (Role)</th>
                        <th class="px-6 py-3.5">Terdaftar Sejak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                #{{ $u->id }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $u->profile->full_name ?? $u->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">
                                {{ $u->username }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                {{ $u->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
