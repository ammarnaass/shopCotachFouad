# Homepage Sections: Show/Hide + Reorder from Admin

## Context

The homepage (`resources/views/frontend/home.blade.php`) has 7 sections rendered in a **hardcoded order**:

1. Hero Slider
2. Marquee Features (trust badges)
3. Categories Grid
4. Featured Products
5. Latest Products
6. CTA Banner 1
7. Banner 2

Currently only 4 sections have show/hide toggles (`show_categories`, `show_featured`, `show_latest`, `show_newsletter`). The order is fixed in the blade template. The user wants **full control**: show/hide every section + reorder them from admin.

## Approach

Store section order as a JSON array in settings. The admin UI shows a sortable list with toggles. The homepage blade iterates over the stored order and renders each section conditionally.

## New Settings

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `home_section_order` | JSON array | `["hero","marquee","categories","featured","latest","banner_1","banner_2"]` | Ordered list of section keys |
| `show_hero` | `'1'`/`'0'` | `'1'` | Show/hide hero slider |
| `show_marquee` | `'1'`/`'0'` | `'1'` | Show/hide trust badges marquee |
| `show_banner_1` | `'1'`/`'0'` | `'1'` | Show/hide CTA banner |

Existing toggles reused: `show_categories`, `show_featured`, `show_latest`, `show_newsletter`.

## Changes

### 1. `app/Http/Controllers/Admin/CustomizeController.php`

**`index()` — add to `$current` array:**
```php
'home_section_order' => $this->settings->get('home_section_order', '["hero","marquee","categories","featured","latest","banner_1","banner_2"]'),
'show_hero' => $this->settings->get('show_hero', '1'),
'show_marquee' => $this->settings->get('show_marquee', '1'),
'show_banner_1' => $this->settings->get('show_banner_1', '1'),
```

**`update()` — add validation:**
```php
'home_section_order' => 'nullable|string|max:500',
'show_hero' => 'boolean',
'show_marquee' => 'boolean',
'show_banner_1' => 'boolean',
```

**`$checkboxKeys` — add:**
```php
'show_hero', 'show_marquee', 'show_banner_1',
```

**In the save loop, handle `home_section_order`:**
```php
if ($key === 'home_section_order') {
    $decoded = json_decode($value, true);
    if (!is_array($decoded)) $decoded = ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
    $this->settings->set($key, json_encode($decoded), 'customize');
    continue;
}
```

### 2. `resources/views/admin/customize/index.blade.php`

Add a new **"Homepage Sections"** tab (or add to the existing "Sections" tab). The tab contains:

- A **sortable list** of section items (using Alpine.js + `x-sortable` or simple up/down arrows)
- Each item has: drag handle, section name, show/hide toggle
- Hidden input stores the JSON order: `<input type="hidden" name="home_section_order" id="home_section_order">`

Section definitions (hardcoded in the blade):
```php
$homeSections = [
    'hero' => ['label' => 'السلايدر الرئيسي', 'icon' => 'view_carousel'],
    'marquee' => ['label' => 'شار trust badges', 'icon' => 'verified'],
    'categories' => ['label' => 'الأقسام', 'icon' => 'category'],
    'featured' => ['label' => 'منتجات مميزة', 'icon' => 'star'],
    'latest' => ['label' => 'أحدث المنتجات', 'icon' => 'new_releases'],
    'banner_1' => ['label' => 'بانر رئيسي', 'icon' => 'campaign'],
    'banner_2' => ['label' => 'بانر ثانوي', 'icon' => 'campaign'],
];
```

### 3. `resources/views/frontend/home.blade.php`

Replace the hardcoded section order with a dynamic loop:

```php
@section('content')
    @php
        $sectionOrder = json_decode(site('home_section_order', '["hero","marquee","categories","featured","latest","banner_1","banner_2"]'), true);
        if (!is_array($sectionOrder)) {
            $sectionOrder = ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
        }
    @endphp

    @foreach($sectionOrder as $section)
        @if($section === 'hero' && site('show_hero', '1') === '1')
            {{-- Hero slider code --}}
        @elseif($section === 'marquee' && site('show_marquee', '1') === '1')
            {{-- Marquee code --}}
        @elseif($section === 'categories' && site('show_categories', '1') === '1' && $categories->count() > 0)
            {{-- Categories code --}}
        @elseif($section === 'featured' && site('show_featured', '1') === '1' && $featuredProducts->count() > 0)
            {{-- Featured products code --}}
        @elseif($section === 'latest' && site('show_latest', '1') === '1' && $latestProducts->count() > 0)
            {{-- Latest products code --}}
        @elseif($section === 'banner_1' && site('show_banner_1', '1') === '1')
            {{-- Banner 1 code --}}
        @elseif($section === 'banner_2' && (site('banner_2_title') || site('banner_2_image')))
            {{-- Banner 2 code --}}
        @endif
    @endforeach
@endsection
```

### 4. Translation Files

Add keys to `lang/ar.json`, `lang/en.json`, `lang/fr.json`:

```json
"admin.customize.tab_homepage": "الأقسام",
"admin.customize.homepage_sections": "أقسام الصفحة الرئيسية",
"admin.customize.section_hero": "السلايدر الرئيسي",
"admin.customize.section_marquee": "المميزات",
"admin.customize.section_categories": "الأقسام",
"admin.customize.section_featured": "منتجات مميزة",
"admin.customize.section_latest": "أحدث المنتجات",
"admin.customize.section_banner_1": "بانر رئيسي",
"admin.customize.section_banner_2": "بانر ثانوي"
```

### 5. Clear Cache

```bash
php artisan view:clear && php artisan cache:clear
```

## Files Modified

| File | Change |
|------|--------|
| `app/Http/Controllers/Admin/CustomizeController.php` | Add 3 new settings to defaults + validation + checkboxes |
| `resources/views/admin/customize/index.blade.php` | Add sortable section list with toggles in homepage tab |
| `resources/views/frontend/home.blade.php` | Replace hardcoded order with dynamic `@foreach` loop |
| `lang/ar.json` | Add section translation keys |
| `lang/en.json` | Add section translation keys |
| `lang/fr.json` | Add section translation keys |

## Validation

1. Admin → Customize → Homepage tab: reorder sections → Save → verify order changes on frontend
2. Toggle a section off → Save → verify it disappears from homepage
3. Toggle all off → only footer/nav should remain
4. Default order should match current layout (no visual regression on fresh install)
5. Run `php artisan view:clear && php artisan cache:clear`
