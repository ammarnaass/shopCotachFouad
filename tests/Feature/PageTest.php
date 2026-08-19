<?php

namespace Tests\Feature;

use App\Models\Content\Page;
use App\Models\User\User;
use Database\Seeders\PageSeeder;
use Tests\TestCase;

class PageTest extends TestCase
{
    protected array $pages = ['about', 'contact', 'faq', 'return-policy', 'shipping', 'privacy', 'terms'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    // ─── Frontend: All pages render ─────────────────────────────

    public function test_all_static_pages_render_in_arabic(): void
    {
        foreach ($this->pages as $slug) {
            $response = $this->get("/ar/page/{$slug}");
            $response->assertStatus(200);
        }
    }

    public function test_all_static_pages_render_in_english(): void
    {
        foreach ($this->pages as $slug) {
            $response = $this->get("/en/page/{$slug}");
            $response->assertStatus(200);
        }
    }

    public function test_all_static_pages_render_in_french(): void
    {
        foreach ($this->pages as $slug) {
            $response = $this->get("/fr/page/{$slug}");
            $response->assertStatus(200);
        }
    }

    // ─── Frontend: Content integrity ────────────────────────────

    public function test_about_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSeeInOrder([
            'من نحن',
            'مهمتنا',
            'رؤيتنا',
            'قيمنا',
        ]);
    }

    public function test_contact_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/contact');

        $response->assertSeeInOrder([
            'اتصل بنا',
            'الواتساب',
            'البريد الإلكتروني',
            'ساعات العمل',
            'المقر الرئيسي',
        ]);
    }

    public function test_faq_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/faq');

        $response->assertSeeInOrder([
            'الأسئلة الشائعة',
            'كيف أطلب منتجاً؟',
            'ما هي طرق الدفع المتاحة؟',
            'كيف أتواصل مع خدمة العملاء؟',
        ]);
    }

    public function test_return_policy_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/return-policy');

        $response->assertSeeInOrder([
            'سياسة الإرجاع',
            'مدة الإرجاع',
            'المنتجات غير القابلة للإرجاع',
            'طريقة الإرجاع',
            'استرداد المبلغ',
            'المنتجات التالفة',
        ]);
    }

    public function test_shipping_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/shipping');

        $response->assertSeeInOrder([
            'الشحن والتوصيل',
            'مدة التوصيل',
            'تكلفة الشحن',
            'الشحن المجاني',
            'شركات الشحن',
            'تتبع الطلب',
        ]);
    }

    public function test_privacy_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/privacy');

        $response->assertSeeInOrder([
            'سياسة الخصوصية',
            'البيانات التي نجمعها',
            'كيف نستخدم بياناتك',
            'أمان البيانات',
            'حقوقك',
            'ملفات تعريف الارتباط',
        ]);
    }

    public function test_terms_page_has_correct_content(): void
    {
        $response = $this->get('/ar/page/terms');

        $response->assertSeeInOrder([
            'الشروط والأحكام',
            'الاستخدام',
            'الأسعار',
            'الطلب والتأكيد',
            'الملكية الفكرية',
            'حدود المسؤولية',
        ]);
    }

    // ─── Frontend: UI elements ──────────────────────────────────

    public function test_page_has_breadcrumb(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('home');
    }

    public function test_page_has_hero_section(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('container-app', false);
        $response->assertSee('font-extrabold');
    }

    public function test_page_has_material_icon_in_hero(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('material-symbols-outlined');
        $response->assertSee('info');
    }

    public function test_page_has_intro_block(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('format_quote');
        $response->assertSee('متجرك العربي المفضل للتسوق الإلكتروني');
    }

    public function test_page_has_cta_section(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('هل تحتاج مساعدة؟');
        $response->assertSee('صفحة الاتصال');
        $response->assertSee('واتساب');
    }

    public function test_page_uses_correct_color_scheme(): void
    {
        $response = $this->get('/ar/page/about');

        $response->assertSee('from-blue-600');
    }

    public function test_contact_page_uses_green_scheme(): void
    {
        $response = $this->get('/ar/page/contact');

        $response->assertSee('from-emerald-600');
    }

    public function test_faq_page_uses_purple_scheme(): void
    {
        $response = $this->get('/ar/page/faq');

        $response->assertSee('from-purple-600');
    }

    public function test_privacy_page_uses_indigo_scheme(): void
    {
        $response = $this->get('/ar/page/privacy');

        $response->assertSee('from-indigo-600');
    }

    public function test_terms_page_uses_red_scheme(): void
    {
        $response = $this->get('/ar/page/terms');

        $response->assertSee('from-rose-600');
    }

    public function test_page_has_material_icon_for_each_slug(): void
    {
        $icons = [
            'about' => 'info',
            'contact' => 'headset_mic',
            'faq' => 'help',
            'return-policy' => 'undo',
            'shipping' => 'local_shipping',
            'privacy' => 'shield',
            'terms' => 'description',
        ];

        foreach ($icons as $slug => $icon) {
            $response = $this->get("/ar/page/{$slug}");
            $response->assertSee($icon);
        }
    }

    // ─── Frontend: Redirect routes ──────────────────────────────

    public function test_about_redirect_works(): void
    {
        $response = $this->get('/ar/about');
        $response->assertRedirect('/ar/page/about');
    }

    public function test_contact_redirect_works(): void
    {
        $response = $this->get('/ar/contact');
        $response->assertRedirect('/ar/page/contact');
    }

    public function test_faq_redirect_works(): void
    {
        $response = $this->get('/ar/faq');
        $response->assertRedirect('/ar/page/faq');
    }

    public function test_return_redirect_works(): void
    {
        $response = $this->get('/ar/return');
        $response->assertRedirect('/ar/page/return-policy');
    }

    // ─── Frontend: Edge cases ──────────────────────────────────

    public function test_nonexistent_page_returns_404(): void
    {
        $response = $this->get('/ar/page/nonexistent-page');
        $response->assertStatus(404);
    }

    public function test_inactive_page_not_visible(): void
    {
        $page = Page::where('slug', 'about')->first();
        $page->update(['is_active' => false]);

        $response = $this->get('/ar/page/about');
        $response->assertStatus(404);
    }

    public function test_page_with_empty_content_still_renders(): void
    {
        Page::create([
            'title' => 'Empty Page',
            'slug' => 'empty',
            'content' => null,
            'is_active' => true,
        ]);

        $response = $this->get('/ar/page/empty');
        $response->assertStatus(200);
        $response->assertSee('Empty Page');
    }

    // ─── Admin CRUD: Listing ───────────────────────────────────

    public function test_guest_cannot_access_admin_pages(): void
    {
        $response = $this->get('/ar/admin/pages');
        $response->assertRedirect();
    }

    public function test_admin_can_list_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/pages');

        $response->assertStatus(200);
        $response->assertSee('الصفحات', false);
        foreach ($this->pages as $slug) {
            $response->assertSee($slug);
        }
    }

    // ─── Admin CRUD: Create ────────────────────────────────────

    public function test_admin_can_see_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/pages/create');

        $response->assertStatus(200);
        $response->assertSee('إضافة صفحة', false);
    }

    public function test_admin_can_create_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/ar/admin/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'icon' => 'star',
            'color' => 'blue',
            'intro' => 'Test intro',
            'content' => '[{"title":"Section 1","body":"Body 1"}]',
            'meta_title' => 'Test Meta',
            'meta_description' => 'Test description',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $response->assertRedirect('/ar/admin/pages');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'slug' => 'test-page',
            'title' => 'Test Page',
            'icon' => 'star',
            'color' => 'blue',
        ]);
    }

    public function test_created_page_is_accessible_on_frontend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/ar/admin/pages', [
            'title' => 'New Frontend Page',
            'slug' => 'new-frontend-page',
            'icon' => 'star',
            'color' => 'purple',
            'intro' => 'Check this out',
            'content' => '[{"title":"Sec 1","body":"Body text"}]',
            'is_active' => true,
        ]);

        $response = $this->get('/ar/page/new-frontend-page');
        $response->assertStatus(200);
        $response->assertSee('New Frontend Page');
        $response->assertSee('Check this out');
        $response->assertSee('Sec 1');
    }

    // ─── Admin CRUD: Validation ────────────────────────────────

    public function test_create_requires_title_and_slug(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/ar/admin/pages', [
            'title' => '',
            'slug' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'slug']);
    }

    public function test_create_requires_unique_slug(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/ar/admin/pages', [
            'title' => 'First',
            'slug' => 'duplicate-slug',
        ]);

        $response = $this->actingAs($admin)->post('/ar/admin/pages', [
            'title' => 'Second',
            'slug' => 'duplicate-slug',
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    // ─── Admin CRUD: Edit & Update ─────────────────────────────

    public function test_admin_can_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::where('slug', 'about')->first();

        $response = $this->actingAs($admin)->get("/ar/admin/pages/{$page->slug}/edit");

        $response->assertStatus(200);
        $response->assertSee('من نحن');
        $response->assertSee('info');
    }

    public function test_admin_can_update_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::where('slug', 'about')->first();

        $response = $this->actingAs($admin)->put("/ar/admin/pages/{$page->slug}", [
            'title' => 'Updated Title',
            'slug' => 'about',
            'icon' => 'star',
            'color' => 'red',
            'intro' => 'Updated intro',
            'content' => '[{"title":"New Section","body":"New body"}]',
            'is_active' => true,
        ]);

        $response->assertRedirect('/ar/admin/pages');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'slug' => 'about',
            'title' => 'Updated Title',
            'icon' => 'star',
            'color' => 'red',
            'intro' => 'Updated intro',
        ]);
    }

    public function test_updated_page_reflects_on_frontend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::where('slug', 'about')->first();

        $this->actingAs($admin)->put("/ar/admin/pages/{$page->slug}", [
            'title' => 'عن المتجر',
            'slug' => 'about',
            'icon' => 'info',
            'color' => 'green',
            'intro' => 'مقدمة جديدة',
            'content' => '[{"title":"قسم جديد","body":"نص جديد"}]',
            'is_active' => true,
        ]);

        $response = $this->get('/ar/page/about');
        $response->assertStatus(200);
        $response->assertSee('عن المتجر');
        $response->assertSee('مقدمة جديدة');
        $response->assertSee('قسم جديد');
    }

    // ─── Admin CRUD: Delete ────────────────────────────────────

    public function test_admin_can_delete_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::where('slug', 'about')->first();

        $response = $this->actingAs($admin)->delete("/ar/admin/pages/{$page->slug}");

        $response->assertRedirect('/ar/admin/pages');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('pages', ['slug' => 'about']);
    }

    public function test_deleted_page_returns_404_on_frontend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::where('slug', 'about')->first();

        $this->actingAs($admin)->delete("/ar/admin/pages/{$page->slug}");

        $response = $this->get('/ar/page/about');
        $response->assertStatus(404);
    }

    // ─── Role-based access ─────────────────────────────────────

    public function test_manager_can_access_admin_pages(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get('/ar/admin/pages');
        $response->assertStatus(200);
    }

    public function test_customer_cannot_access_admin_pages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/ar/admin/pages');
        $response->assertStatus(403);
    }
}
