<?php

namespace App\Data\Repositories\Contracts;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Builder;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function findWithRelations(int $id, array $relations = []): ?Order;

    public function query(): Builder;

    public function create(array $data): Order;

    public function getStats(): array;

    public function bulkUpdateStatus(array $ids, string $status): int;
}
