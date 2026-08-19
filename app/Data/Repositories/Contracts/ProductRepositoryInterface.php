<?php

namespace App\Data\Repositories\Contracts;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function query(): Builder;

    public function create(array $data): Product;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function search(array $filters): Builder;
}
