<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Category;
use App\Models\Shipping\ShippingCompany;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    public function index(Request $request): View
    {
        $products = $this->productService->searchProducts($request->all())
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('status', 'active')
            ->withCount('products')
            ->orderBy('order')
            ->get();

        return view('frontend.shop.index', compact('products', 'categories'));
    }

    public function show(string $slug): View
    {
        $product = $this->productService->getProductBySlug($slug);
        $related = $this->productService->getRelated($product);
        $relatedProducts = $related;
        $shippingCompanies = ShippingCompany::where('status', 'active')->orderBy('name')->get();

        return view('frontend.shop.show', compact('product', 'related', 'relatedProducts', 'shippingCompanies'));
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->withCount('products')
            ->firstOrFail();

        $products = $this->productService->searchProducts(array_merge($request->all(), ['category_id' => $category->id]))
            ->paginate(12)
            ->withQueryString();

        return view('frontend.shop.category', compact('category', 'products'));
    }
}
