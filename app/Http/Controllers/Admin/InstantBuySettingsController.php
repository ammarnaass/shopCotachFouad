<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstantBuy\InstantBuyOrder;
use App\Models\InstantBuy\InstantBuySetting;
use Illuminate\Http\Request;

class InstantBuySettingsController extends Controller
{
    public function index()
    {
        $settings = InstantBuySetting::firstOrCreate([], []);
        $orders = InstantBuyOrder::latest()->take(20)->get();
        $stats = [
            'total' => InstantBuyOrder::count(),
            'new' => InstantBuyOrder::where('status', 'new')->count(),
            'confirmed' => InstantBuyOrder::where('status', 'confirmed')->count(),
            'cancelled' => InstantBuyOrder::where('status', 'cancelled')->count(),
        ];

        return view('admin.instant-buy.settings', compact('settings', 'orders', 'stats'));
    }

    public function updateGeneral(Request $request)
    {
        $settings = InstantBuySetting::firstOrFail();
        $settings->update($request->validate([
            'is_enabled' => 'boolean',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'show_product_summary' => 'boolean',
            'show_quantity_selector' => 'boolean',
            'show_price_breakdown' => 'boolean',
            'show_shipping_calculator' => 'boolean',
            'auto_select_cheapest_shipping' => 'boolean',
            'trust_message' => 'nullable|string|max:255',
            'show_bank_transfer' => 'boolean',
        ]));

        return back()->with('success', 'تم حفظ الإعدادات العامة');
    }

    public function updateColors(Request $request)
    {
        $settings = InstantBuySetting::firstOrFail();
        $settings->update($request->validate([
            'form_bg_color' => 'required|string|max:7',
            'form_border_color' => 'required|string|max:7',
            'form_border_width' => 'required|integer|min:0|max:10',
            'form_border_radius' => 'required|integer|min:0|max:50',
            'form_shadow' => 'nullable|string|max:50',
            'section_title_color' => 'required|string|max:7',
            'section_title_size' => 'required|integer|min:12|max:32',
            'section_title_weight' => 'required|in:normal,bold,semibold',
            'section_icon_color' => 'required|string|max:7',
            'input_bg_color' => 'required|string|max:7',
            'input_border_color' => 'required|string|max:7',
            'input_focus_color' => 'required|string|max:7',
            'input_text_color' => 'required|string|max:7',
            'input_placeholder_color' => 'required|string|max:7',
            'input_border_radius' => 'required|integer|min:0|max:50',
            'input_height' => 'required|integer|min:32|max:80',
            'summary_bg_color' => 'required|string|max:7',
            'summary_border_color' => 'required|string|max:7',
            'summary_text_color' => 'required|string|max:7',
            'summary_total_color' => 'required|string|max:7',
            'summary_total_size' => 'required|integer|min:14|max:36',
            'trust_message_color' => 'required|string|max:7',
            'trust_message_size' => 'required|integer|min:10|max:20',
        ]));

        return back()->with('success', 'تم حفظ الألوان');
    }

    public function updateFields(Request $request)
    {
        $settings = InstantBuySetting::firstOrFail();
        $fields = $request->input('fields', []);
        foreach ($fields as $field => $config) {
            $settings->update([
                "field_{$field}_enabled" => $config['enabled'] ?? false,
                "field_{$field}_required" => $config['required'] ?? false,
                "field_{$field}_label" => $config['label'] ?? null,
                "field_{$field}_placeholder" => $config['placeholder'] ?? null,
            ]);
        }

        return back()->with('success', 'تم حفظ إعدادات الحقول');
    }

    public function updateButtons(Request $request)
    {
        $settings = InstantBuySetting::firstOrFail();
        $settings->update($request->validate([
            'button_text' => 'required|string|max:100',
            'button_icon' => 'nullable|string|max:50',
            'button_bg_color' => 'required|string|max:7',
            'button_hover_color' => 'required|string|max:7',
            'button_text_color' => 'required|string|max:7',
            'button_text_size' => 'required|integer|min:12|max:32',
            'button_weight' => 'required|in:normal,bold',
            'button_border_radius' => 'required|integer|min:0|max:50',
            'button_height' => 'required|integer|min:32|max:80',
            'field_coupon_button_text' => 'required|string|max:50',
        ]));

        return back()->with('success', 'تم حفظ إعدادات الأزرار');
    }

    public function updateSuccess(Request $request)
    {
        $settings = InstantBuySetting::firstOrFail();
        $settings->update($request->validate([
            'success_title' => 'required|string|max:100',
            'success_message' => 'required|string|max:255',
            'success_button_text' => 'required|string|max:50',
            'success_show_order_number' => 'boolean',
            'success_show_whatsapp_button' => 'boolean',
            'success_show_order_details' => 'boolean',
            'success_whatsapp_message' => 'nullable|string|max:255',
            'success_icon_color' => 'required|string|max:7',
            'success_icon_size' => 'required|integer|min:32|max:128',
            'success_title_color' => 'required|string|max:7',
            'success_order_number_color' => 'required|string|max:7',
            'success_order_number_size' => 'required|integer|min:14|max:36',
        ]));

        return back()->with('success', 'تم حفظ إعدادات النجاح');
    }

    public function resetToDefaults()
    {
        InstantBuySetting::query()->delete();
        InstantBuySetting::create([]);

        return back()->with('success', 'تمت استعادة الإعدادات الافتراضية');
    }

    public function orders()
    {
        $orders = InstantBuyOrder::latest()->paginate(20);
        $stats = [
            'total' => InstantBuyOrder::count(),
            'new' => InstantBuyOrder::where('status', 'new')->count(),
            'confirmed' => InstantBuyOrder::where('status', 'confirmed')->count(),
            'processing' => InstantBuyOrder::where('status', 'processing')->count(),
            'shipped' => InstantBuyOrder::where('status', 'shipped')->count(),
            'delivered' => InstantBuyOrder::where('status', 'delivered')->count(),
            'cancelled' => InstantBuyOrder::where('status', 'cancelled')->count(),
        ];

        return view('admin.instant-buy.orders', compact('orders', 'stats'));
    }

    public function updateOrderStatus(Request $request, InstantBuyOrder $order)
    {
        $data = $request->validate([
            'status' => 'required|in:new,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);
        $order->update($data);

        return back()->with('success', 'تم تحديث حالة الطلب');
    }
}
