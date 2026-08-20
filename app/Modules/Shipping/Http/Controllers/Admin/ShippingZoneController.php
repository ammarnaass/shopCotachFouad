<?php

namespace App\Modules\Shipping\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Http\Requests\StoreShippingZoneRequest;
use App\Modules\Shipping\Http\Requests\UpdateShippingZoneRequest;
use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Support\Facades\DB;

class ShippingZoneController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::with('company', 'methods')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.shipping.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.shipping.zone-form', [
            'zone' => new ShippingZone(),
        ]);
    }

    public function store(StoreShippingZoneRequest $request)
    {
        $validated = $request->validated();

        $zone = DB::transaction(function () use ($validated) {
            if (!empty($validated['is_default'])) {
                $this->clearOtherDefaults();
            }

            return ShippingZone::create($validated);
        });

        return redirect()
            ->route('admin.shipping-zones.edit', $zone)
            ->with('success', 'تم إنشاء منطقة الشحن بنجاح.');
    }

    public function edit(ShippingZone $zone)
    {
        return view('admin.shipping.zone-form', compact('zone'));
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $zone)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $zone) {
            if (!empty($validated['is_default'])) {
                $this->clearOtherDefaults(exceptZoneId: $zone->id);
            }

            $zone->update($validated);
        });

        return redirect()
            ->route('admin.shipping-zones.edit', $zone)
            ->with('success', 'تم تحديث منطقة الشحن بنجاح.');
    }

    public function destroy(ShippingZone $zone)
    {
        if ($zone->is_default) {
            return back()->withErrors([
                'is_default' => 'لا يمكن حذف المنطقة الافتراضية. عيّن منطقة افتراضية بديلة أولًا.',
            ]);
        }

        $zone->delete();

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('success', 'تم حذف منطقة الشحن.');
    }

    /**
     * يقفل صفوف المناطق الافتراضية الحالية (lockForUpdate) داخل نفس الـ transaction
     * لمنع سباق (race condition) لو حاول أدمنين تعيين منطقتين افتراضيتين بنفس اللحظة.
     */
    private function clearOtherDefaults(?int $exceptZoneId = null): void
    {
        $query = ShippingZone::where('is_default', true)->lockForUpdate();

        if ($exceptZoneId !== null) {
            $query->where('id', '!=', $exceptZoneId);
        }

        $query->update(['is_default' => false]);
    }
}
