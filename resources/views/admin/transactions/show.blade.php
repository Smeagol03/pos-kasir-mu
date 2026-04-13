<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.transactions.index') }}" class="text-gray-500 hover:text-gray-700">
                ← Kembali
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $transaction->invoice_code }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Info -->
                <div class="p-6 border-b border-gray-100">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Invoice</p>
                            <p class="font-bold text-indigo-600">{{ $transaction->invoice_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal</p>
                            <p class="font-medium">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kasir</p>
                            <p class="font-medium">{{ $transaction->user->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span
                                class="inline-flex px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">Sukses</span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Detail Item</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($transaction->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            {{ $item->product->name ?? 'Deleted Product' }}</td>
                                        <td class="px-4 py-3 text-sm text-center">{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-sm text-right">Rp
                                            {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold">Rp
                                            {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="p-6 bg-gray-50 border-t border-gray-100">
                    <div class="max-w-xs ml-auto space-y-2">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-indigo-600">Rp
                                {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tunai</span>
                            <span>Rp {{ number_format($transaction->cash, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Kembalian</span>
                            <span class="text-green-600 font-semibold">Rp
                                {{ number_format($transaction->change, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-6 border-t border-gray-100 flex gap-4">
                    <a href="{{ route('kasir.pos.receipt', $transaction->id) }}" target="_blank"
                        class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                        🖨️ Cetak Struk
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
