<div x-show="sidebarOpen" class="fixed inset-0 flex z-40 lg:hidden" role="dialog" aria-modal="true" x-ref="mobileSidebar">
    <!-- Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-600 bg-opacity-75" 
         @click="sidebarOpen = false" 
         aria-hidden="true"></div>

    <!-- Sidebar content -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-in-out duration-300 transform" 
         x-transition:enter-start="-translate-x-full" 
         x-transition:enter-end="translate-x-0" 
         x-transition:leave="transition ease-in-out duration-300 transform" 
         x-transition:leave-start="translate-x-0" 
         x-transition:leave-end="-translate-x-full" 
         class="relative flex-1 flex flex-col max-w-xs w-full bg-white transition ease-in-out duration-300 transform">
        
        <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                <span class="sr-only">Close sidebar</span>
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
            <div class="flex-shrink-0 flex items-center px-4">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="h-8 w-auto fill-current text-indigo-600" />
                </a>
            </div>
            <nav class="mt-5 px-2 space-y-1">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="heroicon-o-home">
                    {{ __('Dashboard') }}
                </x-sidebar-link>

                @if (auth()->user()->isAdmin() || auth()->user()->isKasir())
                    <x-sidebar-link :href="route('kasir.pos.index')" :active="request()->routeIs('kasir.pos.*')" icon="heroicon-o-shopping-cart">
                        {{ __('POS Kasir') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')" icon="heroicon-o-receipt-refund">
                        {{ __('Riwayat Transaksi') }}
                    </x-sidebar-link>
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="pt-4 pb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Admin Menu') }}</p>
                    </div>
                    <x-sidebar-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')" icon="heroicon-o-cube">
                        {{ __('Kelola Produk') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.stock.index')" :active="request()->routeIs('admin.stock.*')" icon="heroicon-o-archive-box">
                        {{ __('Manajemen Stok') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="heroicon-o-users">
                        {{ __('Kelola User') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" icon="heroicon-o-chart-bar">
                        {{ __('Laporan Keuangan') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')" icon="heroicon-o-document-text">
                        {{ __('Activity Log') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')" icon="heroicon-o-cog">
                        {{ __('Pengaturan') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups.*')" icon="heroicon-o-arrow-down-on-square-stack">
                        {{ __('Backup Data') }}
                    </x-sidebar-link>
                @endif
            </nav>
        </div>
        <!-- Mobile Sidebar Bottom: User Info & Logout -->
        <div class="flex-shrink-0 flex border-t border-gray-200 p-4">
            <div class="flex-shrink-0 w-full group block">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="inline-block h-9 w-9 rounded-full overflow-hidden bg-gray-100">
                            <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-shrink-0 w-14">
        <!-- Dummy element to force sidebar to shrink to fit close button -->
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:flex lg:flex-shrink-0">
    <div class="flex flex-col w-64 bg-white border-r border-gray-200">
        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
            <div class="flex items-center flex-shrink-0 px-4 mb-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-9 w-auto fill-current text-indigo-600" />
                    <span class="text-xl font-bold text-gray-900 tracking-tight">{{ config('app.name') }}</span>
                </a>
            </div>
            <nav class="flex-1 px-3 space-y-1">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="heroicon-o-home">
                    {{ __('Dashboard') }}
                </x-sidebar-link>

                @if (auth()->user()->isAdmin() || auth()->user()->isKasir())
                    <x-sidebar-link :href="route('kasir.pos.index')" :active="request()->routeIs('kasir.pos.*')" icon="heroicon-o-shopping-cart">
                        {{ __('POS Kasir') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')" icon="heroicon-o-receipt-refund">
                        {{ __('Riwayat Transaksi') }}
                    </x-sidebar-link>
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="pt-6 pb-2 px-3">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ __('Admin Management') }}</p>
                    </div>
                    <x-sidebar-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')" icon="heroicon-o-cube">
                        {{ __('Kelola Produk') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.stock.index')" :active="request()->routeIs('admin.stock.*')" icon="heroicon-o-archive-box">
                        {{ __('Manajemen Stok') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="heroicon-o-users">
                        {{ __('Kelola User') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" icon="heroicon-o-chart-bar">
                        {{ __('Laporan Keuangan') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')" icon="heroicon-o-document-text">
                        {{ __('Activity Log') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')" icon="heroicon-o-cog">
                        {{ __('Pengaturan') }}
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups.*')" icon="heroicon-o-arrow-down-on-square-stack">
                        {{ __('Backup Data') }}
                    </x-sidebar-link>
                @endif
            </nav>
        </div>
        <!-- Sidebar Bottom: User Info -->
        <div class="flex-shrink-0 flex border-t border-gray-200 p-4">
            <div class="flex-shrink-0 w-full group block">
                <div class="flex items-center">
                    <div class="inline-block h-9 w-9 rounded-full overflow-hidden bg-gray-100">
                        <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
