<?php

namespace App\Modules\Shipping\Services;

/**
 * نتيجة احتساب شحن — immutable، لضمان ما يصير تعديل عرضي على السعر بعد احتسابه.
 */
final class ShippingQuote
{
    public function __construct(
        public readonly int $zoneId,
        public readonly string $zoneName,
        public readonly int $methodId,
        public readonly string $methodCode,
        public readonly string $methodName,
        public readonly float $cost,
        public readonly ?int $minDeliveryDays,
        public readonly ?int $maxDeliveryDays,
        public readonly bool $isFreeShipping,
    ) {
    }

    public function toArray(): array
    {
        return [
            'zone_id' => $this->zoneId,
            'zone_name' => $this->zoneName,
            'method_id' => $this->methodId,
            'method_code' => $this->methodCode,
            'method_name' => $this->methodName,
            'cost' => round($this->cost, 2),
            'min_delivery_days' => $this->minDeliveryDays,
            'max_delivery_days' => $this->maxDeliveryDays,
            'is_free_shipping' => $this->isFreeShipping,
        ];
    }
}
