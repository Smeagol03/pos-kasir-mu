<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Kasir — Streamlined Retail Management</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .mesh-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.03) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(225, 39%, 30%, 0.03) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(339, 49%, 30%, 0.03) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(253, 16%, 7%, 0.03) 0px, transparent 50%);
        }

        .dark .mesh-bg {
            background-color: #030712;
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(225, 39%, 30%, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(339, 49%, 30%, 0.1) 0px, transparent 50%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dark .glass {
            background: rgba(17, 24, 39, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="antialiased mesh-bg text-slate-900 dark:text-slate-100">
    <div class="relative min-h-screen flex flex-col">
        <!-- Decoration -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-gradient-to-b from-indigo-50/50 to-transparent pointer-events-none dark:from-indigo-950/20">
        </div>

        <!-- Navigation -->
        <header class="relative z-20 px-6 py-6" x-data="{ open: false }">
            <nav class="max-w-6xl mx-auto flex justify-between items-center gap-4">
                <a href="/" class="flex items-center gap-2 group">
                    <div
                        class="w-10 h-10 bg-slate-900 dark:bg-white rounded-xl flex items-center justify-center transition-transform group-hover:scale-105 shadow-lg shadow-slate-200 dark:shadow-none">
                        <svg class="w-6 h-6 text-white dark:text-slate-900" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">POS<span class="font-light">KASIR</span></span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="text-sm font-medium hover:text-indigo-600 transition-colors">Dashboard</a>
                        <a href="{{ route('kasir.pos.index') }}"
                            class="px-6 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-full text-sm font-bold hover:opacity-90 transition-opacity shadow-lg shadow-slate-200 dark:shadow-none">
                            Buka Kasir
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium hover:text-indigo-600 transition-colors">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-6 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-full text-sm font-bold hover:opacity-90 transition-opacity shadow-lg shadow-slate-200 dark:shadow-none">
                                Daftar Gratis
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button @click="open = !open"
                    class="md:hidden p-2 text-slate-600 dark:text-slate-300 focus:outline-none"
                    aria-label="Toggle navigation">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!open">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="open"
                        style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
                class="absolute top-full left-0 w-full glass md:hidden border-b border-slate-100 dark:border-slate-800 py-6 px-6 z-50"
                style="display: none;">
                <div class="flex flex-col gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-lg font-medium py-2">Dashboard</a>
                        <a href="{{ route('kasir.pos.index') }}"
                            class="w-full h-14 flex items-center justify-center bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold">
                            Buka Kasir
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-lg font-medium py-2">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="w-full h-14 flex items-center justify-center bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold">
                                Daftar Gratis
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <!-- Hero -->
        <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 text-center">
            <div class="max-w-3xl">
                <h1 class="md:mt-20 mt-10 md:text-5xl text-4xl font-bold tracking-tight mb-8 leading-[1.1]">
                    Kasir digital untuk <br />
                    <span class="text-indigo-600">toko modernmu.</span>
                </h1>
                <p
                    class="md:text-xl text-lg text-slate-500 dark:text-slate-400 mb-12 max-w-xl mx-auto leading-relaxed font-light">
                    Kelola stok, proses transaksi, dan pantau penjualan dalam satu platform yang didesain untuk
                    kecepatan.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-24">
                    @auth
                        <a href="{{ route('kasir.pos.index') }}"
                            class="h-14 px-10 flex items-center bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold shadow-2xl shadow-slate-200 dark:shadow-none transition-transform hover:-translate-y-1">
                            Mulai Transaksi
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                            class="h-14 px-10 flex items-center bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold shadow-2xl shadow-slate-200 dark:shadow-none transition-transform hover:-translate-y-1">
                            Mulai Gratis Sekarang
                        </a>
                    @endauth
                </div>

                <!-- Features Minimalist -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left max-w-5xl mx-auto py-12 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="text-indigo-600 font-bold mb-4 tracking-tighter">01.</div>
                        <h3 class="text-lg font-bold mb-3">Manajemen Stok</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed font-light">
                            Pantau ketersediaan barang secara real-time dengan sistem notifikasi stok rendah.
                        </p>
                    </div>
                    <div>
                        <div class="text-indigo-600 font-bold mb-4 tracking-tighter">02.</div>
                        <h3 class="text-lg font-bold mb-3">Transaksi Kilat</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed font-light">
                            Antarmuka yang dioptimalkan untuk kecepatan layar sentuh dan pemindaian barcode.
                        </p>
                    </div>
                    <div>
                        <div class="text-indigo-600 font-bold mb-4 tracking-tighter">03.</div>
                        <h3 class="text-lg font-bold mb-3">Laporan Akurat</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed font-light">
                            Dapatkan wawasan penjualan harian dan produk terlaris dengan grafik yang bersih.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 px-6 py-12">
            <div
                class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6 border-t border-slate-50 dark:border-slate-900 pt-12">
                <div class="text-sm font-light text-slate-400">
                    &copy; {{ date('Y') }} POSKASIR Digital. All rights reserved.
                </div>
                <div class="flex gap-8 text-xs font-medium text-slate-400 uppercase tracking-widest">
                    <a href="#" class="hover:text-slate-900 dark:hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-slate-900 dark:hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-slate-900 dark:hover:text-white transition-colors">Support</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
