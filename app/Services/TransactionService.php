<?php

namespace App\Services;

use App\Exceptions\InsufficientCashException;
use App\Exceptions\InsufficientStockException;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected TransactionRepository $transactionRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Process a sale transaction with pessimistic locking to prevent race conditions.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     *
     * @throws \Exception
     */
    public function createTransaction(int $userId, array $items, int $cash): Transaction
    {
        return DB::transaction(function () use ($userId, $items, $cash) {
            $total = 0;
            $transactionItems = [];
            $productsToLock = [];

            // Collect all product IDs
            foreach ($items as $item) {
                $productsToLock[] = $item['product_id'];
            }

            // Lock all products at once for validation (pessimistic locking)
            $products = Product::query()
                ->whereIn('id', array_unique($productsToLock))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate and calculate
            foreach ($items as $item) {
                $productId = $item['product_id'];

                if (! $products->has($productId)) {
                    throw new \Exception("Produk dengan ID {$productId} tidak ditemukan.");
                }

                $product = $products->get($productId);

                if ($product->stock < $item['quantity']) {
                    throw new InsufficientStockException($product->name, $product->stock, $item['quantity']);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $transactionItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'purchase_price' => $product->purchase_price,
                    'subtotal' => $subtotal,
                ];
            }

            if ($cash < $total) {
                throw new InsufficientCashException($total, $cash);
            }

            // Create transaction
            try {
                $transaction = $this->transactionRepository->create([
                    'invoice_code' => $this->transactionRepository->generateInvoiceCode(),
                    'user_id' => $userId,
                    'total' => $total,
                    'cash' => $cash,
                    'change' => $cash - $total,
                ]);
            } catch (\Exception $e) {
                throw new \Exception('Gagal membuat transaksi: '.$e->getMessage());
            }

            // Create items and reduce stock
            foreach ($transactionItems as $item) {
                $transaction->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'purchase_price' => $item['purchase_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                try {
                    $this->productRepository->decrementStock($item['product'], $item['quantity']);
                } catch (\Exception $e) {
                    throw new \Exception('Gagal mengurangi stok: '.$e->getMessage());
                }
            }

            // No cache to clear since caching has been removed

            // Log the activity inside the transaction to ensure consistency
            ActivityLog::log(
                'Transaksi POS',
                "Transaksi baru: {$transaction->invoice_code} (Total: Rp ".number_format($transaction->total, 0, ',', '.').')',
                $transaction
            );

            return $transaction->load(['items.product', 'user']);
        });
    }

    public function getTransactionById(int $id): ?Transaction
    {
        return $this->transactionRepository->find($id);
    }

    public function getPaginatedTransactions(int $perPage = 15)
    {
        return $this->transactionRepository->paginate($perPage);
    }

    /**
     * @return array{total: int, count: int}
     */
    public function getTodayStats(): array
    {
        return [
            'total' => $this->transactionRepository->getTodayTotal(),
            'count' => $this->transactionRepository->getTodayCount(),
        ];
    }

    public function getTransactionByInvoiceCode(string $invoiceCode): ?Transaction
    {
        return $this->transactionRepository->findByInvoiceCode($invoiceCode);
    }
}
