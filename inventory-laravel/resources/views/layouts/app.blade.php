<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F5F6F1]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Inventoria' }} — Sistem Manajemen Inventaris & Logistik</title>

    <!-- Google Fonts: Plus Jakarta Sans, Geist, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Geist', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Geist Mono', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        canvas: '#F5F6F1',
                        cream: '#EEF0E9',
                        'cream-light': '#F7F8F4',
                        'cream-active': '#ECEEE8',
                        charcoal: '#1C1D1F',
                        pitch: '#18181B',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            letter-spacing: -0.012em;
        }
        .font-mono {
            font-feature-settings: "tnum" 1, "zero" 1;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="h-full bg-[#F5F6F1] text-stone-800 antialiased font-sans flex">

    <!-- Fixed Left Sidebar (Pure White #FFFFFF, Hairline Separator) -->
    <aside id="appSidebar" class="fixed inset-y-0 left-0 w-64 bg-white border-r border-black/[0.06] z-30 flex flex-col justify-between transition-transform duration-200 -translate-x-full lg:translate-x-0">
        
        <!-- Top Section: Brand & Navigations -->
        <div class="flex flex-col flex-1 overflow-y-auto px-5 py-6 space-y-7">
            
            <!-- Brand Logo (Modern Bold Typography, No Emoji) -->
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-xl bg-black text-white flex items-center justify-center font-bold text-sm tracking-tighter">
                        IV
                    </div>
                    <div>
                        <span class="text-base font-extrabold text-stone-900 tracking-tight block leading-tight group-hover:text-black transition-colors">Inventoria</span>
                        <span class="text-[10px] font-mono font-medium text-stone-400 uppercase tracking-wider block">Enterprise Hub</span>
                    </div>
                </a>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigasi Utama -->
            <div>
                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Aktivitas Mutasi -->
                    <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('stock.index') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('stock.index') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Aktivitas Mutasi</span>
                    </a>

                    <!-- Katalog Produk -->
                    <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('products.*') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Katalog Produk</span>
                    </a>

                    <!-- Kategori -->
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('categories.*') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('categories.*') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Kategori</span>
                    </a>

                    <!-- Supplier -->
                    <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('suppliers.*') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Supplier</span>
                    </a>
                </nav>
            </div>

            <!-- Bagian Kedua: Operasional Gudang -->
            <div>
                <div class="text-[11px] font-mono font-medium text-stone-400 uppercase tracking-wider px-3.5 mb-2">Operasional Gudang</div>
                <nav class="space-y-1">
                    <!-- Barang Masuk -->
                    <a href="{{ route('stock.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('stock.create') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('stock.create') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Barang Masuk (IN)</span>
                    </a>

                    <!-- Barang Keluar -->
                    <a href="{{ route('stock.createOut') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('stock.createOut') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('stock.createOut') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                        <span>Barang Keluar (OUT)</span>
                    </a>

                    <!-- Riwayat -->
                    <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('stock.index') && !request()->routeIs('stock.create*') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('stock.index') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Riwayat Mutasi</span>
                    </a>
                </nav>
            </div>

            <!-- Kontrol Administrator (Jika Admin) -->
            @if(Auth::check() && Auth::user()->isAdmin())
                <div>
                    <div class="text-[11px] font-mono font-medium text-stone-400 uppercase tracking-wider px-3.5 mb-2">Manajemen Sistem</div>
                    <nav class="space-y-1">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-xs font-medium rounded-xl transition-all {{ request()->routeIs('users.*') ? 'bg-[#ECEEE8] text-stone-900 font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#F5F6F1]' }}">
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-stone-900' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Kelola Pengguna</span>
                        </a>
                    </nav>
                </div>
            @endif

        </div>

        <!-- Bagian Bawah: Pengguna Sistem (Profile Widget & Logout) -->
        @auth
            @php
                $fullName = Auth::user()->profile->full_name ?? Auth::user()->name ?? Auth::user()->username;
                $parts = explode(' ', trim($fullName));
                $initials = count($parts) >= 2
                    ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                    : strtoupper(substr($fullName, 0, 2));
                $isAdmin = Auth::user()->isAdmin();
            @endphp
            <div class="p-4 border-t border-black/[0.06] bg-stone-50/50">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#18181B] text-white flex items-center justify-center font-bold text-xs flex-shrink-0 tracking-tight">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-stone-900 truncate leading-tight">{{ $fullName }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-block text-[10px] font-mono font-medium px-1.5 py-0.2 rounded {{ $isAdmin ? 'bg-stone-200 text-stone-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $isAdmin ? 'Admin' : 'Staff' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" title="Keluar dari Sistem" class="p-2 text-stone-400 hover:text-stone-900 hover:bg-stone-200/60 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endauth

    </aside>

    <!-- Mobile Drawer Backdrop -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/40 backdrop-blur-xs z-20 hidden lg:hidden"></div>

    <!-- Main Application Wrapper (Canvas Seamless #F5F6F1) -->
    <div class="lg:pl-64 flex-1 flex flex-col min-h-screen bg-[#F5F6F1]">
        
        <!-- Top Header (Seamlessly Integrated with Canvas #F5F6F1) -->
        <header class="sticky top-0 z-20 bg-[#F5F6F1]/90 backdrop-blur-md px-6 sm:px-8 lg:px-10 py-4 flex items-center justify-between gap-4 border-b border-black/[0.04]">
            
            <!-- Left: Mobile Menu Toggle & Search Bar -->
            <div class="flex items-center gap-3.5 flex-1 max-w-lg">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl bg-white border border-black/[0.06] text-stone-600 hover:text-stone-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Search Input Form -->
                <form action="{{ route('products.index') }}" method="GET" class="relative w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search produk, SKU, kategori..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-black/[0.06] text-xs sm:text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-black/20 focus:border-stone-400 shadow-2xs transition-all">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>
            </div>

            <!-- Right Controls: Filter/Sort Indicators & Action Pills -->
            <div class="flex items-center gap-2.5">
                
                <!-- Quick Info Pill 'Me' -->
                @auth
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-black/[0.06] text-xs font-medium text-stone-700 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ Auth::user()->role === 'admin' ? 'Administrator' : 'Staff Operasional' }}</span>
                    </div>
                @endauth

                <!-- Action Button: + Catat Masuk (Pill Hitam #18181B) -->
                <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#18181B] hover:bg-black text-white text-xs sm:text-sm font-semibold transition-all shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Catat Masuk</span>
                </a>

                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Action Button: + Tambah Produk (Pill Cream #EEF0E9 / Charcoal) -->
                    <a href="{{ route('products.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#EEF0E9] hover:bg-[#E2E5DC] text-stone-900 text-xs sm:text-sm font-semibold transition-all border border-black/[0.05]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Produk</span>
                    </a>
                @endif
            </div>

        </header>

        <!-- Main Content Area -->
        <main class="flex-1 px-6 sm:px-8 lg:px-10 py-6 sm:py-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-white border border-emerald-500/20 text-emerald-950 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-white border border-rose-500/20 text-rose-950 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('error') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 rounded-2xl bg-white border border-stone-200 text-stone-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                    <svg class="w-4 h-4 text-stone-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="font-medium leading-relaxed">{{ session('info') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-white border border-rose-500/20 text-rose-950 text-xs sm:text-sm shadow-2xs">
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

            <!-- Main Page Slot / Content -->
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Minimalist System Footer -->
        <footer class="border-t border-black/[0.04] bg-[#F5F6F1] py-4 px-6 sm:px-8 text-center text-xs text-stone-400 font-mono mt-auto">
            <span>Inventoria &middot; Enterprise Logistics Management System &middot; {{ date('Y') }}</span>
        </footer>

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