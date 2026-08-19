# Hero Slider Bug Fix Plan

## Issues Found (4 remaining)

### 1. ~~Route [slider.reorder] not defined~~ — ALREADY FIXED
- `index.blade.php:152` now uses `route('admin.slider.reorder')` (correct name under `admin.` group prefix)
- View and route caches cleared

### 2. Homepage slider `__PHP_Incomplete_Class` — ALREADY FIXED
- `HomeController.php:42` now uses `->toArray()` before caching to Redis
- Stale Redis cache cleared via `Cache::store('redis')->forget('home.active_sliders')`
- `hero-slider.blade.php` uses array functions (`!empty()`, `count()`) instead of Eloquent methods

### 3. SortableJS not loaded — NEEDS FIX
**Problem:** `index.blade.php:137` checks `typeof Sortable !== 'undefined'` but SortableJS is never loaded anywhere — not in `app.js`, not via CDN. Drag-drop reorder silently doesn't work.

**Fix:** Install SortableJS via npm and import it in `app.js`:
```
npm install sortablejs
```
Then in `resources/js/app.js`:
```js
import Sortable from 'sortablejs';
window.Sortable = Sortable;
```

### 4. Toggle route method mismatch — NEEDS FIX
**Problem:** `index.blade.php:68` sends `@method('PATCH')` but `web.php:300` defines the route as `Route::post(...)`. This causes a 405 Method Not Allowed when clicking the toggle button.

**Fix:** Change the route in `web.php:300` from `Route::post` to `Route::patch`:
```php
Route::patch('/slider/{slider}/toggle', ...)->name('slider.toggle');
```

### 5. Existing slide has empty `link` — DATA ISSUE (not code)
The only slide in DB has `btn_text: "اطلب الان"` but `link: null`. The CTA button won't render because `hero-slider.blade.php:66` checks both `btn_text` AND `link`. User should fill the `link` field via admin edit form.

## Execution Order
1. Fix toggle route: `Route::post` → `Route::patch` in `routes/web.php:300`
2. Install SortableJS: `npm install sortablejs`
3. Import SortableJS in `resources/js/app.js`
4. Rebuild assets: `npm run build`
5. Clear caches: `php artisan view:clear && php artisan route:clear`
6. Verify admin slider page loads without errors
7. Verify homepage slider renders
