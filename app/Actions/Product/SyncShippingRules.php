<?php

namespace App\Actions\Product;

use App\Models\Catalog\Product;

class SyncShippingRules
{
    public function execute(Product $product, array $input = []): void
    {
        if (empty($input)) {
            $product->shippingRule()?->delete();

            return;
        }

        $product->shippingRule()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'max_weight' => ! empty($input['max_weight']) ? $input['max_weight'] : null,
                'priority' => (int) ($input['priority'] ?? 0),
                'fragile' => filter_var($input['fragile'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'hazardous' => filter_var($input['hazardous'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'requires_signature' => filter_var($input['requires_signature'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }
}
