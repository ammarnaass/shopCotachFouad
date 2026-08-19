<?php

namespace Tests\Feature;

use App\Models\Documents\Invoice;
use App\Models\Documents\InvoiceTemplate;
use App\Models\Documents\LabelTemplate;
use App\Models\Order\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DocumentPermissionsSeeder::class);
        $this->seed(\Database\Seeders\InvoiceTemplateSeeder::class);
        $this->seed(\Database\Seeders\LabelTemplateSeeder::class);
    }

    public function test_invoice_templates_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/invoices/templates');
        $response->assertStatus(200);
        $response->assertSee('القالب الكلاسيكي');
    }

    public function test_label_templates_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/order-labels/templates');
        $response->assertStatus(200);
        $response->assertSee('ملصق شحن قياسي');
    }

    public function test_footer_management_page_accessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/footer');
        $response->assertStatus(200);
    }

    public function test_printing_settings_page_accessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/settings/printing');
        $response->assertStatus(200);
    }

    public function test_invoice_number_generation_is_unique_and_sequential(): void
    {
        $year = now()->year;

        $num1 = Invoice::generateNumber();
        $this->assertStringStartsWith("INV-{$year}-", $num1);

        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);
        $invoice = Invoice::create(['order_id' => $order->id]);

        $this->assertNotNull($invoice->invoice_number);
    }
}
