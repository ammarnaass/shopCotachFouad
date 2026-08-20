<?php

namespace App\Http\Controllers;

use App\Models\Content\Page;
use App\Models\Order\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Static pages rendered from DB.
     */
    public function show(string $slug): View
    {
        $pageModel = Page::where('slug', $slug)->where('is_active', true)->first();

        if (! $pageModel) {
            abort(404, 'الصفحة غير موجودة');
        }

        $page = $pageModel->toArray();
        $page['sections'] = json_decode($page['content'] ?? '[]', true) ?: [];

        return view('frontend.page', [
            'page' => $page,
            'slug' => $slug,
        ]);
    }

    /**
     * Handle contact form submissions.
     */
    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => __t('contact.name_required', [], 'يرجى إدخال الاسم الكامل'),
            'email.required' => __t('contact.email_required', [], 'يرجى إدخال البريد الإلكتروني'),
            'email.email' => __t('contact.email_invalid', [], 'يرجى إدخال بريد إلكتروني صحيح'),
            'message.required' => __t('contact.message_required', [], 'يرجى كتابة نص الرسالة'),
        ]);

        return back()->with('success', __t('contact.success_message', [], 'شكراً لتواصلك معنا! تم استلام رسالتك وسنقوم بالرد عليك في أقرب وقت.'));
    }

    /**
     * Track an order by order_number + email/phone.
     */
    public function track(Request $request): View
    {
        $order = null;
        $error = null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'order_number' => 'required|string',
                'contact' => 'required|string',
            ], [
                'order_number.required' => 'رقم الطلب مطلوب',
                'contact.required' => 'البريد أو الهاتف مطلوب',
            ]);

            $order = Order::with('items', 'shippingAddress')
                ->where('order_number', $data['order_number'])
                ->where(function ($q) use ($data) {
                    $q->whereHas('user', function ($u) use ($data) {
                        $u->where('email', $data['contact'])->orWhere('phone', $data['contact']);
                    })
                        ->orWhere('guest_email', $data['contact'])
                        ->orWhere('guest_phone', $data['contact']);
                })
                ->first();

            if (! $order) {
                $error = 'لم يتم العثور على طلب بهذه البيانات. تحقق من رقم الطلب وعنوان البريد/الهاتف.';
            }
        }

        return view('frontend.track', [
            'order' => $order,
            'error' => $error,
            'orderNumber' => $request->input('order_number'),
            'contact' => $request->input('contact'),
        ]);
    }

    /**
     * Return states for a given country code (used by instant-buy form).
     */
    public function states(string $code): JsonResponse
    {
        $code = strtoupper($code);
        $countries = config('ecommerce.countries', []);
        $states = $countries[$code]['states'] ?? [];
        // Normalize to [{code, name}]
        $normalized = [];
        foreach ($states as $key => $val) {
            if (is_array($val)) {
                $normalized[] = ['code' => $val['code'] ?? $key, 'name' => $val['name'] ?? $key];
            } else {
                $normalized[] = ['code' => $key, 'name' => (string) $val];
            }
        }

        return response()->json(['states' => $normalized]);
    }
}
