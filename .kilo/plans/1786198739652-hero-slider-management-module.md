# AN Shop Hero Slider Management Module — Implementation Plan

## Executive Summary

The project already has a partial slider implementation (`Slide` model, admin CRUD, static hero showing only first slide). This plan upgrades it to a full Hero Slider Management Module per the spec: responsive carousel with mobile/desktop images, scheduling, Redis caching, drag-and-drop reordering, and frontend slider with auto-play/touch/keyboard navigation.

---

## Current State Analysis

| Component | Status | Notes |
|-----------|--------|-------|
| `Slide` model | ✅ Exists | `App\Models\Content\Slide` — missing: `mobile_image`, `description`, `button_target`, `starts_at`, `ends_at` |
| Migration | ✅ Exists | `2026_06_28_090907_create_slides_table.php` — needs new columns |
| Admin Controller | ✅ Exists | `Admin\SliderController` — needs: reorder, cache invalidation, mobile image upload |
| Admin Routes | ✅ Exists | Resource + toggle — needs: reorder route |
| Admin Views | ✅ Exists | index, create, edit — need: mobile image field, description, scheduling, button_target, drag-drop |
| Frontend Hero | ⚠️ Partial | Shows only first slide as static image — needs full carousel |
| HomeController | ✅ Exists | Fetches all active slides — needs Redis caching |
| Redis Config | ✅ Exists | Configured but default cache is `database` — must use `Cache::store('redis')` |
| Translations | ✅ Exists | Arabic — need new keys for new fields |

---

## Phase 1: Database & Model

### 1.1 Create Migration for New Columns

**File:** `database/migrations/2026_08_09_add_slider_fields_to_slides_table.php`

```php
Schema::table('slides', function (Blueprint $table) {
    $table->string('mobile_image')->nullable()->after('image');
    $table->text('description')->nullable()->after('subtitle');
    $table->enum('button_target', ['_same', '_blank'])->default('_same')->after('button_text');
    $table->timestamp('starts_at')->nullable()->after('is_active');
    $table->timestamp('ends_at')->nullable()->after('starts_at');
    $table->index(['is_active', 'starts_at', 'ends_at', 'sort_order']);
});
```

### 1.2 Update Slide Model

**File:** `app/Models/Content/Slide.php`

```php
protected $fillable = [
    'title', 'subtitle', 'description', 'badge',
    'image', 'mobile_image', 'link', 'btn_text', 'button_target',
    'sort_order', 'is_active', 'starts_at', 'ends_at',
];

protected $casts = [
    'is_active' => 'boolean',
    'sort_order' => 'integer',
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
];

// Scopes
public function scopeActive($query) { ... }
public function scopeOrdered($query) { ... }
public function scopeVisible($query) { ... } // applies scheduling logic

// Accessors
public function getImageUrlAttribute(): ?string { ... }
public function getMobileImageUrlAttribute(): ?string { ... }
public function getEffectiveMobileImageUrlAttribute(): ?string { // fallback to desktop_image }
```

---

## Phase 2: Admin Controller Enhancements

### 2.1 Update SliderController

**File:** `app/Http/Controllers/Admin/SliderController.php`

Add/Modify:
- `reorder()` — handle drag-and-drop reordering (expects `items[]` with id + sort_order)
- `store()`/`update()` — handle `mobile_image_file`, `description`, `button_target`, `starts_at`, `ends_at`
- Add `Cache::store('redis')->forget('home.active_sliders')` after every create/update/destroy/toggle/reorder
- Add validation for new fields (dates, image mimes, button_target enum)

### 2.2 Add Reorder Route

**File:** `routes/web.php`

```php
Route::post('/slider/reorder', [App\Http\Controllers\Admin\SliderController::class, 'reorder'])
    ->name('slider.reorder');
```

---

## Phase 3: Admin Views Update

### 3.1 Index View — Drag & Drop

**File:** `resources/views/admin/slider/index.blade.php`

- Add drag handle (`☰`) to each row
- Add SortableJS via Vite or inline script
- On drop, POST to `/admin/slider/reorder` with ordered IDs

### 3.2 Create/Edit Views — New Fields

**Files:** `resources/views/admin/slider/create.blade.php`, `edit.blade.php`

Add fields:
- `description` (textarea)
- `mobile_image_file` (file input + preview)
- `button_target` (radio: _same / _blank)
- `starts_at` / `ends_at` (datetime-local inputs)
- Update existing `image_file` label to "Desktop Image"

---

## Phase 4: Frontend Hero Slider (Carousel)

### 4.1 Update HomeController

**File:** `app/Http/Controllers/HomeController.php`

```php
use Illuminate\Support\Facades\Cache;

$slides = Cache::store('redis')->remember('home.active_sliders', now()->addMinutes(10), fn () =>
    Slide::query()
        ->visible()
        ->ordered()
        ->get()
);
```

### 4.2 Create Hero Slider Component View

**File:** `resources/views/frontend/partials/hero-slider.blade.php`

```blade
@if($slides->isNotEmpty())
<section class="hero-slider" data-autoplay="5000" data-pause-on-hover="true">
    <div class="hero-slider-track" role="list">
        @foreach($slides as $slide)
            <article class="hero-slide" role="listitem" aria-hidden="{{ !$loop->first }}">
                <picture>
                    @if($slide->effective_mobile_image_url)
                        <source media="(max-width: 767px)" srcset="{{ $slide->effective_mobile_image_url }}">
                    @endif
                    <img src="{{ $slide->image_url }}"
                         alt="{{ $slide->title }}"
                         loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                         decoding="async"
                         class="w-full h-full object-cover">
                </picture>
                <div class="hero-content">
                    @if($slide->badge) <span class="badge">{{ $slide->badge }}</span> @endif
                    @if($slide->title) <h1>{{ $slide->title }}</h1> @endif
                    @if($slide->description) <p>{{ $slide->description }}</p> @endif
                    @if($slide->subtitle) <p class="subtitle">{{ $slide->subtitle }}</p> @endif
                    @if($slide->btn_text && $slide->link)
                        <a href="{{ $slide->link }}"
                           target="{{ $slide->button_target }}"
                           class="hero-button">
                            {{ $slide->btn_text }}
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    <nav class="hero-nav" aria-label="Slider navigation">
        <button class="hero-prev" aria-label="Previous">❮</button>
        <div class="hero-dots" role="tablist">
            @foreach($slides as $index => $slide)
                <button role="tab" aria-selected="{{ $index === 0 }}" aria-label="Go to slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        <button class="hero-next" aria-label="Next">❯</button>
    </nav>
</section>
@endif
```

### 4.3 Add Slider JavaScript

**File:** `resources/js/components/hero-slider.js`

Features:
- Auto-play (configurable delay, pause on hover/focus)
- Previous/Next navigation
- Dot pagination (click to go to slide)
- Touch swipe (mobile)
- Keyboard navigation (ArrowLeft/ArrowRight)
- Smooth CSS transitions
- RTL support (direction-aware)

Register in `resources/js/app.js` and build with Vite.

### 4.4 Update Home Blade

**File:** `resources/views/frontend/home.blade.php`

Replace static hero section with:
```blade
@include('frontend.partials.hero-slider', ['slides' => $slides])
```

---

## Phase 5: Redis Cache & Invalidation

### 5.1 Cache Key Strategy

- Key: `home.active_sliders`
- TTL: 10 minutes
- Store: Redis (explicit via `Cache::store('redis')`)

### 5.2 Invalidation Points

In `SliderController`:
- `store()` → `Cache::store('redis')->forget('home.active_sliders')`
- `update()` → same
- `destroy()` → same
- `toggleActive()` → same
- `reorder()` → same

---

## Phase 6: Translations

### 6.1 Add Arabic Keys

**File:** `lang/ar.json`

```json
"admin.slider.mobile_image": "صورة الجوال",
"admin.slider.description": "الوصف",
"admin.slider.button_target": "فتح الرابط",
"admin.slider.button_target_same": "نفس الصفحة",
"admin.slider.button_target_blank": "صفحة جديدة",
"admin.slider.starts_at": "بداية العرض",
"admin.slider.ends_at": "نهاية العرض",
"admin.slider.reorder_success": "تم تحديث الترتيب",
"admin.slider.drag_hint": "اسحب لترتيب",
"admin.slider.preview": "معاينة",
"admin.slider.delete_confirm": "هل أنت متأكد من حذف هذه الشريحة؟",
"admin.slider.current_mobile_image": "صورة الجوال الحالية"
```

---

## Phase 7: Testing & Validation

### 7.1 Manual Test Checklist

- [ ] Admin: Create slide with desktop + mobile images
- [ ] Admin: Edit slide, change only mobile image
- [ ] Admin: Schedule slide with starts_at/ends_at
- [ ] Admin: Toggle active/inactive
- [ ] Admin: Drag-drop reorder → verify sort_order in DB
- [ ] Admin: Delete slide → verify images deleted from storage
- [ ] Frontend: Hero slider loads all active slides
- [ ] Frontend: Auto-play works, pauses on hover
- [ ] Frontend: Prev/Next/Dots navigation works
- [ ] Frontend: Touch swipe works on mobile
- [ ] Frontend: Keyboard arrows work
- [ ] Frontend: Mobile image loads on ≤767px, desktop on >767px
- [ ] Frontend: First slide eager, rest lazy
- [ ] Cache: Second request hits Redis (check via `redis-cli MONITOR`)
- [ ] Cache: After admin edit, cache invalidated (verify fresh data)

### 7.2 Run Commands

```bash
php artisan migrate
php artisan view:clear
php artisan config:clear
npm run build
php artisan test
```

---

## Files to Create / Modify

### New Files
1. `database/migrations/2026_08_09_add_slider_fields_to_slides_table.php`
2. `resources/views/frontend/partials/hero-slider.blade.php`
3. `resources/js/components/hero-slider.js`

### Modified Files
1. `app/Models/Content/Slide.php`
2. `app/Http/Controllers/Admin/SliderController.php`
3. `app/Http/Controllers/HomeController.php`
4. `routes/web.php`
5. `resources/views/admin/slider/index.blade.php`
6. `resources/views/admin/slider/create.blade.php`
7. `resources/views/admin/slider/edit.blade.php`
8. `resources/views/frontend/home.blade.php`
9. `lang/ar.json` (and en.json, fr.json if they exist)

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Redis not default cache | Use `Cache::store('redis')` explicitly |
| Drag-drop JS conflicts | Use lightweight SortableJS via Vite; scope to table only |
| Mobile image fallback | Model accessor returns desktop_image if mobile_image missing |
| Image cleanup on delete | Already implemented in `destroy()`; extend to mobile_image |
| Scheduling edge cases | `scopeVisible()` handles NULL starts_at/ends_at correctly |
| RTL carousel direction | JavaScript reads `document.dir` and inverts prev/next |

---

## Out of Scope (Future Enhancements)

- Slider settings table (`slider_settings`) for global autoplay/delay/transition
- A/B testing for slides
- Slide analytics (impressions, clicks)
- Video slides
- Multi-language slide content (currently single-language)

---

## Definition of Done

Admin can:
1. Create slide with desktop + mobile images, title, description, badge, CTA, link target, schedule, sort order
2. Edit any field, replace images independently
3. Drag-drop reorder → persists immediately
4. Toggle active/inactive without page reload
5. Delete with confirmation → cleans up storage + cache
6. Preview slide before publish

Frontend shows:
1. Responsive carousel with all active+scheduled slides
2. Mobile images on ≤767px, desktop on >767px
3. Auto-play, pause on hover/focus
4. Prev/Next arrows, dot pagination, touch swipe, keyboard nav
5. First slide eager-loaded, rest lazy
6. Graceful degradation if JS fails (first slide visible)

Cache:
1. Redis key `home.active_sliders` with 10min TTL
2. Invalidated on all admin mutations