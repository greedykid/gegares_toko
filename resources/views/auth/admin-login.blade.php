<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Gegares</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/alpine.js') }}" defer></script>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center px-4 relative font-sans antialiased">

    <div class="relative w-full max-w-md" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4' " class="transition-all duration-700 ease-out">
        
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-24 h-24 bg-slate-900 rounded-4xl flex items-center justify-center mb-5 border border-slate-800 shadow-lg group">
                <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo" class="w-16 h-16 object-contain group-hover:scale-105 transition-transform duration-300">
            </div>
            <div class="inline-flex px-3 py-1 bg-slate-900 border border-slate-800 rounded-full mb-3">
                <span class="text-[10px] font-bold text-primary-400 tracking-widest uppercase">Admin Control</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Login Administrator</h1>
            <p class="mt-1.5 text-sm text-slate-400">Masukkan kredensial untuk mengakses dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800/80 shadow-2xl p-8 relative overflow-hidden" x-data="{ show: false, loading: false }">
            <!-- Decorative Accent Top Border -->
            <div class="absolute top-0 inset-x-0 h-1 bg-primary-600"></div>

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form id="admin-login-form" @submit="loading = true" method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-primary-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500/20 transition-all text-sm"
                               placeholder="admin@gegares.shop">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-primary-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                               class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500/20 transition-all text-sm"
                               placeholder="••••••••">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none p-1.5 transition-colors">
                            <template x-if="!show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center">
                    <label class="group flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="remember" 
                                   class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border border-slate-800 bg-slate-950 checked:bg-primary-600 checked:border-primary-600 transition-all duration-200">
                            <svg class="absolute h-3.5 w-3.5 pointer-events-none stroke-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" 
                                 fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-400 group-hover:text-slate-200 transition-colors">Ingat sesi login</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    :disabled="loading"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                    class="w-full py-3.5 bg-primary-600 text-white font-bold uppercase tracking-widest text-xs rounded-xl hover:bg-primary-500 shadow-md shadow-primary-950/20 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Masuk...' : 'Masuk ke Dashboard'">Masuk ke Dashboard</span>
                </button>
            </form>
        </div>

        <!-- Back to Store Link -->
        <div class="text-center mt-6">
            <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-primary-400 transition-colors py-2 px-4 rounded-full border border-transparent hover:border-slate-800/80 hover:bg-slate-900/50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Toko
            </a>
        </div>

        <p class="mt-6 text-center text-xs text-slate-600">&copy; {{ date('Y') }} Gegares Admin</p>
    </div>

    @if(!app()->environment('local') && config('services.recaptcha.site') && config('services.recaptcha.secret'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
        <script>
            document.getElementById('admin-login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute("{{ config('services.recaptcha.site') }}", {action: 'admin_login'}).then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        document.getElementById('admin-login-form').submit();
                    }).catch(function(error) {
                        document.getElementById('g-recaptcha-response').value = 'error';
                        document.getElementById('admin-login-form').submit();
                    });
                });
            });
        </script>
    @else
        <script>
            document.getElementById('admin-login-form').addEventListener('submit', function(e) {
                document.getElementById('g-recaptcha-response').value = 'local-bypass';
            });
        </script>
    @endif
</body>
</html>
