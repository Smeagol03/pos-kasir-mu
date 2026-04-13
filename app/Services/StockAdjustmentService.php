<?php

namespace App\Services;

use App\Models\StockAdjustment;
use App\Repositories\ProductRepository;
use App\Repositories\StockAdjustmentRepository;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        protected StockAdjustmentRepository $repository,
        protected ProductRepository $productRepository
    ) {}

    public function getPaginatedAdjustments(int $perPage = 15, ?string $search = null, ?string $type = null)
    {
        return $this->repository->paginate($perPage, $search, $type);
    }

    /**
     * Adjust stock with validation and error handling.
     *
     * @throws \Exception
     */
    public function adjustStock(
        int $productId,
        int $userId,
        string $type,
        int $quantity,
        ?string $notes = null
    ): StockAdjustment {
        return DB::transaction(function () use ($productId, $userId, $type, $quantity, $notes) {
            try {
                $product = $this->productRepository->findOrFail($productId);

                // Validate quantity
                if ($quantity <= 0) {
                    throw new \Exception('Jumlah harus lebih besar dari 0.');
                }

                // Validate type
                if (! in_array($type, ['in', 'out'])) {
                    throw new \Exception('Jenis penyesuaian tidak valid.');
                }

                // Check stock for outgoing adjustment
                if ($type === 'out' && $product->stock < $quantity) {
                    throw new \Exception(
                        "Stok tidak mencukupi untuk dikurangi. Tersedia: {$product->stock}, diminta: {$quantity}"
                    );
                }

                // Record adjustment
                $adjustment = $this->repository->create([
                    'product_id' => $productId,
                    'user_id' => $userId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'notes' => $notes,
                ]);

                // Update product stock
                if ($type === 'in') {
                    $this->productRepository->incrementStock($product, $quantity);
                } else {
                    $this->productRepository->decrementStock($product, $quantity);
                }

                return $adjustment->load(['product', 'user']);
            } catch (\Exception $e) {
                throw new \Exception('Gagal menyesuaikan stok: '.$e->getMessage());
            }
        });
    }
}
