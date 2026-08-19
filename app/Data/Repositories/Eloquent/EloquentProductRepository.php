<?php

namespace App\Data\Repositories\Eloquent;

use App\Data\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductRepository extends BaseEloquentRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function search(array $filters): Builder
    {
        $query = $this->model->newQuery();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['featured'])) {
            $query->where('featured', $filters['featured']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        return $query;
    }
}
