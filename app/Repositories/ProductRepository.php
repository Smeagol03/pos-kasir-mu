<?php

namespace App\Repositories;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function __construct(protected Product $model) {}

    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection
    {
        return $this->model->orderBy('name', 'asc')->get();
    }

    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();
    }

    public function find(int $id): ?Product
    {
        return $this->model->find($id, ['*']);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->findOrFail($id, ['*']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        // No cache to clear since caching has been removed
        
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->fill($data);
        $product->save();

        // No cache to clear since caching has been removed
        
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        // No cache to clear since caching has been removed
        
        return (bool) $product->delete();
    }

    /**
     * @return Collection<int, Product>
     */
    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%", 'and')
            ->orWhere('barcode', '=', $query)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Decrement product stock.
     *
     * @throws InsufficientStockException
     */
    public function decrementStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new InsufficientStockException($product->name, $product->stock, $quantity);
        }

        $product->decrement('stock', $quantity);

        // Check if stock falls below threshold after decrement
        $newStock = $product->refresh()->stock;
        if ($newStock <= config('app.low_stock_threshold', 10)) {
            // Send low stock notification using batch send for efficiency
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            if ($adminUsers->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($adminUsers, new \App\Notifications\LowStockAlert($product));
            }
        }

        // No cache to clear since caching has been removed
    }

    public function incrementStock(Product $product, int $quantity): void
    {
        $product->increment('stock', $quantity);

        // No cache to clear since caching has been removed
    }

    /**
     * @return Collection<int, Product>
     */
    public function getLowStock(int $threshold = 10, int $limit = 5): Collection
    {
        return $this->model
            ->where('stock', '<=', $threshold, 'and')
            ->where('stock', '>', 0, 'and')
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get();
    }

    public function paginateTrashed(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->onlyTrashed()->orderBy('deleted_at', 'desc')->paginate($perPage);
    }

    public function findTrashed(int $id): ?Product
    {
        return $this->model->onlyTrashed()->find($id, ['*']);
    }

    public function restore(int $id): ?Product
    {
        $product = $this->findTrashed($id);

        if ($product) {
            $product->restore();

            // No cache to clear since caching has been removed
            
            return $product->fresh();
        }

        return null;
    }

    public function forceDelete(Product $product): bool
    {
        // No cache to clear since caching has been removed

        return $product->forceDelete();
    }
}
