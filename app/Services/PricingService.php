<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductOptionValue;

class PricingService
{
    public function calculateProductPrice(
        Product $product,
        array $optionValues = [],
        ?string $customText = null,
    ): array {
        $base = (float) $product->final_price;
        $optionsAdjustment = 0;
        $optionsSummary = [];

        foreach ($optionValues as $optionId => $valueId) {
            $value = ProductOptionValue::with('option')->find($valueId);
            if ($value && $value->option && $value->option->product_id == $product->id) {
                $optionsAdjustment += (float) $value->price_adjustment;
                $optionsSummary[] = [
                    'option' => $value->option->name,
                    'value' => $value->value,
                    'adjustment' => (float) $value->price_adjustment,
                ];
            }
        }

        $customFieldPrice = 0;
        if ($customText && $product->customFields->count() > 0) {
            $textField = $product->customFields->firstWhere('type', 'text')
                ?? $product->customFields->firstWhere('type', 'textarea');
            if ($textField) {
                $customFieldPrice = (float) $textField->price_effect;
            }
        }

        $unitPrice = $base + $optionsAdjustment + $customFieldPrice;

        return [
            'base_price' => round($base, 2),
            'options_adjustment' => round($optionsAdjustment, 2),
            'options_summary' => $optionsSummary,
            'custom_field_price' => round($customFieldPrice, 2),
            'unit_price' => round($unitPrice, 2),
        ];
    }

    public function calculateSubtotal(float $unitPrice, int $quantity): float
    {
        return round($unitPrice * $quantity, 2);
    }

    public function calculateWeight(float $productWeight, int $quantity): float
    {
        return round($productWeight * $quantity, 3);
    }
}
