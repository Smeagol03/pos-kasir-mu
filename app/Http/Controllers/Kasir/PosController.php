<?php

namespace App\Http\Controllers\Kasir;

use App\Exceptions\InsufficientCashException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\ActivityLog;
use App\Services\ProductService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected TransactionService $transactionService
    ) {}

    public function index(): View
    {
        // Real-time data without caching
        $products = $this->productService->getAllProducts();

        $todayStats = $this->transactionService->getTodayStats();

        return view('kasir.pos.index', compact('products', 'todayStats'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = $this->productService->searchProducts($query);

        return response()->json($products);
    }

    public function checkout(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->createTransaction(
                $request->user()->id,
                $request->input('items'),
                $request->input('cash')
            );

            // No cache to clear since caching has been removed

            ActivityLog::log(
                'Transaksi',
                "Transaksi berhasil: {$transaction->invoice_code} (Rp ".number_format($transaction->total, 0, ',', '.').')',
                $transaction
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'transaction' => [
                    'id' => $transaction->id,
                    'invoice_code' => $transaction->invoice_code,
                    'total' => $transaction->total,
                    'cash' => $transaction->cash,
                    'change' => $transaction->change,
                ],
            ]);
        } catch (InsufficientStockException $e) {
            // Log the specific stock error
            \Log::channel('errors')->warning('Insufficient stock error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'items' => $request->input('items'),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (InsufficientCashException $e) {
            \Log::channel('errors')->warning('Insufficient cash error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'items' => $request->input('items'),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            // Log the general error
            \Log::channel('errors')->error('POS checkout error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'items' => $request->input('items'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.',
            ], 500);
        }
    }

    public function receipt(int $id): View
    {
        $transaction = $this->transactionService->getTransactionById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        return view('kasir.pos.receipt', compact('transaction'));
    }

    public function receiptByInvoice(string $invoice_code): View
    {
        $transaction = $this->transactionService->getTransactionByInvoiceCode($invoice_code);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        return view('kasir.pos.receipt', compact('transaction'));
    }
}
