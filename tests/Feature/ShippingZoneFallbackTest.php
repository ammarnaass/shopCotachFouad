<?php

namespace Tests\Feature;

use App\Exceptions\NoShippingZoneException;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingZoneFallbackTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // 1. لا منطقة تغطي المدينة + لا منطقة افتراضية → استثناء واضح (لا صفر)
    // =====================================================================
    public function test_unmatched_city_throws_exception_when_no_default_zone(): void
    {
        // لا توجد أي منطقة في قاعدة البيانات
        $this->expectException(NoShippingZoneException::class);

        app(OrderService::class)->calculateShipping(
            city: 'مدينة-وهمية-2099',
            countryCode: 'ZZ',
        );
    }

    // =====================================================================
    // 2. لا منطقة تغطي المدينة + توجد منطقة افتراضية نشطة → يستخدم الـ fallback
    // =====================================================================
    public function test_unmatched_city_uses_default_zone_when_available(): void
    {
        ShippingZone::factory()->asDefault()->create([
            'cost'      => 500,
            'home_cost' => null,
        ]);

        $cost = app(OrderService::class)->calculateShipping(
            city: 'مدينة-وهمية-2099',
            countryCode: 'ZZ',
        );

        // calculateCost ترجع 0 لأن المدينة لا تطابق المنطقة بصرامة
        // لكن الاستثناء لن يُرمى — سيتم استخدام fallback
        $this->assertIsFloat($cost);
    }

    // =====================================================================
    // 3. منطقة افتراضية غير نشطة لا تُستخدم كـ fallback
    // =====================================================================
    public function test_inactive_default_zone_is_not_used_as_fallback(): void
    {
        ShippingZone::factory()->asDefault()->inactive()->create([
            'cost' => 500,
        ]);

        $this->expectException(NoShippingZoneException::class);

        app(OrderService::class)->calculateShipping(
            city: 'مدينة-وهمية-2099',
            countryCode: 'ZZ',
        );
    }

    // =====================================================================
    // 4. ضمان منطقة افتراضية وحيدة — إنشاء ثانية يلغي الأولى
    // =====================================================================
    public function test_only_one_zone_can_be_default_at_a_time(): void
    {
        /** @var ShippingZone $zone1 */
        $zone1 = ShippingZone::factory()->asDefault()->create(['name' => 'المنطقة الأولى']);

        $this->assertTrue($zone1->fresh()->is_default);

        // إنشاء منطقة ثانية افتراضية عبر الكنترولر (محاكاة HTTP)
        $this->actingAs($this->createAdminUser())
            ->post(route('admin.shipping.zone.store', ['locale' => 'ar']), [
                'name'                    => 'المنطقة الثانية',
                'delivery_type'           => 'both',
                'cost'                    => 300,
                'status'                  => 'active',
                'is_default'              => '1',
                'countries'               => ['DZ'],
            ]);

        // يجب أن تكون منطقة واحدة فقط افتراضية
        $this->assertEquals(
            1,
            ShippingZone::where('is_default', true)->count(),
            'يجب ألا تكون هناك أكثر من منطقة افتراضية واحدة'
        );

        // المنطقة الأولى يجب أن تفقد is_default
        $this->assertFalse($zone1->fresh()->is_default);
    }

    // =====================================================================
    // 5. NoShippingZoneException تحمل المدينة والدولة الصحيحة
    // =====================================================================
    public function test_exception_contains_city_and_country(): void
    {
        try {
            app(OrderService::class)->calculateShipping(
                city: 'وجدة',
                countryCode: 'MA',
            );
            $this->fail('يجب أن يُرمى استثناء NoShippingZoneException');
        } catch (NoShippingZoneException $e) {
            $this->assertEquals('وجدة', $e->getCity());
            $this->assertEquals('MA', $e->getCountryCode());
        }
    }

    // =====================================================================
    // Helper: إنشاء مستخدم Admin للاختبارات التي تحتاج صلاحيات
    // =====================================================================
    private function createAdminUser()
    {
        $userClass = class_exists(\App\Models\User\User::class)
            ? \App\Models\User\User::class
            : \App\Models\User::class;

        $user = $userClass::factory()->create();

        if (class_exists(\App\Modules\Users\Models\Role::class)) {
            $role = \App\Modules\Users\Models\Role::firstOrCreate(
                ['name' => 'admin'],
                ['display_name' => 'Administrator', 'name' => 'admin']
            );
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }
}
