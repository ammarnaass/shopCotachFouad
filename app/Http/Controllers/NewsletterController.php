<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\NewsletterSubscribeRequest;
use App\Models\Content\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request): RedirectResponse
    {
        NewsletterSubscriber::firstOrCreate(
            ['email' => $request->email],
            ['status' => 'active', 'subscribed_at' => now()]
        );

        return back()->with('success', __t('footer.newsletter_thanks'));
    }
}
