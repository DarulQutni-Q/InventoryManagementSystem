<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F5F6F1]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventoria System</title>

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
                        sans: ['Plus Jakarta Sans', 'Geist', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Geist Mono', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        canvas: '#F5F6F1',
                        cream: '#EEF0E9',
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
            letter-spacing: -0.015em;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 antialiased font-sans text-stone-900 bg-[#F5F6F1]">
    <div class="max-w-md w-full">

        <!-- Logo & Header Title -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#18181B] text-white mb-3 shadow-xs">
                <span class="font-extrabold text-base tracking-tighter">IV</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Inventoria System</h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1 font-normal">
                Sistem Manajemen Inventaris & Logistik Gudang
            </p>
        </div>

        <!-- Clean Surface Card -->
        <div class="bg-white rounded-3xl border border-black/[0.05] shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 sm:p-8">

            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-xs">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Username / Akun
                    </label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('username') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-stone-900 text-sm placeholder-stone-400 transition-colors shadow-2xs"
                        placeholder="admin atau staff">
                    @error('username')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-mono font-semibold text-stone-600 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3.5 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-stone-200 bg-stone-50/50 @enderror focus:bg-white focus:border-stone-400 focus:ring-1 focus:ring-black/10 text-stone-900 text-sm placeholder-stone-400 transition-colors font-mono shadow-2xs"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-stone-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded border-stone-300 text-stone-900 focus:ring-black/20">
                        <span>Ingat sesi masuk</span>
                    </label>
                    <span class="text-stone-400 font-mono text-[11px]">Enkripsi Bcrypt</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-full bg-[#18181B] hover:bg-black text-white font-semibold text-sm transition-all shadow-xs mt-3">
                    Masuk ke Sistem Inventaris &rarr;
                </button>
            </form>

            <!-- Quick Demo Accounts Helper -->
            <div class="mt-6 pt-5 border-t border-stone-100">
                <div class="text-[10px] font-mono font-semibold text-stone-400 uppercase tracking-widest mb-2.5">
                    Akun Akses Cepat (Klik untuk isi):
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillCreds('admin', 'admin123')" class="p-2.5 rounded-xl bg-[#F5F6F1] border border-black/[0.04] hover:bg-[#ECEEE8] text-left transition-colors group">
                        <div class="font-bold text-stone-800 text-[11px]">Admin Utama</div>
                        <div class="text-stone-500 font-mono text-[10px] mt-0.5">admin / admin123</div>
                    </button>
                    <button type="button" onclick="fillCreds('staff', 'staff123')" class="p-2.5 rounded-xl bg-[#F5F6F1] border border-black/[0.04] hover:bg-[#ECEEE8] text-left transition-colors group">
                        <div class="font-bold text-stone-800 text-[11px]">Staff Gudang</div>
                        <div class="text-stone-500 font-mono text-[10px] mt-0.5">staff / staff123</div>
                    </button>
                </div>
            </div>

        </div>

        <div class="mt-6 text-center text-xs text-stone-400 font-mono">
            <span>Inventoria &middot; Enterprise Logistics Management System &middot; {{ date('Y') }}</span>
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