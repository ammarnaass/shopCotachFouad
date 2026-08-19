<?php

namespace App\Modules\Reviews\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reviews\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with('product', 'user');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->rating) {
            $query->where('rating', (int) $request->rating);
        }

        $reviews = $query->latest()->paginate(20);
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'avg_rating' => round(Review::where('status', 'approved')->avg('rating') ?? 0, 1),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $review->update($data);

        return back()->with('success', 'تم تحديث حالة التقييم');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'تم حذف التقييم');
    }
}
