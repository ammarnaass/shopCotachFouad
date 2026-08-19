<?php
// Bootstrap Laravel and check product lookup
require_once 'C:/Users/amarn/ecommerce/vendor/autoload.php';
$app = require_once 'C:/Users/amarn/ecommerce/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $product = \App\Models\Catalog\Product::active()
        ->with(['category', 'images', 'options.values', 'variants', 'customFields', 'reviews.user'])
        ->where('slug', 'classic-jeans')
        ->firstOrFail();
    echo "Product found: {$product->id} - {$product->name}\n";
    echo "Status: {$product->status}\n";
    echo "Images count: " . $product->images->count() . "\n";
    echo "Category: " . ($product->category?->name ?? 'none') . "\n";
} catch (\Exception $e) {
    echo "Exception: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
