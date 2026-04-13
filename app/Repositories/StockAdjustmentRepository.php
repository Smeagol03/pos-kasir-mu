<?php

namespace App\Repositories;

use App\Models\StockAdjustment;
use Illuminate\Pagination\LengthAwarePaginator;

class StockAdjustmentRepository
{
    public function __construct(protected StockAdjustment $model) {}

    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator
    {
        $query = $this->model->with(['product', 'user'])->orderByDesc('created_at');

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): StockAdjustment
    {
        return $this->model->create($data);
    }
}
