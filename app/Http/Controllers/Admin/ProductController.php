<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $products = $this->productService->getPaginatedProducts(15, $search);

        return view('admin.products.index', compact('products', 'search'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $search = $request->get('search');
        $products = $this->productService->getPaginatedProducts(1000, $search);

        $filename = 'products_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama', 'Barcode', 'Harga Beli', 'Harga Jual', 'Stok']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->name,
                    $product->barcode ?? '-',
                    $product->purchase_price,
                    $product->price,
                    $product->stock,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->createProduct($request->validated());

        ActivityLog::log('Tambah Produk', "Menambahkan produk baru: {$product->name}", $product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $product = $this->productService->getProductById($id);

        if (! $product) {
            abort(404);
        }

        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $product = $this->productService->updateProduct($id, $request->validated());

        ActivityLog::log('Update Produk', "Memperbarui produk: {$product->name}", $product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = $this->productService->getProductById($id);
        if ($product) {
            ActivityLog::log('Hapus Produk', "Menghapus produk: {$product->name}", $product);
            $this->productService->deleteProduct($id);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function trashed(): View
    {
        $products = $this->productService->getTrashedProducts(15);

        return view('admin.products.trashed', compact('products'));
    }

    public function restore(int $id): RedirectResponse
    {
        $product = $this->productService->restoreProduct($id);

        if ($product) {
            ActivityLog::log('Restore Produk', "Memulihkan produk: {$product->name}", $product);
        }

        return redirect()
            ->route('admin.products.trashed')
            ->with('success', 'Produk berhasil dipulihkan.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $product = $this->productService->getTrashedProductById($id);

        if ($product) {
            ActivityLog::log('Hapus Permanen Produk', "Menghapus permanen produk: {$product->name}", $product);
            $this->productService->forceDeleteProduct($id);
        }

        return redirect()
            ->route('admin.products.trashed')
            ->with('success', 'Produk berhasil dihapus permanen.');
    }
}
