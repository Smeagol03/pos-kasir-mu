<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Penyesuaian Stok') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="stockAdjustment()">
                    <form action="{{ route('admin.stock.store') }}" method="POST">
                        @csrf

                        <!-- Searchable Product Dropdown -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari & Pilih Produk</label>
                            <div class="relative">
                                <input type="text" x-model="searchQuery" @input="filterProducts()"
                                    placeholder="Ketik nama produk..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <input type="hidden" name="product_id" :value="selectedProduct?.id" required>
                            </div>

                            <!-- Dropdown Results -->
                            <div x-show="searchQuery.length > 0 && !selectedProduct" x-cloak
                                class="mt-1 max-h-60 overflow-y-auto border border-gray-200 rounded-md shadow-lg bg-white">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <div @click="selectProduct(product)"
                                        class="px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center">
                                        <span x-text="product.name" class="font-medium"></span>
                                        <span class="text-sm text-gray-500">Stok: <span x-text="product.stock"
                                                :class="product.stock < 10 ? 'text-red-600 font-bold' : ''"></span></span>
                                    </div>
                                </template>
                                <div x-show="filteredProducts.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                    Produk tidak ditemukan
                                </div>
                            </div>

                            <!-- Selected Product Display -->
                            <div x-show="selectedProduct" x-cloak
                                class="mt-2 p-3 bg-indigo-50 rounded-md flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-indigo-800" x-text="selectedProduct?.name"></span>
                                    <span class="ml-2 text-sm text-gray-600">Stok saat ini: <strong
                                            x-text="selectedProduct?.stock"></strong></span>
                                </div>
                                <button type="button" @click="clearSelection()"
                                    class="text-red-500 hover:text-red-700 text-sm font-medium">Ganti</button>
                            </div>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Adjustment Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Penyesuaian</label>
                            <div class="flex gap-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type" value="in" x-model="adjustmentType"
                                        class="hidden peer">
                                    <div
                                        class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition-colors">
                                        <span class="text-2xl">📥</span>
                                        <p class="font-medium text-green-700">Stok Masuk</p>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type" value="out" x-model="adjustmentType"
                                        class="hidden peer">
                                    <div
                                        class="p-4 border-2 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition-colors">
                                        <span class="text-2xl">📤</span>
                                        <p class="font-medium text-red-700">Stok Keluar</p>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="mb-6">
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah</label>
                            <input type="number" name="quantity" id="quantity" min="1" x-model="quantity"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg"
                                value="{{ old('quantity', 1) }}" required>

                            <!-- Stock Preview -->
                            <div x-show="selectedProduct" class="mt-2 text-sm">
                                <span class="text-gray-600">Stok setelah penyesuaian: </span>
                                <strong :class="newStock < 0 ? 'text-red-600' : 'text-green-600'"
                                    x-text="newStock"></strong>
                                <span x-show="newStock < 0" class="text-red-600 ml-2">⚠️ Stok tidak boleh
                                    negatif!</span>
                            </div>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan /
                                Alasan</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Misal: Barang masuk dari supplier, barang rusak, dll">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.stock.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 underline">Batal</a>
                            <button type="submit" :disabled="!selectedProduct || newStock < 0"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                💾 Simpan Penyesuaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function stockAdjustment() {
                return {
                    products: @json($products),
                    searchQuery: '',
                    selectedProduct: null,
                    adjustmentType: 'in',
                    quantity: 1,
                    filteredProducts: [],

                    filterProducts() {
                        if (this.searchQuery.length < 1) {
                            this.filteredProducts = [];
                            return;
                        }
                        const query = this.searchQuery.toLowerCase();
                        this.filteredProducts = this.products.filter(p =>
                            p.name.toLowerCase().includes(query) ||
                            (p.barcode && p.barcode.includes(query))
                        ).slice(0, 10);
                    },

                    selectProduct(product) {
                        this.selectedProduct = product;
                        this.searchQuery = product.name;
                        this.filteredProducts = [];
                    },

                    clearSelection() {
                        this.selectedProduct = null;
                        this.searchQuery = '';
                    },

                    get newStock() {
                        if (!this.selectedProduct) return 0;
                        const qty = parseInt(this.quantity) || 0;
                        return this.adjustmentType === 'in' ?
                            this.selectedProduct.stock + qty :
                            this.selectedProduct.stock - qty;
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
