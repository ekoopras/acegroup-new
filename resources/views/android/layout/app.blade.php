<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#2563eb">
    <title>@yield('title', 'Service App')</title>

    <link rel="manifest" href="/android/manifest.json">

    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Mencegah seleksi teks dan klik highlight yang mengganggu di Android */
        body {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        /* Memberi ruang agar konten tidak tertutup bottom nav */
        .safe-area-bottom {
            padding-bottom: calc(4rem + env(safe-area-inset-bottom));
        }
    </style>
</head>

<body class="bg-slate-50 antialiased text-slate-900">

    <header class="sticky top-0 z-40 w-full bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="flex items-center justify-between h-14 px-4">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-1.5 rounded-lg text-white">
                    <i data-lucide="wrench" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-lg tracking-tight">@yield('title')</span>
            </div>
            <button class="p-2 text-slate-500">
                <i data-lucide="bell" class="w-5 h-5"></i>
            </button>
        </div>
    </header>

    <main class="safe-area-bottom">
        @yield('content')
    </main>

    <nav class="fixed bottom-4 left-4 right-4 z-50">
        <div
            class="bg-white/90 backdrop-blur-lg border border-slate-200 shadow-2xl rounded-2xl flex justify-between items-center px-2 py-2">
            <a href="{{ route('android.clients') }}"
                class="flex flex-col items-center justify-center w-full py-1 {{ request()->routeIs('android.clients') ? 'text-blue-600' : 'text-slate-400' }}">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Client</span>
            </a>
            <a href="{{ route('android.service-masuk') }}"
                class="flex flex-col items-center justify-center w-full py-1 {{ request()->routeIs('android.service-masuk') ? 'text-blue-600' : 'text-slate-400' }}">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Masuk</span>
            </a>
            <a href="{{ route('android.service-proses') }}"
                class="flex flex-col items-center justify-center w-full py-1 {{ request()->routeIs('android.service-proses') ? 'text-blue-600' : 'text-slate-400' }}">
                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Proses</span>
            </a>
            <a href="{{ route('android.service-jadi') }}"
                class="flex flex-col items-center justify-center w-full py-1 {{ request()->routeIs('android.service-jadi') ? 'text-blue-600' : 'text-slate-400' }}">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Selesai</span>
            </a>
            <a href="{{ route('android.profile') }}"
                class="flex flex-col items-center justify-center w-full py-1 {{ request()->routeIs('android.profile') ? 'text-blue-600' : 'text-slate-400' }}">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1 font-medium">Profil</span>
            </a>
        </div>
    </nav>

    <script>
        // Inisialisasi Icon Lucide
        lucide.createIcons();

        // Registrasi Service Worker untuk PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered: ', registration);
                }).catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
    </script>
</body>

</html>
