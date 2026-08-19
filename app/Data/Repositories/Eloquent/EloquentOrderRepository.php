<?php

namespace App\Data\Repositories\Eloquent;

use App\Data\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order\Order;

class EloquentOrderRepository extends BaseEloquentRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id, array $relations = []): ?Order
    {
        $relations = $relations ?: ['items', 'shippingAddress', 'payment', 'user', 'coupon', 'notes', 'statusHistory'];

        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function getStats(): array
    {
        $query = $this->model->newQuery();

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'total_revenue' => (clone $query)->where('payment_status', 'paid')->sum('grand_total'),
        ];
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }
}
