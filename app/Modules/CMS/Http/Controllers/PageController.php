<?php

namespace App\Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\Page;
use App\Modules\Orders\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
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

    public function states(string $code): JsonResponse
    {
        $code = strtoupper($code);
        $countries = config('ecommerce.countries', []);
        $states = $countries[$code]['states'] ?? [];
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
