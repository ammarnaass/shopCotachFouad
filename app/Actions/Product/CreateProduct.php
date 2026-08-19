<?php

namespace App\Actions\Product;

use App\Data\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private ImageUploadService $images,
    ) {}

    public function execute(array $data, ?array $images = null, ?array $options = null, ?array $customFields = null, ?array $shippingRules = null): Product
    {
        return DB::transaction(function () use ($data, $images, $options, $customFields, $shippingRules) {
            $productData = collect($data)->only([
                'category_id', 'name', 'name_en', 'name_fr',
                'description', 'description_en', 'description_fr',
                'short_description', 'short_description_en', 'short_description_fr',
                'price', 'sale_price', 'sku', 'stock', 'type', 'status', 'featured',
                'shipping_company_id', 'seo_title', 'seo_description',
            ])->toArray();

            $productData['weight'] = $data['weight'] ?? 0;

            $product = $this->products->create($productData);

            if ($images) {
                $this->storeImages($product, $images);
            }

            $this->syncOptionsAndCustomFields($product, $options ?? [], $customFields ?? []);
            $this->syncShippingRules($product, $shippingRules ?? []);

            return $product;
        });
    }

    private function storeImages(Product $product, array $images): void
    {
        $hasPrimary = $product->primaryImage()->exists();
        $maxOrder = $product->images()->max('order') ?? 0;

        foreach ($images as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $path = "products/{$product->id}/".Str::random(20).".{$ext}";

            $dir = dirname($path);
            if (! Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir, 0755, true);
            }

            try {
                $file->storeAs($dir, basename($path), 'public');
            } catch (\Throwable $e) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'is_primary' => ! $hasPrimary && $index === 0,
                'order' => ++$maxOrder,
            ]);
        }
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
