<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.products.update', $product) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk
                                *</label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', $product->name) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="purchase_price_display"
                                    class="block text-sm font-medium text-gray-700 mb-1">Harga Beli / Modal (Rp)
                                    *</label>
                                <input type="text" id="purchase_price_display" inputmode="numeric"
                                    value="{{ number_format(old('purchase_price', $product->purchase_price), 0, ',', '.') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 price-input"
                                    data-target="purchase_price" required>
                                <input type="hidden" name="purchase_price" id="purchase_price"
                                    value="{{ old('purchase_price', $product->purchase_price) }}">
                                @error('purchase_price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="price_display" class="block text-sm font-medium text-gray-700 mb-1">Harga
                                    Jual (Rp) *</label>
                                <input type="text" id="price_display" inputmode="numeric"
                                    value="{{ number_format(old('price', $product->price), 0, ',', '.') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 price-input"
                                    data-target="price" required>
                                <input type="hidden" name="price" id="price"
                                    value="{{ old('price', $product->price) }}">
                                @error('price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                            <input type="number" name="stock" id="stock"
                                value="{{ old('stock', $product->stock) }}" min="0" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('stock')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <input type="text" name="barcode" id="barcode"
                                value="{{ old('barcode', $product->barcode) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('barcode')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                            @if ($product->image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                        class="h-20 w-20 object-cover rounded">
                                </div>
                            @endif
                            <input type="file" name="image" id="image" accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('admin.products.index') }}"
                                class="px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.price-input').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    let numValue = parseInt(value) || 0;

                    // Update hidden input
                    const targetId = this.dataset.target;
                    document.getElementById(targetId).value = numValue;

                    // Format display with dots
                    this.value = numValue.toLocaleString('id-ID');
                });

                input.addEventListener('focus', function() {
                    if (this.value === '0') {
                        this.value = '';
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.value = '0';
                        document.getElementById(this.dataset.target).value = 0;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
