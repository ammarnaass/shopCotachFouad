# AN Shop — Gap-Analysis & Implementation Plan (Phase 2+)

## 0. Context Recap

**Project state today** (from earlier inspection):
- Laravel 13 app at `G:\project progamming\shopCotachFouad\` exists and is running on `localhost:8000`.
- Brand is "Fouad Muscle Zone" (supplements); user chose to **keep this identity** rather than rename to "AN Shop".
- Stack: Laravel 13, MySQL, Redis (predis), Blade, Tailwind v4, Vite, Alpine.js, Sanctum.
- Most **frontend pages already exist** (home, shop, product, cart, wishlist, checkout, account) and **work** on a fixed desktop-first layout.
- Existing controllers: `HomeController`, `ShopController`, `CartController`, `WishlistController`, `InstantBuyController`, `OrderController`, `AccountController`, `AuthController`, `PageController`, `NewsletterController` + a full `app/Http/Controllers/Admin/*` suite.
- Existing models (`Product`, `Category`, `Order`, etc.), migrations (40+ tables), and services (`CartService`, `ProductService`, `PricingService`, `CouponService`, `DynamicShippingService`, `TranslationService`, `SettingsService`) already cover most of the PRD's data and business logic.
- Existing helper `site()` for settings; existing `__t()` for translations.
- Already in place: Redis cache for settings/translations/shipping, CSRF, validation in controllers, role middleware for admin, cash-on-delivery flow via `InstantBuyController`.
- Several **partial refactors already applied** during this session:
  - `frontend/home.blade.php` hero, categories grid, featured/latest product grids simplified and made responsive.
  - `frontend/partials/header.blade.php` rewritten — but it lost functionality (no live search, no wishlist/cart counters, no user menu, no login link, no account, no theme/lang/currency switcher) and still has PHP parsing risk if the controller does not pass `$cartCount`/`$wishlistCount`.
  - `frontend/partials/product-card.blade.php` partially updated; review-only fields still reference review count/avg that must be eager-loaded.

**Therefore this plan is a remediation plan, not a rebuild.** Goal: keep what works, align remaining gaps with the PRD, avoid destructive rewrites.

---

## 1. Out of Scope (explicit)

- Renaming the brand from "Fouad Muscle Zone" to "AN Shop" (user decision).
- Migrating cart storage from MySQL → Redis (MySQL is the durable source; we will add Redis as a hot cache, per PRD §23).
- Adding PWA / mobile app / multi-vendor / multi-currency expansion (future).
- New payment gateways (current COD is sufficient; payment-method table already supports extensibility).

---

## 2. Goal & Constraints (PRD-aligned)

- **Mobile-First responsive** UI on every storefront page; no horizontal scroll at 320 / 360 / 390 / 430 / 768 / 1024 / 1280 / 1536.
- **RTL** Arabic-first (`<html lang="ar" dir="rtl">`). Use Tailwind logical utilities (`ms-/me-/ps-/pe-/start/end`).
- **Progressive enhancement**: every critical action (search, add to cart, checkout, wishlist) is a normal HTML form. JS only adds UX.
- **No business logic in Blade.** Queries live in controllers or services. Blade has no raw `Model::` calls.
- **Validation** moves from inline `$request->validate(...)` to `FormRequest` classes for storefront flows (Cart, Wishlist, Checkout/InstantBuy, Newsletter, Account).
- **Server-side price recomputation** in cart/checkout — never trust client `price/total`.
- **Redis cache** for hot reads (featured products, homepage sections, settings) with proper invalidation on writes.
- **No N+1**: every listing/cart uses eager loading (`with(...)`, `withCount(...)`).
- **One feature per commit.**

---

## 3. Critical Files & Boundaries

### Frontend
- `resources/views/frontend/layout.blade.php` — global shell; loads Vite, Alpine, fonts, meta.
- `resources/views/frontend/partials/header.blade.php` — **needs safe-restore** (lost features).
- `resources/views/frontend/partials/footer.blade.php` — keep; only audit responsive grid + RTL.
- `resources/views/frontend/home.blade.php` — already touched; finalize hero/categories/products sections.
- `resources/views/frontend/shop/index.blade.php` & `category.blade.php` — product listing, filters, sort, pagination.
- `resources/views/frontend/shop/show.blade.php` — product detail (gallery, options, related).
- `resources/views/frontend/cart/index.blade.php` — cart UI.
- `resources/views/frontend/instant/buy.blade.php` — checkout flow (COD).
- `resources/views/frontend/wishlist/index.blade.php`, `orders/index.blade.php`, `orders/show.blade.php`, `account/index.blade.php`.

### Backend
- `app/Http/Controllers/{HomeController,ShopController,CartController,WishlistController,InstantBuyController,OrderController,AccountController}.php`
- `app/Services/{ProductService,CartService,CouponService,DynamicShippingService,PricingService,TranslationService,SettingsService}.php`
- `app/Models/Catalog/{Product,Category,ProductImage}.php`, `app/Models/Order/*.php`, `app/Models/Cart/*.php`.
- `routes/web.php` (locale prefix + admin).

### Assets
- `resources/css/app.css`, `resources/js/app.js` (Alpine + global cart/wishlist stores live in `frontend/partials/alpine-components.blade.php`).

---

## 4. Implementation Tasks (ordered, small, committable)

### Phase A — Foundation / Safety (must complete before visual work)

**A1. Restore safe header without losing features**
- Re-integrate into `frontend/partials/header.blade.php`:
  - Desktop nav (Home, Products, Categories, Offers/Featured, Pages).
  - Live search input + suggestions dropdown (Alpine `liveSearch`) — keep existing Alpine component if present; otherwise add to `alpine-components.blade.php`.
  - Wishlist icon with `$wishlistCount` badge.
  - Cart icon with `$cartCount` badge.
  - User menu: login link (guest) / avatar dropdown (auth) with my account, orders, wishlist, logout.
  - Currency + language switchers (existing in old header).
  - Mobile drawer with Home, Products, Categories, Offers, Login/My Account, Wishlist, Cart.
- Push `$cartCount` and `$wishlistCount` from `AppServiceProvider::boot()` view-composer so every page (including `/cart`, `/checkout`, error pages) has them without per-controller work.
- Sticky `top-0 z-40`, mobile drawer with RTL-aware side (`is_rtl()`).
- Add `min-h-[44px]` to interactive elements (mobile UX §35).
- Validate: `php -l` the file; manual check at 320/768/1280.

**A2. Remove N+1 / leaks**
- `HomeController@index`: pass through `ProductService::getFeatured(8)` (already does so), but ensure `->with('primaryImage')->withCount('reviews')` is eager. Same for `latestProducts`. For `categories`, eager-load `children` and `withCount('products')`.
- `ShopController@index` / `category`: `with(['primaryImage','category'])->withCount('reviews')`.
- `CartController@index`: already eager-loads `product.primaryImage` — verify.

**A3. View composer for header counts**
- Create `App\Providers\HeaderViewServiceProvider` (or extend existing `AppServiceProvider::boot()`) to share:
  - `$cartCount` = `CartService::getCount()` (lightweight query, single int).
  - `$wishlistCount` = `auth()->check() ? Wishlist::where('user_id', auth()->id())->count() : 0`.
  - `$headerNavCategories` = cache-remembered `Category::active()->topLevel()->orderBy('order')->limit(6)->get(['id','name','slug'])`.

**A4. Redis cache for homepage sections**
- `ProductService::getFeatured()`: wrap in `Cache::remember('home.featured', 600, ...)`.
- `CategoryService` (or inline in `HomeController` via service): `Cache::remember('home.categories', 1800, ...)`.
- Add `Cache::forget('home.featured'|'home.categories')` in `App\Models\Catalog\Product` and `Category` model `saved/deleted` observers (or `boot()`).
- Header nav categories already cached by `SettingsService` keys if they live in `settings` table; keep.

**A5. Form Requests for storefront**
- Create `App\Http\Requests\Web\AddToCartRequest`, `UpdateCartRequest`, `ApplyCouponRequest`, `CheckoutRequest` (InstantBuy), `WishlistToggleRequest`, `NewsletterSubscribeRequest`, `ContactFormRequest`.
- Replace inline `$request->validate(...)` in `CartController`, `WishlistController`, `NewsletterController`, `InstantBuyController`, `InstantBuyOrderController` (already uses `InstantBuyRequest`), `PageController::track`.

### Phase B — Pages

**B1. Homepage (`home.blade.php`)**
- Already partially done in session: simplified hero, categories 2/3/4/5/6 grid, featured & latest 2/3/4/5. Final pass:
  - Replace remaining `min-h-[…]` with `aspect-[16/9]` + `object-cover` per PRD §7.
  - Add `loading="lazy"` to all non-LCP images.
  - Add `fetchpriority="high"` on hero image if above the fold.
  - Add `defer` to non-critical inline scripts in `@push('scripts')`.
- Add empty states in partials: `product-card.blade.php` should handle missing primary image with a fallback icon (already does).

**B2. Product Listing (`shop/index.blade.php`, `category.blade.php`)**
- Mobile: filters open as a bottom sheet / drawer triggered by a "Filters" button; Tailwind only (no JS lib).
- Desktop: filters in a left sidebar.
- Sort options: latest, price asc, price desc, best-selling (use `withCount('orders')` or a sales counter column — if column missing, fall back to `views_count` or simply newest).
- Pagination already exists (`->paginate(12)`); verify responsive pagination component.
- Add `loading="lazy"` to product cards beyond the first row.

**B3. Product Details (`shop/show.blade.php`)**
- Mobile: gallery first, info below.
- Desktop: gallery left (≥`lg:grid-cols-2`), info right.
- Show: SKU, price (current + old strikethrough), discount %, stock status, quantity selector, add-to-cart, related products (same category, `where('id','!=',$id)->limit(4)`).
- Add Product JSON-LD schema (`@push('head')`) with `@type Product`, `offers.price`, `availability`.
- Ensure alt text on every gallery image.

**B4. Cart (`cart/index.blade.php`)**
- Already functional with Alpine + Forms. Audit:
  - Mobile: items stacked, summary at bottom as a sticky panel (or bottom CTA).
  - Remove product: form-based DELETE (already).
  - Quantity: `<input type="number" min="1" max="{{ stock }}">` with debounced PATCH (Alpine).
  - Server must **recompute** subtotal/discount/total on every update (`CartService`).

**B5. Checkout (`instant/buy.blade.php`)**
- Fields: full name, phone, country (DZ default), wilaya (DB-backed), commune (wilaya-scoped), address, notes, payment method (COD default).
- Remove any hidden `price/total` fields from form; backend recalculates from cart.
- Verify `InstantBuyController::submit` re-resolves every line item from DB and refuses if `stock < qty`.

**B6. Wishlist (`wishlist/index.blade.php`)**
- 2/3/4/5/6 responsive grid using same `product-card.blade.php` partial.
- Empty state with CTA back to shop.

**B7. Account / Orders (`account/index.blade.php`, `orders/index.blade.php`, `orders/show.blade.php`)**
- Mobile-first two-column on desktop, single column on mobile.
- Order detail shows **snapshot** fields from `order_items` (product_name, sku, unit_price, qty, subtotal) — confirm these columns exist on `order_items` migration; if not, add migration.

**B8. Error pages**
- Create responsive 404 / 403 / 419 / 429 / 500 in `resources/views/errors/`. Use the global container, brand colors, "Go home" CTA.

### Phase C — Performance & SEO

**C1. Eager loading sweep**
- Controllers in §A2 already covered. Add `ProductService::search()` that returns `Product::active()->with(['primaryImage','category'])->paginate(...)`.
- `WishlistController@index`: eager-load `product.primaryImage`.

**C2. Image optimization**
- If `Spatie\Image\Intervention\Laravel` is not present, do not add a dependency. Instead, ensure `primaryImage` accessor returns a path; the `<img>` uses `srcset` with 1×/2× sizes if multiple variants exist; otherwise skip.
- Add `loading="lazy"` and `decoding="async"` everywhere except the hero LCP image.

**C3. SEO**
- `Product` detail page: title/description/canonical/og, JSON-LD `Product` schema (B3).
- Add `sitemap.xml` route + controller (uses `Spatie\Sitemap` only if already present; otherwise emit XML by hand from existing models).
- `robots.txt` route that allows everything except `/admin/*`, `/account/*`, `/cart`, `/checkout`.

**C4. RTL audit**
- Grep for `ml-`, `mr-`, `pl-`, `pr-`, `text-left`, `text-right` and replace with `ms-`, `me-`, `ps-`, `pe-`, `text-start`, `text-end` where they affect direction.
- Use `start-*` / `end-*` for absolute positioning.

**C5. Accessibility audit**
- All icons-only buttons get `aria-label`.
- Form fields have `<label>` (or `aria-label` if visually hidden).
- Skip-to-content link in `layout.blade.php`.
- Focus ring visible (`focus-visible:ring-2 focus-visible:ring-primary`).
- Color contrast: keep text `text-gray-700+` on white.

### Phase D — Verification

For each commit:
1. `php -l` on changed PHP files.
2. `php artisan view:clear`.
3. `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/ar` → expect `200`.
4. `php artisan test` — keep existing test suite green; add one feature test per new FormRequest (smoke).
5. Manual responsive check at 320 / 768 / 1280.
6. Lighthouse / DevTools: no horizontal scroll, no console errors.

---

## 5. Risks & Mitigations

- **Risk:** Header rewrite regresses desktop navigation.
  **Mitigation:** A1 view-composer + restore every old widget before adding new behaviour. Use `git diff` to compare with backup `header.blade.php.backup*`.
- **Risk:** Locale prefix routing breaks if we touch `web.php`.
  **Mitigation:** No new locale routing; all changes inside `Route::prefix('{locale?}')` group or under `/admin` group.
- **Risk:** Cart migration regression (totals out of sync).
  **Mitigation:** B4 keeps MySQL as source of truth; only add Redis as a 60s cache. Backend always recomputes.
- **Risk:** MySQL `order_items` lacks snapshot columns.
  **Mitigation:** Confirm schema first; add minimal additive migration (never drop columns).
- **Risk:** Alpine.js removed → regressions.
  **Mitigation:** Phase A only adds JS; never removes existing Alpine components. Cart/wishlist stores stay.

---

## 6. Validation Plan (Definition of Done)

A task is **done** when ALL of the following hold:
- Mobile (320–430 px), tablet (768 px), laptop (1024+ px), desktop (1536 px) render without horizontal scroll.
- RTL `dir="rtl"` is intact and Tailwind logical utilities are used in changed lines.
- No Laravel exception in `storage/logs/laravel.log` after browsing homepage → product → add-to-cart → checkout.
- `php artisan test` passes.
- Modified files limited to the task's scope.
- One commit per task, message format: `feat(<scope>): <summary>`.

---

## 7. Open Questions for the User (one at a time)

None for now — scope is set. Next question will arise only if a real blocker is hit (e.g., `order_items` snapshot columns missing).

---

## 8. Recommended Next Implementation Step

Switch to an implementation-capable agent and start with **A1 (restore header)** and **A3 (view-composer)** as a single commit `feat(header): restore features + share cart/wishlist counts`. This is the smallest reversible change that re-enables header functionality before any other Phase B work.
