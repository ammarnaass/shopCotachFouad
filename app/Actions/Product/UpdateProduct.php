<?php

namespace App\Actions\Product;

use App\Data\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(Product $product, array $data, ?array $options = null, ?array $customFields = null, ?array $shippingRules = null): Product
    {
        return DB::transaction(function () use ($product, $data, $options, $customFields, $shippingRules) {
            $productData = collect($data)->only([
                'category_id', 'name', 'name_en', 'name_fr',
                'description', 'description_en', 'description_fr',
                'short_description', 'short_description_en', 'short_description_fr',
                'price', 'sale_price', 'sku', 'stock', 'type', 'status', 'featured',
                'shipping_company_id', 'seo_title', 'seo_description',
            ])->toArray();

            $productData['weight'] = $data['weight'] ?? 0;

            $this->products->update($product->id, $productData);

            $this->syncOptionsAndCustomFields($product, $options ?? [], $customFields ?? []);
            $this->syncShippingRules($product, $shippingRules ?? []);

            return $product->fresh();
        });
    }

    private function syncOptionsAndCustomFields(Product $product, array $optionsInput, array $customFieldsInput): void
    {
        $product->options()->delete();

        foreach ($optionsInput as $index => $opt) {
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

        foreach ($customFieldsInput as $cf) {
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

    private function syncShippingRules(Product $product, array $input): void
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
