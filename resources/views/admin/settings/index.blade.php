<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Toko') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Store Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold border-b pb-2">Identitas Toko</h3>

                                <div>
                                    <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                        Toko *</label>
                                    <input type="text" name="store_name" id="store_name"
                                        value="{{ old('store_name', $settings['store_name']) }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('store_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="store_phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor
                                        Telepon</label>
                                    <input type="text" name="store_phone" id="store_phone"
                                        value="{{ old('store_phone', $settings['store_phone']) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('store_phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="store_address"
                                        class="block text-sm font-medium text-gray-700 mb-1">Alamat Toko</label>
                                    <textarea name="store_address" id="store_address" rows="3"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('store_address', $settings['store_address']) }}</textarea>
                                    @error('store_address')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Receipt Settings -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold border-b pb-2">Pengaturan Struk</h3>

                                <div>
                                    <label for="receipt_footer"
                                        class="block text-sm font-medium text-gray-700 mb-1">Pesan Footer Struk</label>
                                    <textarea name="receipt_footer" id="receipt_footer" rows="3"
                                        placeholder="Contoh: Terima kasih atas kunjungan Anda!"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                                    <p class="text-xs text-gray-500 mt-1">Pesan ini akan muncul di bagian paling bawah
                                        struk belanja.</p>
                                    @error('receipt_footer')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
