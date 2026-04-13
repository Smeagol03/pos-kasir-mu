<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TransactionRepository
{
    public function __construct(protected Transaction $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['user', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): ?Transaction
    {
        return $this->model->with(['user', 'items.product'])->find($id);
    }

    public function findOrFail(int $id): Transaction
    {
        return $this->model->with(['user', 'items.product'])->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Transaction
    {
        // No cache to clear since caching has been removed
        return $this->model->create($data);
    }

    /**
     * Generate invoice code with pessimistic locking to prevent race conditions.
     */
    public function generateInvoiceCode(): string
    {
        $date = now()->format('Ymd');
        $lastTransaction = $this->model->query()
            ->whereDate('created_at', today()->toDateString())
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($lastTransaction) {
            // Extract sequence from invoice code like INV-20260118-0001
            $parts = explode('-', $lastTransaction->invoice_code);
            if (count($parts) === 3) {
                $sequence = (int) $parts[2] + 1;
            }
        }

        return sprintf('INV-%s-%04d', $date, $sequence);
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getByDateRange(Carbon $start, Carbon $end): Collection
    {
        return $this->model->query()
            ->with(['user', 'items.product'])
            ->whereBetween('created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()], 'and', false)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getTodayTotal(): int
    {
        return (int) $this->model->query()->whereDate('created_at', today()->toDateString())->sum('total');
    }

    public function getTodayCount(): int
    {
        return $this->model->query()->whereDate('created_at', today()->toDateString())->count();
    }

    /**
     * Get revenue data for the last N days with optimized single query.
     *
     * @return array<int, array{date: string, total: int}>
     */
    public function getRevenueLast7Days(int $days = 7): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        
        // Single query with GROUP BY for efficiency
        $results = $this->model->query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayData = $results->get($dateKey);
            $data[] = [
                'date' => $date->format('d M'),
                'total' => (int) ($dayData->total ?? 0),
            ];
        }

        return $data;
    }

    /**
     * @return array{count: int, total: int}
     */
    public function getDailyStats(Carbon $date): array
    {
        $stats = $this->model->query()
            ->whereDate('created_at', $date->toDateString())
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        return [
            'count' => (int) ($stats->count ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ];
    }

    /**
     * @return array{count: int, total: int}
     */
    public function getMonthlyStats(Carbon $date): array
    {
        return [
            'count' => $this->model->query()
                ->whereMonth('created_at', '=', $date->month, 'and')
                ->whereYear('created_at', '=', $date->year, 'and')
                ->count(),
            'total' => (int) $this->model->query()
                ->whereMonth('created_at', '=', $date->month, 'and')
                ->whereYear('created_at', '=', $date->year, 'and')
                ->sum('total'),
        ];
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getRecent(int $limit = 5): Collection
    {
        return $this->model
            ->with(['user'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top selling products with optimized JOIN query.
     *
     * @return Collection<int, object{product_id: int, name: string, total_sold: int, total_revenue: int}>
     */
    public function getTopProducts(int $limit = 5): Collection
    {
        return \App\Models\TransactionItem::query()
            ->select('products.id as product_id', 'products.name')
            ->selectRaw('SUM(transaction_items.quantity) as total_sold, SUM(transaction_items.subtotal) as total_revenue')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function paginateByDateRange(Carbon $start, Carbon $end, int $perPage = 15, ?string $invoiceCode = null): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['user'])
            ->whereBetween('created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()], 'and', false);

        if ($invoiceCode) {
            $query->where('invoice_code', 'like', "%{$invoiceCode}%");
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTotalByDateRange(Carbon $start, Carbon $end): int
    {
        return (int) $this->model->query()
            ->whereBetween('created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()], 'and', false)
            ->sum('total');
    }

    public function findByInvoiceCode(string $invoiceCode): ?Transaction
    {
        return $this->model->with(['user', 'items.product'])->where('invoice_code', $invoiceCode)->first();
    }

    /**
     * Get profit report by date range.
     *
     * @return array{revenue: int, cost: int, profit: int}
     */
    public function getProfitStats(Carbon $start, Carbon $end): array
    {
        $stats = \App\Models\TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->selectRaw('SUM(subtotal) as revenue, SUM(purchase_price * quantity) as cost')
            ->first();

        $revenue = (int) $stats->revenue;
        $cost = (int) $stats->cost;

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $revenue - $cost,
        ];
    }

    public function getCountByDateRange(Carbon $start, Carbon $end): int
    {
        return $this->model->query()
            ->whereBetween('created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->count();
    }

    /**
     * Get daily profit breakdown for a date range with optimized single query.
     *
     * @return array<int, array{date: string, revenue: int, cost: int, profit: int, count: int}>
     */
    public function getDailyProfitBreakdown(Carbon $start, Carbon $end): array
    {
        $stats = \App\Models\TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->selectRaw('DATE(transactions.created_at) as date,
                         SUM(subtotal) as revenue,
                         SUM(purchase_price * quantity) as cost,
                         COUNT(DISTINCT transactions.id) as transaction_count')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return $stats->map(fn ($item) => [
            'date' => Carbon::parse($item->date)->format('d/m/Y'),
            'revenue' => (int) $item->revenue,
            'cost' => (int) $item->cost,
            'profit' => (int) ($item->revenue - $item->cost),
            'count' => (int) $item->transaction_count,
        ])->toArray();
    }

    /**
     * Get detailed transaction items for accounting export.
     */
    public function getTransactionItemsForExport(Carbon $start, Carbon $end): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\TransactionItem::query()
            ->with(['transaction', 'product'])
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->select('transaction_items.*')
            ->orderByDesc('transactions.created_at')
            ->get();
    }
}
