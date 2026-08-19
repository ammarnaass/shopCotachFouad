<?php

namespace App\Modules\Coupons\Services;

use App\Data\Repositories\Contracts\CouponRepositoryInterface;
use App\Modules\Coupons\Models\Coupon;

class CouponService
{
    public function __construct(
        private CouponRepositoryInterface $coupons,
    ) {}

    public function validate(?string $code, float $subtotal): ?array
    {
        if (!$code) {
            return null;
        }

        $coupon = $this->coupons->findByCode($code);

        if (!$coupon) {
            return null;
        }

        if (!$coupon->isValid($subtotal)) {
            return null;
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'coupon' => $coupon,
            'discount' => round($discount, 2),
        ];
    }

    public function apply(?string $code, float $subtotal): array
    {
        $result = $this->validate($code, $subtotal);

        if (!$result) {
            return ['coupon' => null, 'discount' => 0, 'coupon_id' => null];
        }

        return [
            'coupon' => $result['coupon'],
            'discount' => $result['discount'],
            'coupon_id' => $result['coupon']->id,
        ];
    }

    public function incrementUsage(?Coupon $coupon): void
    {
        if ($coupon) {
            $this->coupons->incrementUsage($coupon);
        }
    }
}
