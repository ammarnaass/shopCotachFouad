<?php

namespace App\Actions\Order;

use App\Data\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\OrderService;

class BulkOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderService $orderService,
    ) {}

    public function execute(array $orderIds, string $action): int
    {
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (! in_array($action, $validStatuses)) {
            throw new \Exception('إجراء غير صالح');
        }

        return $this->orders->bulkUpdateStatus($orderIds, $action);
    }
}
