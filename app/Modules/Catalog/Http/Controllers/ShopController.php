<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Shipping\Models\ShippingCompany;
use App\Modules\Catalog\Services\ProductService;
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
        $companyClass = class_exists(ShippingCompany::class) ? ShippingCompany::class : \App\Models\Shipping\ShippingCompany::class;
        $shippingCompanies = $companyClass::where('status', 'active')->orderBy('name')->get();

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
