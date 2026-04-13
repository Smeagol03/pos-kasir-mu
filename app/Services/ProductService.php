<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(protected ProductRepository $repository) {}

    /**
     * @return Collection<int, Product>
     */
    public function getAllProducts(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginatedProducts(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $search);
    }

    public function getProductById(int $id): ?Product
    {
        return $this->repository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data): Product
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        $product = $this->repository->create($data);
        
        // No cache to clear since caching has been removed
        
        return $product;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->repository->findOrFail($id);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        }

        $updatedProduct = $this->repository->update($product, $data);
        
        // No cache to clear since caching has been removed
        
        return $updatedProduct;
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->repository->findOrFail($id);

        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $result = $this->repository->delete($product);
        
        // No cache to clear since caching has been removed
        
        return $result;
    }

    /**
     * @return Collection<int, Product>
     */
    public function searchProducts(string $query): Collection
    {
        return $this->repository->search($query);
    }

    public function getTrashedProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateTrashed($perPage);
    }

    public function getTrashedProductById(int $id): ?Product
    {
        return $this->repository->findTrashed($id);
    }

    public function restoreProduct(int $id): ?Product
    {
        return $this->repository->restore($id);
    }

    public function forceDeleteProduct(int $id): bool
    {
        $product = $this->repository->findTrashed($id);

        if (! $product) {
            return false;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return $this->repository->forceDelete($product);
    }
}
