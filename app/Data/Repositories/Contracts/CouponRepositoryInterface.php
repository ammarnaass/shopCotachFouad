<?php

namespace App\Data\Repositories\Contracts;

use App\Models\Catalog\Coupon;

interface CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon;

    public function incrementUsage(Coupon $coupon): void;
}
