<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Today's Transactions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $todayStats['count'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">🧾</span>
                        </div>
                    </div>
                </div>

                <!-- Today's Revenue -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pendapatan Hari Ini</p>
                            <p class="text-2xl font-bold text-green-600">Rp
                                {{ number_format($todayStats['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">💰</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Transactions -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Transaksi Bulan Ini</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $monthStats['count'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
                            <p class="text-2xl font-bold text-green-600">Rp
                                {{ number_format($monthStats['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📈</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <h3 class="font-semibold text-gray-900 mb-4">📈 Pendapatan 7 Hari Terakhir</h3>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Transactions -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Transaksi Terbaru</h3>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.transactions.index') }}"
                                    class="text-sm text-indigo-600 hover:underline">Lihat Semua →</a>
                            @endif
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentTransactions as $transaction)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $transaction->invoice_code }}</p>
                                    <p class="text-sm text-gray-500">{{ $transaction->user->name ?? 'N/A' }} •
                                        {{ $transaction->created_at->format('d M H:i') }}</p>
                                </div>
                                <p class="font-bold text-green-600">Rp
                                    {{ number_format($transaction->total, 0, ',', '.') }}</p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <p class="text-4xl mb-2">📭</p>
                                <p>Belum ada transaksi</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Top Products -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Produk Terlaris</h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($topProducts as $item)
                                <div class="p-4 flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item->name ?? 'Deleted' }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->total_sold }} terjual</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">Rp
                                        {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500 text-sm">Belum ada data</div>
                            @endforelse
                        </div>
                        @if ($topProducts->count() > 0)
                            <div class="p-4 border-t border-gray-100">
                                <canvas id="topProductsChart" class="max-h-48"></canvas>
                            </div>
                        @endif
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">⚠️ Stok Menipis</h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($lowStockProducts as $product)
                                <div class="p-4 flex items-center justify-between">
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full {{ $product->stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $product->stock }} tersisa
                                    </span>
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500 text-sm">Semua stok aman ✅</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('kasir.pos.index') }}"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    🛒 Buka POS Kasir
                </a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.products.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors border border-gray-200">
                        📦 Kelola Produk
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors border border-gray-200">
                        📋 Riwayat Transaksi
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors border border-gray-200">
                        👥 Kelola User
                    </a>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const revenueData = @json($revenueChartData);
                const labels = revenueData.map(item => item.date);
                const data = revenueData.map(item => item.total);

                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: data,
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });

                // Top Products Pie Chart
                const topProductsChartEl = document.getElementById('topProductsChart');
                if (topProductsChartEl) {
                    const topProducts = @json($topProducts);
                    const productLabels = topProducts.map(item => item.name || 'Deleted');
                    const productData = topProducts.map(item => item.total_sold);

                    new Chart(topProductsChartEl, {
                        type: 'doughnut',
                        data: {
                            labels: productLabels,
                            datasets: [{
                                data: productData,
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(245, 158, 11, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(139, 92, 246, 0.8)',
                                ],
                                borderWidth: 0,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 8,
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
