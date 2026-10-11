<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0B0F19]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventoria System</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 antialiased font-sans text-slate-200">
    <div class="max-w-md w-full">

        <!-- Logo & Header Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white mb-3 shadow-lg shadow-indigo-600/20 ring-1 ring-white/20">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Inventoria System</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 font-normal">
                Sistem Manajemen Inventaris & Logistik Gudang
            </p>
        </div>

        <!-- Double-Bezel Hardware Architecture Card (Doppelrand) -->
        <div class="p-1.5 sm:p-2 rounded-3xl bg-slate-800/40 ring-1 ring-slate-700/60 shadow-2xl backdrop-blur-xl">
            <div class="bg-slate-900/90 rounded-[calc(1.5rem-0.375rem)] p-7 sm:p-8 border border-slate-800/80">

                @if(session('info'))
                    <div class="mb-5 p-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs">
                        {{ session('info') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3 rounded-xl bg-rose-950/40 border border-rose-800/60 text-rose-300 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="block text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                            Username / Identitas Akun
                        </label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/70 border @error('username') border-rose-500 @else border-slate-800 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white text-sm placeholder-slate-600 transition-colors"
                            placeholder="admin atau staff">
                        @error('username')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-[11px] font-mono font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                            Kata Sandi Otentikasi
                        </label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/70 border @error('password') border-rose-500 @else border-slate-800 @enderror focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white text-sm placeholder-slate-600 transition-colors font-mono"
                            placeholder="••••••••">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 text-slate-400 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-0">
                            <span>Ingat sesi masuk</span>
                        </label>
                        <span class="text-slate-500 font-mono text-[11px]">Enkripsi Bcrypt</span>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all hover:scale-[1.01] active:scale-[0.99] shadow-lg shadow-indigo-600/25 mt-2">
                        Masuk ke Sistem Inventaris &rarr;
                    </button>
                </form>

                <!-- Quick Demo Accounts Helper -->
                <div class="mt-6 pt-5 border-t border-slate-800/80">
                    <div class="text-[10px] font-mono font-semibold text-slate-400 uppercase tracking-widest mb-2.5">
                        Akun Akses Praktikum (Klik untuk isi):
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="fillCreds('admin', 'admin123')" class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800 hover:border-slate-700 text-left transition-colors group">
                            <div class="font-bold text-white text-[11px] group-hover:text-indigo-300">Admin Utama</div>
                            <div class="text-slate-400 font-mono text-[10px] mt-0.5">admin / admin123</div>
                        </button>
                        <button type="button" onclick="fillCreds('staff', 'staff123')" class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800 hover:border-slate-700 text-left transition-colors group">
                            <div class="font-bold text-white text-[11px] group-hover:text-indigo-300">Staff Gudang</div>
                            <div class="text-slate-400 font-mono text-[10px] mt-0.5">staff / staff123</div>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-400 font-mono">
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
