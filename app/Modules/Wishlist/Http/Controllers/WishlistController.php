<?php

namespace App\Modules\Wishlist\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\WishlistToggleRequest;
use App\Modules\Catalog\Models\Product;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $wishlists = Wishlist::where('user_id', auth()->id())
            ->with(['product.primaryImage', 'product.category', 'product' => fn ($q) => $q->withAvg('reviews', 'rating')])
            ->withCount(['product as product_reviews_count' => fn ($q) => $q->whereHas('reviews')])
            ->latest()
            ->paginate(12);

        return view('frontend.wishlist.index', compact('wishlists'));
    }

    public function store(WishlistToggleRequest $request): JsonResponse
    {
        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'المنتج موجود بالفعل في المفضلة']);
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'message' => 'تمت الإضافة إلى المفضلة']);
    }

    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('wishlist.removed'));
    }
}
