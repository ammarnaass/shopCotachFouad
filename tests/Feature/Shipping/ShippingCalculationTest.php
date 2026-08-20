<?php

namespace Tests\Feature\Shipping;

use App\Models\User\User;
use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use App\Modules\Shipping\Exceptions\NoShippingZoneException;
use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingZone;
use App\Modules\Shipping\Models\ShippingZoneLocation;
use App\Modules\Shipping\Services\ShippingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * اختبارات Feature لكل قواعد العمل في موديول الشحن بناءً على نموذج WooCommerce Shipping Zones (v1.1).
 */
class ShippingCalculationTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ShippingCalculationService
    {
        return app(ShippingCalculationService::class);
    }

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

    /**
     * FR-1.4: مطابقة ووكومرس — الفحص يتم بترتيب الأولوية اليدوي (sort_order ASC).
     * أول منطقة تطابق تفوز فوراً حتى لو كانت منطقة أخرى أكثر تحديداً لكنها لاحقة بالترتيب.
     */
    public function test_first_matching_standard_zone_by_sort_order_wins(): void
    {
        // منطقة 1: عامة (الجزائر كاملة)، مرتبة أولاً (sort_order = 1)
        $countryZone = ShippingZone::create([
            'name'       => 'شحن الجزائر عام',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $countryZone->id,
            'type'             => 'country',
            'value'            => 'DZ',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id' => $countryZone->id,
            'base_cost'        => 600,
            'status'           => 'active',
        ]);

        // منطقة 2: دقيقة (ولاية الجزائر 16)، مرتبة ثانياً (sort_order = 2)
        $stateZone = ShippingZone::create([
            'name'       => 'شحن العاصمة خاص',
            'zone_type'  => 'standard',
            'sort_order' => 2,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $stateZone->id,
            'type'             => 'state',
            'value'            => 'DZ:16',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id' => $stateZone->id,
            'base_cost'        => 300,
            'status'           => 'active',
        ]);

        // بما أن منطقة الدولة أعلى بالقائمة (sort_order = 1)، تفوز فوراً حسب نموذج ووكومرس
        $quote = $this->service()->calculate(
            countryCode: 'DZ',
            stateCode: '16',
            subtotal: 1000,
        );

        $this->assertEquals($countryZone->id, $quote->zoneId);
        $this->assertEquals(600, $quote->cost);

        // الآن نعكس الترتيب (نجعل منطقة العاصمة sort_order = 0)
        $stateZone->update(['sort_order' => 0]);

        $quote2 = $this->service()->calculate(
            countryCode: 'DZ',
            stateCode: '16',
            subtotal: 1000,
        );

        $this->assertEquals($stateZone->id, $quote2->zoneId);
        $this->assertEquals(300, $quote2->cost);
    }

    /**
     * FR-1.5: الرجوع لمنطقة Everywhere المحجوزة عند عدم تطابق أي منطقة قياسية.
     */
    public function test_falls_back_to_everywhere_zone_when_no_standard_zone_matches(): void
    {
        $everywhere = ShippingZone::getOrCreateEverywhereZone();
        $everywhere->update(['status' => 'active']);

        ShippingMethod::factory()->create([
            'shipping_zone_id' => $everywhere->id,
            'base_cost'        => 1200,
            'status'           => 'active',
        ]);

        // طلب لدولة لا توجد لها أي منطقة قياسية معرّفة
        $quote = $this->service()->calculate(
            countryCode: 'TN',
            city: 'تونس العاصمة',
            subtotal: 2000,
        );

        $this->assertEquals($everywhere->id, $quote->zoneId);
        $this->assertEquals(1200, $quote->cost);
    }

    /**
     * FR-2.2: مطابقة الرمز البريدي بنمط Wildcard (16*) وبنمط النطاق (16000-16999).
     */
    public function test_postcode_wildcard_and_range_matching(): void
    {
        // منطقة 1: مطابقة Wildcard (16*)
        $wildcardZone = ShippingZone::create([
            'name'       => 'منطقة 16 العامة',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $wildcardZone->id,
            'type'             => 'postcode',
            'value'            => '16*',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id' => $wildcardZone->id,
            'base_cost'        => 450,
            'status'           => 'active',
        ]);

        $quote = $this->service()->calculate(
            countryCode: 'DZ',
            postcode: '16045',
            subtotal: 1000,
        );

        $this->assertEquals($wildcardZone->id, $quote->zoneId);
        $this->assertEquals(450, $quote->cost);

        // منطقة 2: مطابقة Range (31000-31500)
        $rangeZone = ShippingZone::create([
            'name'       => 'منطقة وهران نطاق بريدي',
            'zone_type'  => 'standard',
            'sort_order' => 2,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $rangeZone->id,
            'type'             => 'postcode',
            'value'            => '31000-31500',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id' => $rangeZone->id,
            'base_cost'        => 550,
            'status'           => 'active',
        ]);

        $quote2 = $this->service()->calculate(
            countryCode: 'DZ',
            postcode: '31200',
            subtotal: 1000,
        );

        $this->assertEquals($rangeZone->id, $quote2->zoneId);
        $this->assertEquals(550, $quote2->cost);
    }

    /**
     * FR-2.4: حماية منطقة Everywhere من الحذف.
     */
    public function test_everywhere_zone_cannot_be_deleted(): void
    {
        $everywhere = ShippingZone::getOrCreateEverywhereZone();
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->delete(route('admin.shipping.zone.destroy', [
            'locale' => 'ar',
            'zone'   => $everywhere->id,
        ]));

        // يجب ألا تُحذف المنطقة من قاعدة البيانات
        $this->assertDatabaseHas('shipping_zones', ['id' => $everywhere->id]);
    }

    /**
     * FR-2.3: نقطة إعادة الترتيب POST admin/shipping/zones/reorder تُحدّث sort_order.
     */
    public function test_reorder_endpoint_updates_sort_orders_excluding_everywhere(): void
    {
        $zoneA = ShippingZone::create([
            'name'       => 'Zone A',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        $zoneB = ShippingZone::create([
            'name'       => 'Zone B',
            'zone_type'  => 'standard',
            'sort_order' => 2,
            'status'     => 'active',
            'regions'    => [],
        ]);
        $everywhere = ShippingZone::getOrCreateEverywhereZone();

        $admin = $this->createAdminUser();

        // إرسال الترتيب المعكوس: B أولاً ثم A
        $response = $this->actingAs($admin)->postJson(route('admin.shipping.zone.reorder', ['locale' => 'ar']), [
            'order' => [$zoneB->id, $zoneA->id],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $this->assertEquals(1, $zoneB->fresh()->sort_order);
        $this->assertEquals(2, $zoneA->fresh()->sort_order);
        $this->assertEquals(2147483647, $everywhere->fresh()->sort_order);
    }

    /**
     * FR-3.3: خطأ إعداد (منطقة بدون طرق شحن فعالة) = InvalidShippingConfigurationException.
     */
    public function test_matched_zone_without_active_method_throws_configuration_exception(): void
    {
        $zone = ShippingZone::create([
            'name'       => 'منطقة بدون طرق',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $zone->id,
            'type'             => 'country',
            'value'            => 'DZ',
        ]);

        $this->expectException(InvalidShippingConfigurationException::class);

        $this->service()->calculate(
            countryCode: 'DZ',
            city: 'الجزائر العاصمة',
            subtotal: 1000,
        );
    }

    /**
     * FR-1.3: الشحن المجاني يظهر فقط عند بلوغ حد free_shipping_threshold صراحة.
     */
    public function test_free_shipping_only_applies_above_explicit_threshold(): void
    {
        $zone = ShippingZone::create([
            'name'       => 'منطقة الجزائر',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $zone->id,
            'type'             => 'country',
            'value'            => 'DZ',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id'        => $zone->id,
            'calculation_type'        => 'flat',
            'base_cost'               => 500,
            'free_shipping_threshold' => 5000,
            'status'                  => 'active',
        ]);

        // تحت الحد -> شحن مدفوع
        $quoteBelow = $this->service()->calculate(countryCode: 'DZ', subtotal: 4999);
        $this->assertEquals(500, $quoteBelow->cost);
        $this->assertFalse($quoteBelow->isFreeShipping);

        // مساوي للحد -> شحن مجاني
        $quoteAt = $this->service()->calculate(countryCode: 'DZ', subtotal: 5000);
        $this->assertEquals(0, $quoteAt->cost);
        $this->assertTrue($quoteAt->isFreeShipping);
    }

    /**
     * FR-5.2: الـ API يرفض country_id رقمي.
     */
    public function test_api_endpoint_rejects_numeric_country_id(): void
    {
        $response = $this->postJson(route('api.shipping.calculate'), [
            'country_id' => 1,
            'city'       => 'الجزائر',
            'subtotal'   => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['country_code']);
    }

    /**
     * FR-5.2: الـ API يقبل country_code بحروف صغيرة ويُطبّعه.
     */
    public function test_api_endpoint_accepts_lowercase_country_code(): void
    {
        $zone = ShippingZone::create([
            'name'       => 'الجزائر',
            'zone_type'  => 'standard',
            'sort_order' => 1,
            'status'     => 'active',
            'regions'    => [],
        ]);
        ShippingZoneLocation::create([
            'shipping_zone_id' => $zone->id,
            'type'             => 'country',
            'value'            => 'DZ',
        ]);
        ShippingMethod::factory()->create([
            'shipping_zone_id' => $zone->id,
            'base_cost'        => 600,
            'status'           => 'active',
        ]);

        $response = $this->postJson(route('api.shipping.calculate'), [
            'country_code' => 'dz',
            'city'         => 'الجزائر',
            'subtotal'     => 1000,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.cost', 600);
    }
}
