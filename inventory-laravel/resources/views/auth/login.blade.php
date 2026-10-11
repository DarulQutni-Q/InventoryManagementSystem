<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0c0d0e]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventoria System</title>

    <!-- Google Fonts: Geist & Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Geist"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"Geist Mono"', '"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        obsidian: '#0c0d0e',
                        surface: '#121316',
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
<body class="h-full flex items-center justify-center p-4 sm:p-6 antialiased font-sans text-zinc-200 bg-[#0c0d0e]">
    <div class="max-w-md w-full">

        <!-- Logo & Header Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-[#5e6ad2] text-white mb-3 shadow-sm ring-1 ring-white/20">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Inventoria System</h1>
            <p class="text-xs sm:text-sm text-zinc-400 mt-1 font-normal">
                Sistem Manajemen Inventaris & Logistik Gudang
            </p>
        </div>

        <!-- Double-Bezel Hardware Architecture Card (Linear / Vercel Precision) -->
        <div class="p-1 rounded-2xl bg-white/[0.04] border border-white/[0.08] shadow-2xl">
            <div class="bg-[#121316] rounded-[calc(1rem-0.125rem)] p-6 sm:p-7 border border-white/[0.06]">

                @if(session('info'))
                    <div class="mb-5 p-3 rounded-lg bg-white/[0.05] border border-white/[0.08] text-zinc-300 text-xs">
                        {{ session('info') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="block text-[11px] font-mono font-medium text-zinc-400 uppercase tracking-wider mb-1.5">
                            Username / Akun
                        </label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                            class="w-full px-3.5 py-2 rounded-lg bg-[#0c0d0e] border @error('username') border-rose-500 @else border-white/[0.1] @enderror focus:border-[#5e6ad2] focus:ring-1 focus:ring-[#5e6ad2] text-white text-sm placeholder-zinc-600 transition-colors"
                            placeholder="admin atau staff">
                        @error('username')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-[11px] font-mono font-medium text-zinc-400 uppercase tracking-wider mb-1.5">
                            Kata Sandi
                        </label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3.5 py-2 rounded-lg bg-[#0c0d0e] border @error('password') border-rose-500 @else border-white/[0.1] @enderror focus:border-[#5e6ad2] focus:ring-1 focus:ring-[#5e6ad2] text-white text-sm placeholder-zinc-600 transition-colors font-mono"
                            placeholder="••••••••">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 text-zinc-400 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="rounded border-zinc-700 bg-zinc-950 text-[#5e6ad2] focus:ring-0">
                            <span>Ingat sesi masuk</span>
                        </label>
                        <span class="text-zinc-500 font-mono text-[11px]">Enkripsi Bcrypt</span>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#5e6ad2] hover:bg-[#4f5ac4] text-white font-medium text-sm transition-all shadow-sm mt-2">
                        Masuk ke Sistem Inventaris &rarr;
                    </button>
                </form>

                <!-- Quick Demo Accounts Helper -->
                <div class="mt-6 pt-5 border-t border-white/[0.08]">
                    <div class="text-[10px] font-mono font-medium text-zinc-400 uppercase tracking-widest mb-2.5">
                        Akun Akses Cepat (Klik untuk isi):
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="fillCreds('admin', 'admin123')" class="p-2.5 rounded-lg bg-white/[0.03] border border-white/[0.08] hover:border-white/[0.16] hover:bg-white/[0.06] text-left transition-colors group">
                            <div class="font-medium text-white text-[11px] group-hover:text-indigo-300">Admin Utama</div>
                            <div class="text-zinc-400 font-mono text-[10px] mt-0.5">admin / admin123</div>
                        </button>
                        <button type="button" onclick="fillCreds('staff', 'staff123')" class="p-2.5 rounded-lg bg-white/[0.03] border border-white/[0.08] hover:border-white/[0.16] hover:bg-white/[0.06] text-left transition-colors group">
                            <div class="font-medium text-white text-[11px] group-hover:text-indigo-300">Staff Gudang</div>
                            <div class="text-zinc-400 font-mono text-[10px] mt-0.5">staff / staff123</div>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-6 text-center text-xs text-zinc-500 font-mono">
            <span>Inventoria P5 Enterprise &middot; Praktikum Pemrograman Web</span>
        </div>

    </div>

    <script>
        function fillCreds(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
