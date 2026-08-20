<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->searchProducts($request->all())
            ->paginate($request->get('per_page', 15));

        $symbol = currentCurrencySymbol();
        $items = collect($products->items())->map(function ($p) use ($symbol) {
            $img = $p->primaryImage?->image ?? $p->image;
            $imageUrl = $img
                ? ((str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) ? $img : asset('storage/' . $img))
                : null;

            $price = (float) ($p->final_price ?? $p->price ?? 0);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'sku' => $p->sku,
                'price' => $price,
                'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
                'formatted_price' => number_format(convertPrice($price), 0) . ' ' . $symbol,
                'stock' => (int) $p->stock,
                'in_stock' => (int) $p->stock > 0,
                'image_url' => $imageUrl,
                'category_name' => $p->category?->name,
                'url' => route('shop.show', ['slug' => $p->slug ?: $p->id]),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->getProductBySlug($slug);
        return response()->json(['success' => true, 'data' => $product]);
    }
}
