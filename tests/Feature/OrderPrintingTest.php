<?php

namespace Tests\Feature;

use App\Actions\Order\CreateInstantOrder;
use App\Models\Catalog\ProductImage;
use App\Models\Documents\InvoiceTemplate;
use App\Models\Documents\LabelTemplate;
use App\Models\Catalog\Product;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\Payment;
use App\Models\Settings\Setting;
use App\Models\Shipping\ShippingAddress;
use App\Models\Shipping\ShippingCompany;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OrderPrintingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DocumentPermissionsSeeder::class);
        $this->seed(\Database\Seeders\InvoiceTemplateSeeder::class);
        $this->seed(\Database\Seeders\LabelTemplateSeeder::class);
    }

    public function test_instant_order_creation_persists_full_order_information(): void
    {
        $product = Product::factory()->create([
            'name' => 'قميص رياضي',
            'sku' => 'SKU-ORDER-1',
            'stock' => 10,
            'price' => 3500,
            'weight' => 1.2,
        ]);

        $shippingCompany = ShippingCompany::create([
            'name' => 'شركة الشحن السريع',
            'status' => 'active',
        ]);

        $order = app(CreateInstantOrder::class)->execute([
            'first_name' => 'فؤاد',
            'last_name' => 'بن علي',
            'phone' => '0550123456',
            'email' => 'customer@example.com',
            'country_code' => 'DZ',
            'state_code' => '16',
            'city' => 'الجزائر',
            'district' => 'حي النصر',
            'address' => 'شارع 5 عمارة 10',
            'zip' => '16000',
            'shipping_method' => 'express',
            'shipping_company_id' => $shippingCompany->id,
            'delivery_type' => 'office',
            'quantity' => 2,
            'custom_text' => 'اسم مطبوع',
            'notes' => 'يرجى الاتصال قبل التسليم',
            'payment_method' => 'cod',
        ], $product);

        $order->refresh()->load('shippingAddress', 'items', 'payment');

        $this->assertSame('office', $order->delivery_type);
        $this->assertSame($shippingCompany->id, $order->shipping_company_id);
        $this->assertSame('express', $order->shipping_method);
        $this->assertSame('customer@example.com', $order->guest_email);
        $this->assertSame('يرجى الاتصال قبل التسليم', $order->notes);
        $this->assertNotNull($order->guest_phone);

        $this->assertSame('فؤاد بن علي', $order->shippingAddress->name);
        $this->assertSame('customer@example.com', $order->shippingAddress->email);
        $this->assertSame('16', $order->shippingAddress->state_code);
        $this->assertSame('حي النصر', $order->shippingAddress->district);
        $this->assertSame('16000', $order->shippingAddress->zip);

        $this->assertCount(1, $order->items);
        $this->assertSame('اسم مطبوع', $order->items->first()->custom_text);
        $this->assertCount(1, $order->payment);
    }

    public function test_invoice_print_preview_includes_complete_order_information(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createPrintableOrder();

        $response = $this->actingAs($admin)->get(route('admin.orders.invoice', [
            'locale' => 'ar',
            'order' => $order,
            'print' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('يرجى الاتصال قبل التسليم');
        $response->assertSee('شركة الشحن السريع');
        $response->assertSee('توصيل إلى المكتب');
        $response->assertSee('المقاس: L');
        $response->assertSee('اللون: أسود');
    }

    public function test_label_print_preview_includes_complete_order_information(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createPrintableOrder();

        $response = $this->actingAs($admin)->get(route('admin.orders.label', [
            'locale' => 'ar',
            'order' => $order,
            'print' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('customer@example.com');
        $response->assertSee('شركة الشحن السريع');
        $response->assertSee('توصيل إلى المكتب');
        $response->assertSee('المقاس: L');
        $response->assertSee('يرجى الاتصال قبل التسليم');
    }

    public function test_other_label_templates_also_include_complete_order_information(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createPrintableOrder();

        foreach (['compact', 'thermal'] as $slug) {
            $template = LabelTemplate::where('slug', $slug)->firstOrFail();

            $response = $this->actingAs($admin)->get(route('admin.orders.label', [
                'locale' => 'ar',
                'order' => $order,
                'print' => 1,
                'template_id' => $template->id,
            ]));

            $response->assertOk();
            $response->assertSee('customer@example.com');
            $response->assertSee('شركة الشحن السريع');
            $response->assertSee('توصيل إلى المكتب');
            $response->assertSee('المقاس: L');
            $response->assertSee('يرجى الاتصال قبل التسليم');
        }
    }

    public function test_pdf_html_uses_local_file_paths_for_images_instead_of_localhost_urls(): void
    {
        config(['app.url' => 'http://localhost:8000']);

        File::ensureDirectoryExists(public_path('storage/test-pdf'));
        File::put(public_path('storage/test-pdf/store-logo.png'), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9s4w0WQAAAAASUVORK5CYII='));
        File::put(public_path('storage/test-pdf/product.png'), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9s4w0WQAAAAASUVORK5CYII='));

        Setting::set('store_logo', 'test-pdf/store-logo.png', 'store');

        $order = $this->createPrintableOrder();
        ProductImage::create([
            'product_id' => $order->items->first()->product_id,
            'image' => 'test-pdf/product.png',
            'is_primary' => true,
            'order' => 1,
        ]);

        $invoiceTemplate = InvoiceTemplate::where('slug', 'modern')->firstOrFail();
        $invoiceService = app(\App\Services\Documents\InvoiceService::class);
        $invoice = $invoiceService->getOrCreate($order, $invoiceTemplate->id);
        $invoiceData = $invoiceService->getInvoiceData($order->fresh(['items.product.images', 'shippingAddress', 'shippingCompany', 'payment', 'user']), $invoice, $invoiceTemplate);
        $invoiceHtml = view('documents.invoices.modern', array_merge($invoiceData, ['pdf_mode' => true]))->render();

        $storeLogoFileUri = $this->toFileUri(public_path('storage/test-pdf/store-logo.png'));
        $productFileUri = $this->toFileUri(public_path('storage/test-pdf/product.png'));

        $this->assertStringContainsString($storeLogoFileUri, $invoiceHtml);
        $this->assertStringContainsString($productFileUri, $invoiceHtml);
        $this->assertStringNotContainsString(asset('storage/test-pdf/store-logo.png'), $invoiceHtml);
        $this->assertStringNotContainsString(asset('storage/test-pdf/product.png'), $invoiceHtml);

        $labelTemplate = LabelTemplate::where('slug', 'classic')->firstOrFail();
        $labelService = app(\App\Services\Documents\LabelService::class);
        $labelData = $labelService->getLabelData($order->fresh(['items.product.images', 'shippingAddress', 'shippingCompany', 'payment', 'user']), $labelTemplate);
        $labelHtml = view('documents.labels.classic', array_merge($labelData, ['pdf_mode' => true]))->render();

        $this->assertStringContainsString($storeLogoFileUri, $labelHtml);
        $this->assertStringNotContainsString(asset('storage/test-pdf/store-logo.png'), $labelHtml);
    }

    public function test_pdf_document_views_use_a_dompdf_safe_arabic_font_stack(): void
    {
        $order = $this->createPrintableOrder();

        $invoiceTemplate = InvoiceTemplate::where('slug', 'modern')->firstOrFail();
        $invoiceService = app(\App\Services\Documents\InvoiceService::class);
        $invoice = $invoiceService->getOrCreate($order, $invoiceTemplate->id);
        $invoiceData = $invoiceService->getInvoiceData($order->fresh(['items.product.images', 'shippingAddress', 'shippingCompany', 'payment', 'user']), $invoice, $invoiceTemplate);
        $invoiceHtml = view('documents.invoices.modern', array_merge($invoiceData, ['pdf_mode' => true]))->render();

        $this->assertStringContainsString('DejaVu Sans', $invoiceHtml);

        $labelTemplate = LabelTemplate::where('slug', 'classic')->firstOrFail();
        $labelService = app(\App\Services\Documents\LabelService::class);
        $labelData = $labelService->getLabelData($order->fresh(['items.product.images', 'shippingAddress', 'shippingCompany', 'payment', 'user']), $labelTemplate);
        $labelHtml = view('documents.labels.classic', array_merge($labelData, ['pdf_mode' => true]))->render();

        $this->assertStringContainsString('DejaVu Sans', $labelHtml);
    }

    public function test_invoice_and_label_download_responses_are_pdf_attachments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createPrintableOrder();

        $invoiceResponse = $this->actingAs($admin)->get(route('admin.orders.invoice', [
            'locale' => 'ar',
            'order' => $order,
        ]));

        $invoiceResponse->assertOk();
        $this->assertSame('application/pdf', $invoiceResponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $invoiceResponse->headers->get('content-disposition', ''));
        $this->assertStringContainsString('invoice-', $invoiceResponse->headers->get('content-disposition', ''));

        $labelResponse = $this->actingAs($admin)->get(route('admin.orders.label', [
            'locale' => 'ar',
            'order' => $order,
        ]));

        $labelResponse->assertOk();
        $this->assertSame('application/pdf', $labelResponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $labelResponse->headers->get('content-disposition', ''));
        $this->assertStringContainsString('label-', $labelResponse->headers->get('content-disposition', ''));
    }

    private function createPrintableOrder(): Order
    {
        $product = Product::factory()->create([
            'name' => 'حذاء تدريب',
            'sku' => 'SKU-PRINT-1',
            'price' => 8500,
            'stock' => 5,
        ]);

        $shippingCompany = ShippingCompany::create([
            'name' => 'شركة الشحن السريع',
            'status' => 'active',
        ]);

        $shippingAddress = ShippingAddress::create([
            'user_id' => null,
            'first_name' => 'فؤاد',
            'last_name' => 'بن علي',
            'name' => 'فؤاد بن علي',
            'phone' => '+213550123456',
            'email' => 'customer@example.com',
            'country_code' => 'DZ',
            'state_code' => '16',
            'city' => 'الجزائر',
            'district' => 'حي النصر',
            'address' => 'شارع 5 عمارة 10',
            'zip' => '16000',
            'is_default' => false,
        ]);

        $order = Order::create([
            'guest_email' => 'customer@example.com',
            'guest_phone' => '+213550123456',
            'is_instant_buy' => true,
            'order_number' => 'ORD-PRINT-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'subtotal' => 8500,
            'shipping_cost' => 600,
            'discount' => 0,
            'tax' => 0,
            'cod_fee' => 0,
            'grand_total' => 9100,
            'notes' => 'يرجى الاتصال قبل التسليم',
            'shipping_address_id' => $shippingAddress->id,
            'shipping_company_id' => $shippingCompany->id,
            'shipping_method' => 'express',
            'delivery_type' => 'office',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => 8500,
            'total' => 8500,
            'options_summary' => [
                ['label' => 'المقاس', 'value' => 'L'],
                ['label' => 'اللون', 'value' => 'أسود'],
            ],
            'custom_text' => 'اسم مطبوع',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'method' => 'cod',
            'status' => 'pending',
            'amount' => 9100,
        ]);

        return $order->fresh(['items.product', 'shippingAddress', 'shippingCompany', 'payment']);
    }

    private function toFileUri(string $path): string
    {
        return 'file:///' . str_replace('\\', '/', $path);
    }
}
