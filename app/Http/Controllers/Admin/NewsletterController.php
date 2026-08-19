<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsletterSubscriber::query();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('email', 'like', '%'.$request->search.'%');
        }

        $subscribers = $query->latest()->paginate(25);
        $totalSubscribers = NewsletterSubscriber::count();
        $activeCount = NewsletterSubscriber::where('status', 'active')->count();
        $unsubscribedCount = NewsletterSubscriber::where('status', 'unsubscribed')->count();

        return view('admin.newsletter.index', compact('subscribers', 'totalSubscribers', 'activeCount', 'unsubscribedCount'));
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->route('admin.newsletter.index')->with('success', __t('admin.newsletter.deleted'));
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $ids = $request->input('selected', []);
        if (! empty($ids)) {
            NewsletterSubscriber::destroy($ids);
        }

        return redirect()->route('admin.newsletter.index')->with('success', __t('admin.newsletter.deleted_selected'));
    }

    public function export()
    {
        $subscribers = NewsletterSubscriber::where('status', 'active')->get(['email', 'subscribed_at']);
        $csv = "Email,Subscribed At\n";
        foreach ($subscribers as $s) {
            $csv .= $s->email.','.$s->subscribed_at."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="newsletter-subscribers.csv"',
        ]);
    }
}
