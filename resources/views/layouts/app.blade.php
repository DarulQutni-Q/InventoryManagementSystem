<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Inventoria' }} — Sistem Manajemen Inventaris</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full text-slate-800 antialiased font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Top Navbar Fixed -->
        <header class="fixed top-0 left-0 right-0 h-14 bg-slate-900 border-b border-slate-800 z-30 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button -->
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 text-slate-400 hover:text-white rounded-md hover:bg-slate-800 focus:outline-none" aria-label="Toggle Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <!-- Logo & Brand Name -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white tracking-tight text-lg">Inventoria</span>
                </a>
            </div>

            <!-- Navbar Right: User & Actions -->
            <div class="flex items-center gap-3">
                @auth
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-semibold text-slate-100">{{ Auth::user()->profile->full_name ?? Auth::user()->name ?? Auth::user()->username }}</span>
                            <span class="text-[11px] text-slate-400">{{ Auth::user()->isAdmin() ? 'Administrator' : 'Staff Gudang' }}</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ Auth::user()->isAdmin() ? 'bg-indigo-900/60 text-indigo-200 border border-indigo-700/50' : 'bg-slate-800 text-slate-300 border border-slate-700/60' }}">
                            {{ Auth::user()->isAdmin() ? 'Admin' : 'Staff' }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded transition-colors" title="Keluar dari Sistem">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <!-- Sidebar Fixed -->
        <aside id="appSidebar" class="fixed top-14 left-0 bottom-0 w-64 bg-slate-900 border-r border-slate-800 z-20 overflow-y-auto flex flex-col justify-between transition-transform duration-200 -translate-x-full lg:translate-x-0">
            <div class="p-4 space-y-6">
                <!-- Navigation Links -->
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Menu Utama</div>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Katalog Produk
                        </a>

                        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            Kategori Barang
                        </a>

                        <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Mitra Supplier
                        </a>
                    </nav>
                </div>

                <!-- Stock Mutations -->
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Transaksi Logistik</div>
                    <nav class="space-y-1">
                        <a href="{{ route('stock.create') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('stock.create') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Barang Masuk (IN)
                        </a>

                        <a href="{{ route('stock.createOut') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('stock.createOut') ? 'bg-amber-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Barang Keluar (OUT)
                        </a>

                        <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('stock.index') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Riwayat Mutasi
                        </a>
                    </nav>
                </div>

                @if(Auth::check() && Auth::user()->isAdmin())
                <!-- Administration -->
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Administrasi</div>
                    <nav class="space-y-1">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Kelola Pengguna
                        </a>
                    </nav>
                </div>
                @endif
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
                <div class="font-medium text-slate-300">Inventoria</div>
                <div class="text-[11px] text-slate-500">Sistem Inventaris Gudang</div>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 z-10 hidden lg:hidden"></div>

        <!-- Main Content Container (lg:pl-64 pt-14) -->
        <main class="flex-1 lg:pl-64 pt-14 min-h-screen bg-slate-50 flex flex-col">
            <div class="p-6 lg:p-8 flex-1 max-w-7xl w-full mx-auto">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-6 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 flex items-start gap-3 shadow-sm">
                        <svg class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('info') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 shadow-sm">
                        <div class="flex items-center gap-2 font-semibold text-sm mb-1 text-amber-900">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Terdapat beberapa kesalahan input:
                        </div>
                        <ul class="list-disc list-inside text-sm space-y-1 text-amber-800 pl-6">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>

            <footer class="border-t border-slate-200 bg-white py-4 px-6 text-center text-xs text-slate-500 mt-auto">
                &copy; {{ date('Y') }} Inventoria Management System &mdash; Sistem Manajemen Inventaris Gudang
            </footer>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>
</body>
</html>
