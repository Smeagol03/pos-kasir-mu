<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StockAdjustment;
use App\Services\ProductService;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockAdjustmentService $stockService,
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $type = $request->get('type');

        $adjustments = $this->stockService->getPaginatedAdjustments(15, $search, $type);

        return view('admin.stock.index', compact('adjustments', 'search', 'type'));
    }

    public function create(): View
    {
        $products = $this->productService->getAllProducts();

        return view('admin.stock.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1|max:999999',
            'notes' => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'type.required' => 'Jenis penyesuaian wajib dipilih.',
            'type.in' => 'Jenis penyesuaian tidak valid.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah terlalu besar.',
        ]);

        try {
            $adjustment = $this->stockService->adjustStock(
                $validated['product_id'],
                Auth::id() ?? 0,
                $validated['type'],
                $validated['quantity'],
                $validated['notes'] ?? null
            );

            ActivityLog::log(
                'Adjustment Stok',
                "Penyesuaian stok {$adjustment->type}: {$adjustment->product->name} ({$adjustment->quantity})",
                $adjustment->product
            );

            return redirect()->route('admin.stock.index')->with('success', 'Stok berhasil disesuaikan.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Stock adjustment error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'product_id' => $validated['product_id'],
            ]);

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $search = $request->get('search');
        $type = $request->get('type');

        $query = StockAdjustment::with(['product', 'user'])->orderByDesc('created_at');

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $adjustments = $query->get();
        $filename = 'stock_adjustments_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($adjustments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'User', 'Produk', 'Jenis', 'Jumlah', 'Catatan']);

            foreach ($adjustments as $adj) {
                fputcsv($handle, [
                    $adj->created_at->format('d/m/Y H:i'),
                    $adj->user->name ?? 'N/A',
                    $adj->product->name ?? 'N/A',
                    $adj->type === 'in' ? 'Masuk' : 'Keluar',
                    $adj->quantity,
                    $adj->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
