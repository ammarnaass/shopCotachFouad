<?php

namespace App\Data\Repositories\Eloquent;

use App\Data\Repositories\Contracts\CouponRepositoryInterface;
use App\Models\Catalog\Coupon;

class EloquentCouponRepository extends BaseEloquentRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->first();
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
