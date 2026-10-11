<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventoria</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased font-sans text-slate-800">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600 text-white mb-3 shadow-lg shadow-indigo-600/30">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Inventoria System</h1>
            <p class="text-sm text-slate-400 mt-1">Sistem Manajemen Inventaris & Logistik Gudang</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8">
            @if(session('info'))
                <div class="mb-5 p-3 rounded-lg bg-sky-50 border border-sky-200 text-sky-800 text-sm">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username / Email</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('username') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-sm transition-all"
                        placeholder="admin atau staff">
                    @error('username')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-sm transition-all"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-6 pt-5 border-t border-slate-100 bg-slate-50 -mx-8 -mb-8 p-6 rounded-b-2xl">
                <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Akun Demo Praktikum:</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded bg-white border border-slate-200">
                        <span class="font-bold text-slate-800">Admin:</span>
                        <div class="text-slate-500 font-mono text-[11px]">admin / admin123</div>
                    </div>
                    <div class="p-2 rounded bg-white border border-slate-200">
                        <span class="font-bold text-slate-800">Staff:</span>
                        <div class="text-slate-500 font-mono text-[11px]">staff / staff123</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
