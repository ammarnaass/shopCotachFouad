<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Slide;
use App\Models\Page;
use App\Models\ShippingCompany;
use App\Models\ShippingZone;
use Tests\TestCase;

class RoutesAndCrudAuditTest extends TestCase
{
    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->orWhere('id', 1)->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_frontend_home_and_core_pages_load(): void
    {
        $response = $this->get('/ar');
        $response->assertStatus(200);

        $response = $this->get('/ar/shop');
        $response->assertStatus(200);

        $product = Product::first();
        if ($product) {
            $response = $this->get('/ar/shop/' . $product->slug);
            $response->assertStatus(200);
        }

        $category = Category::first();
        if ($category) {
            $response = $this->get('/ar/category/' . $category->slug);
            $response->assertStatus(200);
        }

        $response = $this->get('/ar/cart');
        $response->assertStatus(200);

        $response = $this->get('/ar/wishlist');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        $response = $this->get('/ar/login');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        $response = $this->get('/ar/register');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_admin_dashboard_and_catalog_pages_load(): void
    {
        $this->actingAs($this->admin);

        // Dashboard
        $response = $this->get('/ar/admin');
        $response->assertStatus(200);

        // Products
        $response = $this->get('/ar/admin/products');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/products/create');
        $response->assertStatus(200);

        $product = Product::first();
        if ($product) {
            $response = $this->get('/ar/admin/products/' . $product->id . '/edit');
            $response->assertStatus(200);

            $response = $this->get('/ar/admin/products/' . ($product->slug ?: $product->id) . '/gallery');
            $response->assertStatus(200);
        }

        // Categories
        $response = $this->get('/ar/admin/categories');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/categories/create');
        $response->assertStatus(200);

        $category = Category::first();
        if ($category) {
            $response = $this->get('/ar/admin/categories/' . $category->id . '/edit');
            $response->assertStatus(200);
        }
    }

    public function test_admin_orders_and_users_pages_load(): void
    {
        $this->actingAs($this->admin);

        // Orders
        $response = $this->get('/ar/admin/orders');
        $response->assertStatus(200);

        $order = Order::first();
        if ($order) {
            $response = $this->get('/ar/admin/orders/' . $order->id);
            $response->assertStatus(200);
        }

        // Users
        $response = $this->get('/ar/admin/users');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/users/create');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        if ($this->admin) {
            $response = $this->get('/ar/admin/users/' . $this->admin->id . '/edit');
            $response->assertStatus(200);
        }
    }

    public function test_admin_settings_coupons_and_cms_load(): void
    {
        $this->actingAs($this->admin);

        // Coupons
        $response = $this->get('/ar/admin/coupons');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/coupons/create');
        $response->assertStatus(200);

        // Sliders
        $response = $this->get('/ar/admin/slider');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/slider/create');
        $response->assertStatus(200);

        // Shipping
        $response = $this->get('/ar/admin/shipping');
        $response->assertStatus(200);

        // Settings
        $response = $this->get('/ar/admin/settings');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/settings/printing');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/footer');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/languages');
        $response->assertStatus(200);
    }

    public function test_admin_invoice_and_label_templates_load(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/ar/admin/invoices/templates');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/invoices/templates/create');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/order-labels/templates');
        $response->assertStatus(200);

        $response = $this->get('/ar/admin/order-labels/templates/create');
        $response->assertStatus(200);
    }

    public function test_cart_and_instant_buy_ajax_endpoints(): void
    {
        $product = Product::first() ?? Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 5000,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Add to cart
        $response = $this->postJson('/ar/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Instant buy calculate
        $response = $this->postJson('/ar/instant-buy/calculate', [
            'product_id' => $product->id,
            'quantity' => 1,
            'country_code' => 'DZ',
        ]);
        $response->assertStatus(200);
    }
}
