<?php

namespace App\Actions\Product;

use App\Models\Catalog\Product;

class SyncProductOptions
{
    public function execute(Product $product, array $options = [], array $customFields = []): void
    {
        $product->options()->delete();

        foreach ($options as $index => $opt) {
            if (empty($opt['name'])) {
                continue;
            }

            $option = $product->options()->create([
                'name' => $opt['name'],
                'type' => $opt['type'] ?? 'select',
                'required' => filter_var($opt['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'order' => $index,
            ]);

            if (! empty($opt['values']) && is_array($opt['values'])) {
                foreach ($opt['values'] as $val) {
                    if (! isset($val['value']) || $val['value'] === '') {
                        continue;
                    }
                    $option->values()->create([
                        'value' => $val['value'],
                        'color_code' => $val['color_code'] ?? null,
                        'price_adjustment' => (float) ($val['price_adjustment'] ?? 0),
                        'stock' => (int) ($val['stock'] ?? 0),
                    ]);
                }
            }
        }

        $product->customFields()->delete();

        foreach ($customFields as $cf) {
            if (empty($cf['label'])) {
                continue;
            }

            $product->customFields()->create([
                'label' => $cf['label'],
                'type' => $cf['type'] ?? 'text',
                'required' => filter_var($cf['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'price_effect' => (float) ($cf['price_effect'] ?? 0),
            ]);
        }
    }
}
