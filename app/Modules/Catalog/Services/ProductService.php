<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function searchProducts(array $filters = []): Builder
    {
        $query = Product::active()
            ->with(['category', 'primaryImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if (! empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['q']}%")
                    ->orWhere('description', 'like', "%{$filters['q']}%")
                    ->orWhere('sku', 'like', "%{$filters['q']}%");
            });
        }

        if (! empty($filters['category_id']) || ! empty($filters['category'])) {
            $categoryId = $filters['category_id'] ?? null;
            if (! $categoryId && ! empty($filters['category'])) {
                $cat = Category::where('slug', $filters['category'])->first();
                $categoryId = $cat?->id;
            }
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['featured'])) {
            $query->featured();
        }

        if (! empty($filters['in_stock'])) {
            $query->inStock();
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['dir'] ?? 'desc';

        $allowedSorts = ['name', 'price', 'created_at', 'stock', 'best_selling'];
        if ($sortBy === 'best_selling') {
            $query->withCount(['orderItems as sales_count'])->reorder()->orderByDesc('sales_count');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query;
    }

    public function getProductBySlug(string $slug): Product
    {
        return Product::active()
            ->with([
                'category',
                'images',
                'options.values',
                'variants',
                'customFields',
                'reviews.user',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getFeatured(int $limit = 8)
    {
        static $memo = [];
        if (isset($memo[$limit])) {
            return $memo[$limit];
        }

        return $memo[$limit] = Product::active()
            ->featured()
            ->with('primaryImage')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRelated(Product $product, int $limit = 4)
    {
        return Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('primaryImage')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
