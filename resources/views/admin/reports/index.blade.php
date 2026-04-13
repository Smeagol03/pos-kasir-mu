<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4 justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan Keuangan') }}
            </h2>
            <a href="{{ route('admin.reports.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Total Transaksi</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($transactionCount) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Omzet (Pendapatan)</p>
                    <p class="text-3xl font-bold text-blue-600">Rp
                        {{ number_format($profitStats['revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Modal (HPP)</p>
                    <p class="text-3xl font-bold text-red-600">Rp {{ number_format($profitStats['cost'], 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Laba Bersih</p>
                    <p class="text-3xl font-bold {{ $profitStats['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($profitStats['profit'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Rata-rata Transaksi</p>
                    <p class="text-2xl font-bold text-indigo-600">Rp {{ number_format($avgTransactionValue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Hari Terbaik</p>
                    <p class="text-2xl font-bold text-green-600">{{ $bestDay ? 'Rp ' . number_format($bestDay['revenue'], 0, ',', '.') : '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $bestDay ? $bestDay['date'] : '-' }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider">Hari Terburuk</p>
                    <p class="text-2xl font-bold text-red-600">{{ $worstDay ? 'Rp ' . number_format($worstDay['revenue'], 0, ',', '.') : '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $worstDay ? $worstDay['date'] : '-' }}</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">📈 Grafik Pendapatan Harian</h3>
                    <div class="h-80">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Profit Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">💰 Grafik Laba Harian</h3>
                    <div class="h-80">
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">📆 Rincian Harian</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Transaksi</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Pendapatan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Modal
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dailyBreakdown as $day)
                                    @if ($day['count'] > 0)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $day['date'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $day['count'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-blue-600">Rp
                                                {{ number_format($day['revenue'], 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-red-600">Rp
                                                {{ number_format($day['cost'], 0, ',', '.') }}</td>
                                            <td
                                                class="px-4 py-3 text-sm text-right font-medium {{ $day['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                Rp {{ number_format($day['profit'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">🏆 Top 10 Produk Terlaris</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Terjual
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topProducts as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $item->name }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                                            {{ number_format($item->total_sold) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-green-600">Rp
                                            {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Prepare data for charts
                const allDailyBreakdown = @json($dailyBreakdown);
                const dailyBreakdown = allDailyBreakdown.filter(function(day) { return day.count > 0; });
                const dates = dailyBreakdown.map(item => item.date);
                const revenues = dailyBreakdown.map(item => item.revenue);
                const profits = dailyBreakdown.map(item => item.profit);

                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Pendapatan Harian (Rp)',
                            data: revenues,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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

                // Profit Chart
                const profitCtx = document.getElementById('profitChart').getContext('2d');
                new Chart(profitCtx, {
                    type: 'bar',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Laba Harian (Rp)',
                            data: profits,
                            backgroundColor: function(context) {
                                const value = context.dataset.data[context.dataIndex];
                                return value >= 0 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(239, 68, 68, 0.7)';
                            },
                            borderColor: function(context) {
                                const value = context.dataset.data[context.dataIndex];
                                return value >= 0 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)';
                            },
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
