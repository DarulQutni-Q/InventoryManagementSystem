<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Inventoria' }} — Sistem Manajemen Inventaris & Logistik</title>

    <!-- Google Fonts: Geist, Geist Mono, JetBrains Mono, Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Design System Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Geist"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"Geist Mono"', '"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        obsidian: '#0c0d0e',
                        surface: '#111214',
                        brand: {
                            DEFAULT: '#5e6ad2',
                            hover: '#4f5ac4',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Linear / Vercel Typography & Tabular Numerals */
        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11", "liga";
            letter-spacing: -0.011em;
        }
        .font-mono {
            font-feature-settings: "tnum" 1, "zero" 1;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.015em;
        }
    </style>
</head>
<body class="h-full bg-[#FAFAFA] text-zinc-900 antialiased font-sans flex flex-col">

    <!-- Fixed Top Navbar (h-14) — Obsidian Precision -->
    <header class="fixed top-0 left-0 right-0 h-14 bg-[#0c0d0e] border-b border-white/[0.08] z-30 flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-3.5">
            <!-- Mobile Sidebar Toggle -->
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 text-zinc-400 hover:text-white rounded-md hover:bg-white/[0.08] transition-colors focus:outline-none" aria-label="Buka Menu Navigasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Brand Logo with Precise Geometric Mark -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-[#5e6ad2] flex items-center justify-center text-white ring-1 ring-white/20 group-hover:bg-[#4f5ac4] transition-colors shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm tracking-tight text-white">Inventoria</span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.5 text-zinc-400 bg-white/[0.06] border border-white/[0.08] rounded">P5 Enterprise</span>
                </div>
            </a>
        </div>

        <!-- Top Right: Active User Profile & Quick Actions -->
        @auth
            @php
                $fullName = Auth::user()->profile->full_name ?? Auth::user()->name ?? Auth::user()->username;
                $parts = explode(' ', trim($fullName));
                $initials = count($parts) >= 2
                    ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                    : strtoupper(substr($fullName, 0, 2));
                $isAdmin = Auth::user()->isAdmin();
            @endphp
            <div class="flex items-center gap-3">
                <!-- User Profile Pill -->
                <div class="flex items-center gap-2.5 pl-3 pr-2 py-1 rounded-full bg-white/[0.04] border border-white/[0.08]">
                    <div class="flex flex-col text-right">
                        <span class="text-xs font-medium text-white leading-tight max-w-[140px] sm:max-w-xs truncate">{{ $fullName }}</span>
                        <span class="text-[10px] font-mono tracking-wider {{ $isAdmin ? 'text-[#828fff]' : 'text-zinc-400' }}">
                            {{ $isAdmin ? 'ADMINISTRATOR' : 'STAFF GUDANG' }}
                        </span>
                    </div>
                    <div class="w-7 h-7 rounded-full bg-white/[0.1] text-white font-mono text-[11px] font-semibold flex items-center justify-center ring-1 ring-white/10">
                        {{ $initials }}
                    </div>
                </div>

                <!-- Logout Form Button -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-zinc-400 hover:text-rose-400 rounded-lg hover:bg-white/[0.06] transition-colors focus:outline-none" title="Keluar dari Sistem">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endauth
    </header>

    <!-- Fixed Sidebar (Fixed position from top-14 to bottom-0, w-64, Obsidian Precision) -->
    <aside id="appSidebar" class="fixed top-14 left-0 bottom-0 w-64 bg-[#0c0d0e] border-r border-white/[0.08] z-20 overflow-y-auto flex flex-col justify-between transition-transform duration-200 -translate-x-full lg:translate-x-0">
        <div class="p-4 space-y-6">
            <!-- Navigation Section: Master & Ringkasan -->
            <div>
                <div class="text-[10px] font-mono font-medium text-zinc-500 uppercase tracking-wider px-3 mb-2">Navigasi Utama</div>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('products.*') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Katalog Produk</span>
                    </a>

                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('categories.*') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Kategori Barang</span>
                    </a>

                    <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('suppliers.*') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Mitra Supplier</span>
                    </a>
                </nav>
            </div>

            <!-- Navigation Section: Transaksi Mutasi Logistik -->
            <div>
                <div class="text-[10px] font-mono font-medium text-zinc-500 uppercase tracking-wider px-3 mb-2">Operasional Gudang</div>
                <nav class="space-y-1">
                    <a href="{{ route('stock.create') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('stock.create') ? 'bg-white/[0.08] text-emerald-400 border-l-2 border-emerald-500 pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Barang Masuk (IN)</span>
                    </a>

                    <a href="{{ route('stock.createOut') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('stock.createOut') ? 'bg-white/[0.08] text-zinc-300 border-l-2 border-zinc-500 pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                        <span>Barang Keluar (OUT)</span>
                    </a>

                    <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('stock.index') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Riwayat Mutasi</span>
                    </a>
                </nav>
            </div>

            <!-- Navigation Section: Kontrol Akses (Khusus Admin) -->
            @if(Auth::check() && Auth::user()->isAdmin())
                <div>
                    <div class="text-[10px] font-mono font-medium text-zinc-500 uppercase tracking-wider px-3 mb-2">Manajemen Sistem</div>
                    <nav class="space-y-1">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-all {{ request()->routeIs('users.*') ? 'bg-white/[0.08] text-white border-l-2 border-[#5e6ad2] pl-2.5 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.04)]' : 'text-zinc-400 hover:text-white hover:bg-white/[0.04]' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Kelola Pengguna</span>
                        </a>
                    </nav>
                </div>
            @endif
        </div>
    </aside>

    <!-- Mobile Drawer Backdrop -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 z-10 hidden lg:hidden"></div>

    <!-- Main Content Canvas (lg:pl-64 offset from fixed sidebar, pt-14 offset from fixed header) -->
    <main class="lg:pl-64 pt-14 min-h-screen flex flex-col flex-1">
        <div class="p-6 sm:p-8 lg:p-10 max-w-7xl w-full mx-auto flex-1 space-y-6">

            <!-- Flash Alert Messages (Hairline, Clean, Non-Slop) -->
            @if(session('success'))
                <div class="p-3.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 text-xs sm:text-sm flex items-start gap-3">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-900 text-xs sm:text-sm flex items-start gap-3">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('error') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3.5 rounded-lg bg-zinc-100 border border-zinc-200/80 text-zinc-800 text-xs sm:text-sm flex items-start gap-3">
                    <svg class="w-4 h-4 text-zinc-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('info') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-900 text-xs sm:text-sm">
                    <div class="font-semibold text-xs uppercase tracking-wider mb-2 flex items-center gap-2 text-rose-800 font-mono">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Terdapat kesalahan validasi data:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Page Section Slot -->
            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <!-- System Footer -->
        <footer class="border-t border-zinc-200/80 bg-white py-4 px-6 text-center text-xs text-zinc-400 font-mono mt-auto">
            <span>Inventoria P5 Enterprise &middot; Praktikum Pemrograman Web &middot; {{ date('Y') }}</span>
        </footer>
    </main>

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
