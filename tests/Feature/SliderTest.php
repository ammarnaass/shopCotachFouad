<?php

namespace Tests\Feature;

use App\Models\Content\Slide;
use App\Models\User\User;
use Tests\TestCase;

class SliderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ─── Admin CRUD: Create with animation fields ────────────────

    public function test_admin_can_see_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/ar/admin/slider/create');

        $response->assertStatus(200);
        $response->assertSee(__t('admin.slider.animation_effect', [], 'ar'), false);
    }

    public function test_admin_can_create_slide_with_animation_effect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/ar/admin/slider', [
            'title' => 'Test Slide',
            'subtitle' => 'Test subtitle',
            'description' => 'Test description',
            'badge' => 'New',
            'link' => 'https://example.com',
            'btn_text' => 'Shop Now',
            'button_target' => '_same',
            'animation_effect' => 'slide-left',
            'entrance_effect' => 'fade-up',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect('/ar/admin/slider');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('slides', [
            'title' => 'Test Slide',
            'animation_effect' => 'slide-left',
            'entrance_effect' => 'fade-up',
        ]);
    }

    public function test_create_validation_rejects_invalid_animation_effect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/ar/admin/slider', [
            'title' => 'Invalid Effect',
            'animation_effect' => 'bounce',
            'entrance_effect' => 'spin',
        ]);

        $response->assertSessionHasErrors(['animation_effect', 'entrance_effect']);
    }

    // ─── Admin CRUD: Update ──────────────────────────────────────

    public function test_admin_can_update_animation_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $slide = Slide::create([
            'title' => 'Original Slide',
            'sort_order' => 1,
            'is_active' => true,
            'animation_effect' => 'fade',
            'entrance_effect' => 'fade-up',
        ]);

        $response = $this->actingAs($admin)->put("/ar/admin/slider/{$slide->id}", [
            'title' => 'Updated Slide',
            'animation_effect' => 'zoom',
            'entrance_effect' => 'fade-down',
            'button_target' => '_same',
        ]);

        $response->assertRedirect('/ar/admin/slider');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('slides', [
            'id' => $slide->id,
            'animation_effect' => 'zoom',
            'entrance_effect' => 'fade-down',
        ]);
    }

    public function test_admin_can_edit_slide(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $slide = Slide::create([
            'title' => 'Edit Test',
            'sort_order' => 1,
            'is_active' => true,
            'animation_effect' => 'slide-right',
            'entrance_effect' => 'zoom',
        ]);

        $response = $this->actingAs($admin)->get("/ar/admin/slider/{$slide->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('slide-right', false);
    }

    // ─── Admin CRUD: Toggle & Delete ─────────────────────────────

    public function test_admin_can_toggle_slide_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $slide = Slide::create([
            'title' => 'Toggle Test',
            'sort_order' => 1,
            'is_active' => true,
            'animation_effect' => 'fade',
            'entrance_effect' => 'fade-up',
        ]);

        $response = $this->actingAs($admin)->patch("/ar/admin/slider/{$slide->id}/toggle");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('slides', [
            'id' => $slide->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_slide(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $slide = Slide::create([
            'title' => 'Delete Test',
            'sort_order' => 1,
            'is_active' => true,
            'animation_effect' => 'fade',
            'entrance_effect' => 'fade-up',
        ]);

        $response = $this->actingAs($admin)->delete("/ar/admin/slider/{$slide->id}");

        $response->assertRedirect('/ar/admin/slider');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('slides', ['id' => $slide->id]);
    }

    // ─── Frontend rendering ──────────────────────────────────────

    public function test_frontend_renders_slides_with_data_effect(): void
    {
        Slide::create([
            'title' => 'Frontend Slide',
            'subtitle' => 'Subtitle',
            'description' => 'Description',
            'badge' => 'Badge',
            'link' => 'https://example.com',
            'btn_text' => 'Click',
            'button_target' => '_same',
            'sort_order' => 1,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
            'animation_effect' => 'slide-left',
            'entrance_effect' => 'fade-up',
        ]);

        $slides = Slide::visible()->ordered()->get()->toArray();

        $html = view('frontend.partials.hero-slider', ['slides' => $slides])->render();

        $this->assertStringContainsString('data-effect="slide-left"', $html);
        $this->assertStringContainsString('data-entrance="fade-up"', $html);
        $this->assertStringContainsString('data-animate-index="0"', $html);
        $this->assertStringContainsString('data-animate-index="1"', $html);
        $this->assertStringContainsString('data-animate-index="4"', $html);
    }

    public function test_model_accessors_fall_back_to_defaults(): void
    {
        $slide = new Slide;
        $slide->title = 'Fallback Test';
        $slide->sort_order = 1;

        $this->assertSame('fade', $slide->animation_effect);
        $this->assertSame('fade-up', $slide->entrance_effect);
    }

    public function test_constants_define_all_effects(): void
    {
        $this->assertArrayHasKey('fade', Slide::ANIMATION_EFFECTS);
        $this->assertArrayHasKey('slide-left', Slide::ANIMATION_EFFECTS);
        $this->assertArrayHasKey('slide-right', Slide::ANIMATION_EFFECTS);
        $this->assertArrayHasKey('zoom', Slide::ANIMATION_EFFECTS);
        $this->assertArrayHasKey('flip', Slide::ANIMATION_EFFECTS);

        $this->assertArrayHasKey('none', Slide::ENTRANCE_EFFECTS);
        $this->assertArrayHasKey('fade-up', Slide::ENTRANCE_EFFECTS);
        $this->assertArrayHasKey('fade-down', Slide::ENTRANCE_EFFECTS);
        $this->assertArrayHasKey('fade-left', Slide::ENTRANCE_EFFECTS);
        $this->assertArrayHasKey('fade-right', Slide::ENTRANCE_EFFECTS);
        $this->assertArrayHasKey('zoom', Slide::ENTRANCE_EFFECTS);
    }
}
