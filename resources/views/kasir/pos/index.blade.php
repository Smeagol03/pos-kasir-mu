<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a0a0a0; }

        /* Focus styles for keyboard navigation */
        .product-btn:focus { outline: 3px solid #818cf8; outline-offset: 2px; }

        /* Toast Animation */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-enter { animation: slideIn 0.3s ease-out; }

        /* Cloak to prevent flash on load */
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <div x-data="posApp()" x-init="initKeyboard()" class="min-h-screen flex flex-col h-screen lg:overflow-hidden" @keydown.window="handleGlobalKeys($event)">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm z-20">
            <div class="max-w-full mx-auto px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 flex items-center gap-2">
                        <span class="text-indigo-600">⚡</span><span>POS Kasir</span>
                    </h1>
                    <div class="hidden sm:block h-8 w-px bg-gray-200"></div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="font-medium truncate max-w-[160px] sm:max-w-none">{{ auth()->user()->name }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:gap-6">
                    <div class="text-left sm:text-right bg-gray-50 px-4 py-2 rounded-lg border border-gray-100 w-full sm:w-auto">
                        <div class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Transaksi Hari Ini</div>
                        <div class="text-sm font-bold text-gray-900">{{ $todayStats['count'] }} <span class="text-gray-400 font-normal mx-1">|</span> Rp {{ number_format($todayStats['total'], 0, ',', '.') }}</div>
                    </div>
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-1">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:flex-row lg:overflow-hidden relative">
            <!-- Product Grid Area -->
            <div class="flex-1 flex flex-col bg-gray-50 h-full lg:overflow-hidden order-1 lg:order-none">
                <!-- Search Bar -->
                <div class="p-4 bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            type="text" 
                            x-ref="searchInput"
                            x-model="searchQuery" 
                            @input.debounce.300ms="searchProducts"
                            @keydown.arrow-down.prevent="navigateGrid(1)"
                            @keydown.arrow-up.prevent="navigateGrid(-1)"
                            @keydown.enter.prevent="addFocusedProduct()"
                            placeholder="Cari produk atau scan barcode... (F1)" 
                            class="w-full pl-10 pr-4 py-3 bg-white border border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-900 placeholder-gray-400 transition-all"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <span class="text-xs text-gray-400 font-mono bg-gray-100 px-2 py-1 rounded">F1</span>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 overflow-auto p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                        <template x-for="(product, index) in filteredProducts" :key="product.id">
                            <button 
                                @click="addToCart(product)"
                                :id="'product-' + index"
                                class="product-btn bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-indigo-200 hover:-translate-y-1 transition-all duration-200 p-4 flex flex-col relative focus:outline-none"
                                :class="{ 'opacity-50 cursor-not-allowed bg-gray-50': product.stock === 0 }"
                                :disabled="product.stock === 0">
                                
                                <!-- Image Container -->
                                <div class="aspect-square bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden border border-gray-100 group-hover:border-indigo-100 transition-colors">
                                    <template x-if="product.image">
                                        <img :src="'/storage/' + product.image" :alt="product.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image">
                                        <div class="text-center text-gray-300">
                                            <svg class="w-12 h-12 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            <span class="text-xs uppercase font-bold">No Image</span>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800 truncate text-sm leading-tight mb-1" x-text="product.name"></h3>
                                    <div class="flex justify-between items-end mt-1">
                                        <p class="text-indigo-600 font-bold" x-text="formatRupiah(product.price)"></p>
                                        <div class="text-right">
                                            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Stok</p>
                                            <p class="text-xs font-bold" 
                                               :class="product.stock < 10 ? 'text-red-500' : 'text-green-600'" 
                                               x-text="product.stock"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Add Overlay (Visible on Hover/Focus) -->
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="bg-indigo-600 text-white rounded-full p-1.5 shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="filteredProducts.length === 0" class="text-center py-20">
                        <div class="bg-white inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 shadow-sm">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Produk tidak ditemukan</h3>
                        <p class="text-gray-500 mt-1">Coba kata kunci lain atau scan barcode</p>
                    </div>
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div class="w-full lg:w-[400px] bg-white border-t lg:border-t-0 lg:border-l border-gray-200 flex flex-col h-full shadow-xl z-20">
                <!-- Cart Header -->
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h2 class="font-bold text-gray-800">Keranjang <span x-show="cart.length > 0" class="bg-indigo-600 text-white text-[10px] px-1.5 py-0.5 rounded-full ml-1" x-text="cart.length"></span></h2>
                    </div>
                    <button @click="clearCart" x-show="cart.length > 0" class="text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                            <svg class="w-16 h-16 mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="font-medium text-sm">Keranjang masih kosong</p>
                            <p class="text-xs mt-1">Pilih produk dari sebelah kiri</p>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(item, index) in cart" :key="item.product.id">
                            <div class="group flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border border-gray-100 hover:border-indigo-200 transition-all relative"
                                 x-data="{ qty: item.quantity }">
                                
                                <!-- Item Details -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-800 truncate text-sm" x-text="item.product.name"></h4>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="formatRupiah(item.product.price) + ' / item'"></p>
                                </div>

                                <!-- Qty Controls -->
                                <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200 px-1 py-0.5">
                                    <button @click="decrementQty(index)" class="w-7 h-7 flex items-center justify-center rounded text-gray-500 hover:bg-white hover:text-red-500 hover:shadow-sm transition-all text-lg font-bold leading-none">−</button>
                                    <span class="w-8 text-center text-sm font-bold text-gray-800 select-none" x-text="item.quantity"></span>
                                    <button @click="incrementQty(index)" class="w-7 h-7 flex items-center justify-center rounded text-gray-500 hover:bg-white hover:text-indigo-600 hover:shadow-sm transition-all text-lg font-bold leading-none">+</button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right min-w-[70px]">
                                    <p class="font-bold text-sm text-gray-900" x-text="formatRupiah(item.product.price * item.quantity)"></p>
                                </div>

                                <!-- Remove Button (Visible on Hover) -->
                                <button @click="removeFromCart(index)" class="absolute -left-2 top-1/2 -translate-y-1/2 translate-x-0 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs shadow-md transition-all hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Payment Section (Sticky Footer) -->
                <div class="bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] p-4 z-20">
                    <!-- Summary -->
                    <div class="mb-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-900" x-text="formatRupiah(cartTotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Total Item</span>
                            <span class="font-medium text-gray-900" x-text="cart.reduce((a,b)=>a+b.quantity,0)"></span>
                        </div>
                        <div class="pt-2 mt-2 border-t border-dashed border-gray-200">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total</span>
                                <span class="text-2xl font-black text-indigo-700" x-text="formatRupiah(cartTotal)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Input -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                            <input 
                                type="text" 
                                id="cash_received"
                                inputmode="numeric"
                                class="w-full pl-10 pr-12 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 font-bold text-lg transition-all"
                                placeholder="0" 
                                @input="handleCashInput($event)"
                                :value="cashReceived.toLocaleString('id-ID')"
                            >
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1">
                                <button @click="setCash(cartTotal)" title="Uang Pas (F2)" class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 transition-colors">PAS</button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                        <button @click="setCash(20000)" class="py-1.5 text-xs font-bold bg-white border border-gray-200 rounded shadow-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">20rb</button>
                        <button @click="setCash(50000)" class="py-1.5 text-xs font-bold bg-white border border-gray-200 rounded shadow-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">50rb</button>
                        <button @click="setCash(100000)" class="py-1.5 text-xs font-bold bg-white border border-gray-200 rounded shadow-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">100rb</button>
                        <button @click="setCash(150000)" class="py-1.5 text-xs font-bold bg-white border border-gray-200 rounded shadow-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">150rb</button>
                    </div>

                    <!-- Change Display -->
                    <div class="flex justify-between items-center mb-4 p-3 rounded-lg" :class="change >= 0 ? 'bg-green-50' : 'bg-red-50'">
                        <span class="text-sm font-medium" :class="change >= 0 ? 'text-green-700' : 'text-red-700'">Kembalian</span>
                        <span class="text-xl font-black" :class="change >= 0 ? 'text-green-700' : 'text-red-700'" x-text="formatRupiah(change)"></span>
                    </div>

                    <!-- Checkout Button -->
                    <button 
                        @click="checkout" 
                        :disabled="cart.length === 0 || cashReceived < cartTotal || isLoading"
                        class="w-full py-4 bg-indigo-600 text-white text-lg font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 disabled:bg-gray-300 disabled:shadow-none disabled:cursor-not-allowed transform active:scale-[0.99] transition-all flex items-center justify-center gap-2"
                    >
                        <template x-if="!isLoading">
                            <span class="flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span x-text="cart.length > 0 ? 'BAYAR (' + formatRupiah(cartTotal) + ')' : 'PILIH ITEM DULU'"></span>
                            </span>
                        </template>
                        <template x-if="isLoading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>MEMPROSES...</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div x-show="toasts.length > 0" x-cloak class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 flex flex-col gap-3 pointer-events-none">
            <template x-for="(toast, index) in toasts" :key="index">
                <div 
                    class="toast-enter pointer-events-auto max-w-sm bg-white rounded-lg shadow-lg border-l-4 p-4 flex items-center gap-3 min-w-[300px]"
                    :class="{
                        'border-green-500': toast.type === 'success',
                        'border-red-500': toast.type === 'error',
                        'border-indigo-500': toast.type === 'info'
                    }"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="flex-shrink-0">
                        <template x-if="toast.type === 'success'">
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900" x-text="toast.message"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal" x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all scale-100" @click.outside="!isLoading && closeSuccessModal()">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Transaksi Berhasil!</h3>
                    <p class="text-gray-500 mb-6">Kode Invoice:</p>
                    <p class="text-2xl font-mono font-bold text-indigo-600 mb-6 bg-indigo-50 py-2 rounded-lg" x-text="lastTransaction?.invoice_code"></p>
                    
                    <div class="bg-gray-50 rounded-xl p-5 mb-8 text-left space-y-3 border border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total</span>
                            <span class="font-bold text-gray-900" x-text="formatRupiah(lastTransaction?.total || 0)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tunai</span>
                            <span class="font-bold text-gray-900" x-text="formatRupiah(lastTransaction?.cash || 0)"></span>
                        </div>
                        <div class="flex justify-between text-base pt-2 border-t border-gray-200">
                            <span class="font-medium text-gray-700">Kembalian</span>
                            <span class="font-bold text-green-600" x-text="formatRupiah(lastTransaction?.change || 0)"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <a :href="'/kasir/pos/receipt/' + lastTransaction?.id" target="_blank" class="flex items-center justify-center gap-2 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Struk
                        </a>
                        <button @click="closeSuccessModal" class="flex items-center justify-center gap-2 py-3 border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors">
                            Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function posApp() {
            return {
                products: @json($products),
                searchQuery: '',
                filteredProducts: @json($products),
                cart: [],
                cashReceived: 0,
                isLoading: false,
                showSuccessModal: false,
                lastTransaction: null,
                toasts: [],
                focusedProductIndex: -1,

                init() {
                    // Reset all state on page load to prevent glitches
                    this.showSuccessModal = false;
                    this.lastTransaction = null;
                    this.toasts = [];
                    this.isLoading = false;
                    this.cart = [];
                    this.cashReceived = 0;

                    // Clear state when page unloads
                    window.addEventListener('beforeunload', () => {
                        this.showSuccessModal = false;
                        this.toasts = [];
                    });
                },

                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
                },

                get change() {
                    return this.cashReceived - this.cartTotal;
                },

                formatRupiah(num) {
                    if (num === null || num === undefined) return 'Rp 0';
                    return 'Rp ' + num.toLocaleString('id-ID');
                },

                // Toast Notification System
                showToast(message, type = 'info') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                },

                handleCashInput(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    this.cashReceived = parseInt(value) || 0;
                },

                setCash(amount) {
                    this.cashReceived = amount;
                    // Focus back on the input for convenience
                    // this.$nextTick(() => document.getElementById('cash_received').focus());
                },

                searchProducts() {
                    if (!this.searchQuery) {
                        this.filteredProducts = this.products;
                        return;
                    }
                    const q = this.searchQuery.toLowerCase();
                    this.filteredProducts = this.products.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.barcode && p.barcode.toLowerCase() === q)
                    );
                    this.focusedProductIndex = -1; // Reset focus when searching
                },

                addToCart(product) {
                    if (product.stock === 0) {
                        this.showToast('Stok produk habis!', 'error');
                        return;
                    }

                    const existing = this.cart.find(item => item.product.id === product.id);
                    if (existing) {
                        if (existing.quantity < product.stock) {
                            existing.quantity++;
                        } else {
                            this.showToast('Stok tidak mencukupi!', 'error');
                            return;
                        }
                    } else {
                        this.cart.push({
                            product,
                            quantity: 1
                        });
                    }
                },

                incrementQty(index) {
                    const item = this.cart[index];
                    if (item.quantity < item.product.stock) {
                        item.quantity++;
                    } else {
                        this.showToast('Stok tidak mencukupi!', 'error');
                    }
                },

                decrementQty(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.removeFromCart(index);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
                        this.cart = [];
                        this.cashReceived = 0;
                    }
                },

                // Keyboard Navigation Logic
                initKeyboard() {
                    this.$refs.searchInput.focus();
                },
                
                handleGlobalKeys(e) {
                    // If modal is open, ignore global shortcuts except Escape
                    if (this.showSuccessModal) {
                        if (e.key === 'Escape') this.closeSuccessModal();
                        return;
                    }

                    // Ignore if typing in input
                    if (e.target.tagName === 'INPUT' && e.target.type === 'text') return;

                    switch(e.key) {
                        case 'F1': 
                            e.preventDefault();
                            this.$refs.searchInput.focus();
                            break;
                        case 'F2':
                            e.preventDefault();
                            this.setCash(this.cartTotal);
                            break;
                        case 'F3':
                        case 'Enter':
                            // Only trigger checkout if cash is sufficient
                            if (this.cart.length > 0 && this.cashReceived >= this.cartTotal) {
                                e.preventDefault();
                                this.checkout();
                            }
                            break;
                    }
                },

                navigateGrid(direction) {
                    if (this.filteredProducts.length === 0) return;
                    
                    let newIndex = this.focusedProductIndex + direction;
                    if (newIndex < 0) newIndex = 0;
                    if (newIndex >= this.filteredProducts.length) newIndex = this.filteredProducts.length - 1;

                    this.focusedProductIndex = newIndex;
                    
                    // Scroll into view
                    this.$nextTick(() => {
                        const el = document.getElementById('product-' + newIndex);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.focus();
                        }
                    });
                },

                addFocusedProduct() {
                    if (this.focusedProductIndex >= 0 && this.focusedProductIndex < this.filteredProducts.length) {
                        this.addToCart(this.filteredProducts[this.focusedProductIndex]);
                    }
                },

                async checkout() {
                    if (this.cart.length === 0) return;

                    if (this.cashReceived < this.cartTotal) {
                        this.showToast('Uang tidak mencukupi!', 'error');
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const response = await fetch('{{ route('kasir.pos.checkout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                items: this.cart.map(item => ({
                                    product_id: item.product.id,
                                    quantity: item.quantity
                                })),
                                cash: this.cashReceived
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.lastTransaction = data.transaction;
                            this.showSuccessModal = true;
                            this.showToast('Transaksi berhasil!', 'success');

                            // Update local stock
                            this.cart.forEach(item => {
                                const product = this.products.find(p => p.id === item.product.id);
                                if (product) {
                                    product.stock -= item.quantity;
                                }
                            });

                            this.cart = [];
                            this.cashReceived = 0;
                            this.searchProducts();
                            this.$refs.searchInput.focus();
                        } else {
                            this.showToast(data.message || 'Terjadi kesalahan', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan: ' + error.message, 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                closeSuccessModal() {
                    this.showSuccessModal = false;
                    this.lastTransaction = null;
                    this.$refs.searchInput.focus();
                }
            };
        }
    </script>
</body>

</html>
