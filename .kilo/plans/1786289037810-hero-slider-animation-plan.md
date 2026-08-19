# Hero Slider Animation Enhancement Plan

## Context

The hero slider feature **already exists** and is fully functional for CRUD management. This plan adds the **animation** capability ("المتحركة") that is currently missing.

### Existing implementation (no changes needed to core CRUD)
- **Model**: `app/Models/Content/Slide.php` — fields: title, subtitle, description, badge, image, mobile_image, link, btn_text, button_target, sort_order, is_active, starts_at/ends_at
- **Controller**: `app/Http/Controllers/Admin/SliderController.php` — store/update use `->toArray()` (new fields auto-included once in model)
- **Admin views**: `admin/slider/{index,create,edit}.blade.php` (index uses SortableJS for reorder)
- **Frontend**: `frontend/partials/hero-slider.blade.php` + `resources/js/components/hero-slider.js`
- **Home**: `HomeController.php:39-44` loads `Slide::visible()->ordered()->get()->toArray()` via Redis cache (`home.active_sliders`, 10 min TTL)
- **Routes**: `admin.slider` resource + toggle + reorder (`routes/web.php:299-301`)

### What's missing
1. Only a basic **fade** transition exists — no slide/zoom/flip transition effects
2. Content elements (badge, title, subtitle, description, button) appear instantly — no entrance animations
3. No per-slide animation configuration in admin
4. **Translation gaps**: `en.json` and `fr.json` missing many slider/`home.*` keys (ar.json is the source of truth)
5. No seeder for sample slides; no tests for the slider

## Resolved Design Decisions

**D1 — Per-slide fields** (stored on `slides` table):
- `animation_effect` (string, default `fade`) — slide transition: `fade`, `slide-left`, `slide-right`, `zoom`, `flip`
- `entrance_effect` (string, default `fade-up`) — content entrance: `none`, `fade-up`, `fade-down`, `fade-left`, `fade-right`, `zoom`

**D2 — Global timing settings** (via `SettingsService` / `site()` helper, in Customize → sections tab):
- `slider_animation_duration` (int ms, default 500) — transition duration
- `slider_entrance_stagger` (int ms, default 80) — delay between content element entrances

**D3 — Transition effects** implemented via CSS classes + JS. The JS reads `data-effect` per slide; direction-aware for slide-left/right (RTL flips).

**D4 — Entrance animations** applied to content children with `data-animate-index` (0=badge, 1=title, 2=subtitle, 3=description, 4=button). JS sets `animation-delay = index × stagger`. Re-triggers on each slide activation by removing/re-adding classes.

**D5 — RTL**: `slide-left`/`slide-right` flip direction in RTL (existing `isRtl` check in hero-slider.js handles this).

**D6 — Defaults**: Migration sets defaults; model `$casts` + constants for validation. Controller falls back to global `site()` settings when a slide field is empty.

## Ordered Tasks

### 1. Migration — new columns on `slides`
File: `database/migrations/2026_08_09_200000_add_animation_fields_to_slides_table.php`
- Add `string('animation_effect')->default('fade')`
- Add `string('entrance_effect')->default('fade-up')`
- Indexes on both (for query clarity)

### 2. Model — `app/Models/Content/Slide.php`
- Add `animation_effect`, `entrance_effect` to `$fillable`
- Add accessor `getAnimationEffectAttribute` / `getEntranceEffectAttribute` that falls back to `site()` global defaults
- Add class constants `ANIMATION_EFFECTS` and `ENTRANCE_EFFECTS` arrays (for reuse in views/controllers)

### 3. Global settings — wire into Customize
File: `app/Http/Controllers/Admin/CustomizeController.php`
- Add `slider_animation_duration` and `slider_entrance_stagger` to `$current` array (keys + defaults: 500, 80)
- Add validation rules (`nullable|integer|min:100|max:2000` for duration; `min:10|max:300` for stagger)
- Persist in `update()` loop (add to checkboxKeys exclusion or plain foreach — these are scalar, not checkboxes)

### 4. Admin form — create & edit views
Files: `admin/slider/create.blade.php`, `admin/slider/edit.blade.php`
- Add `animation_effect` <select> with `ANIMATION_EFFECTS` options (with a short description/tooltip per option)
- Add `entrance_effect` <select> with `ENTRANCE_EFFECTS` options
- Edit view: pre-select current value

### 5. Admin index — show effect column (optional)
File: `admin/slider/index.blade.php`
- Add a small column showing the animation effect icon/label (optional, keep minimal)

### 6. Frontend blade — pass animation data to DOM
File: `frontend/partials/hero-slider.blade.php`
- Add `data-effect="{{ $slide['animation_effect'] ?? 'fade' }}"` on each `.hero-slide`
- Add `data-entrance="{{ $slide['entrance_effect'] ?? 'fade-up' }}"` on the content container
- Add `data-animate-index="0".."4"` on badge, title, subtitle, description, button
- Pass global timing via `data-duration` and `data-stagger` on the `.hero-slider` root

### 7. CSS — animation keyframes + transition classes
File: `resources/css/app.css`
- Add keyframes: `slide-left-in`, `slide-left-out`, `slide-right-in`, `slide-right-out`, `zoom-in`, `zoom-out`, `flip-in`, `flip-out`, `fade-down`, `fade-left`, `fade-right`, `zoom-enter`
- Add classes: `.hero-slide-enter`, `.hero-slide-exit` variants per effect; `.hero-content-enter` variants
- Reuse existing `fade-in`, `fade-up` where possible

### 8. JS — extend HeroSlider for transitions + entrance
File: `resources/js/components/hero-slider.js`
- Constructor: read `data-duration` and `data-stagger` from root element
- `goToSlide()`: apply exit class to outgoing slide + enter class to incoming slide based on `data-effect`; listen for `transitionend`/`animationend`; on enter-complete, trigger content entrance animations with staggered delays
- `triggerEntrance()` on active slide's content children: set `animation-delay` per `data-animate-index`
- Re-trigger entrance when revisiting a slide (remove animation classes first)
- Guard against `prefers-reduced-motion`

### 9. Translations — add missing keys to all 3 locales
- Add new keys: `admin.slider.animation_effect`, `admin.slider.entrance_effect`, option labels, duration/stagger labels
- Backfill missing keys in `en.json` and `fr.json` from `ar.json` (the source of truth)

### 10. Seeder — sample animated slides
File: `database/seeders/SliderSeeder.php`
- 2-3 sample slides with varied effects (fade, slide-left, zoom)
- Call from `DatabaseSeeder.php` (add `$this->call(SliderSeeder::class)`)

### 11. Tests — feature tests
File: `tests/Feature/SliderTest.php`
- Admin can create slide with `animation_effect`/`entrance_effect`
- Admin can update animation fields
- Frontend home renders slides with `data-effect` attribute
- Toggle active / delete works (existing behavior preserved)
- Follow pattern from `tests/Feature/PageTest.php` (uses `User::factory()`, `RefreshDatabase`)

## Files Affected (summary)

| Layer | File |
|-------|------|
| DB | `database/migrations/2026_08_09_200000_add_animation_fields_to_slides_table.php` (new) |
| Model | `app/Models/Content/Slide.php` |
| Admin controller | `app/Http/Controllers/Admin/CustomizeController.php` |
| Admin views | `admin/slider/create.blade.php`, `admin/slider/edit.blade.php`, `admin/slider/index.blade.php` |
| Frontend view | `frontend/partials/hero-slider.blade.php` |
| Frontend JS | `resources/js/components/hero-slider.js` |
| CSS | `resources/css/app.css` |
| Translations | `lang/ar.json`, `lang/en.json`, `lang/fr.json` |
| Seeder | `database/seeders/SliderSeeder.php` (new) + `DatabaseSeeder.php` |
| Tests | `tests/Feature/SliderTest.php` (new) |

## Risks & Edge Cases

1. **Animation + autoplay race**: transition duration (500ms) must be shorter than autoplay interval (5000ms) — safe with defaults, but validate user-set duration doesn't exceed autoplay.
2. **Reduced motion**: respect `prefers-reduced-motion` — disable entrance animations, keep simple fade.
3. **RTL direction flip**: slide-left/slide-right must invert in `dir="rtl"`.
4. **Content re-animation**: when cycling back to a previously-shown slide, entrance animations must replay — requires removing animation classes after delay.
5. **Cached slides**: `HomeController` caches `->toArray()` for 10 min — animation fields are included automatically; cache invalidation already fires on save via `invalidateCache()`.

## Open Questions

None — all key design decisions resolved above. The animation approach (CSS classes driven by JS) follows the existing vanilla-JS pattern already established in `hero-slider.js`.

## Validation Plan

1. `php artisan migrate` — new columns apply cleanly
2. `php artisan db:seed --class=SliderSeeder` — sample slides insert with effects
3. `npm run build` — Vite compiles updated CSS + JS
4. `php artisan test --filter=SliderTest` — feature tests pass
5. Manual: visit `/ar` (or `/en`, `/fr`) — observe slide transitions and content entrance animations; visit `/ar/admin/slider` — create/edit form shows animation dropdowns and saves correctly
