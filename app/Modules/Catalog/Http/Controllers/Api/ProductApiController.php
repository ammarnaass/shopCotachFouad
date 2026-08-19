<?php

namespace App\Modules\Catalog\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->searchProducts($request->all())
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products->items(),
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
