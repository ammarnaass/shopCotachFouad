<?php

namespace App\Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\CMS\Models\Slide;
use App\Modules\Catalog\Services\ProductService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    public function index(): View
    {
        $featuredProducts = $this->productService->getFeatured(8);

        $latestProducts = Product::active()
            ->with('primaryImage')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->limit(8)
            ->get();

        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with(['children'])
            ->withCount('products')
            ->limit(8)
            ->get();

        $slides = Cache::remember('home.active_sliders', now()->addMinutes(10), function () {
            return Slide::visible()
                ->ordered()
                ->get()
                ->toArray();
        });

        return view('frontend.home', compact('featuredProducts', 'latestProducts', 'categories', 'slides'));
    }
}
