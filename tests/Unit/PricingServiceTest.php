<?php

namespace Tests\Unit;

use App\Services\PricingService;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = new PricingService();
    }

    public function test_calculate_subtotal(): void
    {
        $result = $this->pricing->calculateSubtotal(100.0, 3);
        $this->assertEquals(300.0, $result);
    }

    public function test_calculate_subtotal_with_decimals(): void
    {
        $result = $this->pricing->calculateSubtotal(99.99, 2);
        $this->assertEquals(199.98, $result);
    }

    public function test_calculate_subtotal_single_quantity(): void
    {
        $result = $this->pricing->calculateSubtotal(50.0, 1);
        $this->assertEquals(50.0, $result);
    }

    public function test_calculate_weight(): void
    {
        $result = $this->pricing->calculateWeight(1.5, 3);
        $this->assertEquals(4.5, $result);
    }

    public function test_calculate_weight_zero_quantity(): void
    {
        $result = $this->pricing->calculateWeight(2.0, 0);
        $this->assertEquals(0.0, $result);
    }

    public function test_calculate_weight_with_decimals(): void
    {
        $result = $this->pricing->calculateWeight(0.333, 3);
        $this->assertEquals(0.999, $result);
    }
}
