# Product card: gold-star rating + ringed "Press here to order" CTA

## Context
The client asked (in Arabic): "أضف نجوم فوق، ذهبية فوق سعر مع مستطيل حول طلمة اضغط هنا للطلب" — add a
gold star rating above the price on each product card, and frame the "اضغط هنا للطلب" (press here
to order) CTA with a rectangle (ring). This is a deliberate visual identity change for the product
card, the most conversion-critical unit on the storefront. The card lives in
`resources/views/frontend/partials/product-card.blade.php` and is reused on home, shop index,
category, search, wishlist, and related-products surfaces.

## Design direction (the single deliberate risk)
**Risk:** the price is set in italic *Instrument Serif*, not the AI-default bold sans. The gold star
row at the top and the gold hairline ring on the CTA at the bottom share one warm `#E8B341`, so the
card reads top-to-bottom as *trust → price → action*, tied by a single warm color. Italic serif
price + ringed CTA in matching gold is opinionated and specific to a retail card; it would not
appear on a SaaS page. All other AI defaults (cream + terracotta, black + acid-green, dense
broadsheet) are rejected because they would fight the gold and the warm retail tone.

## Tokens (added to `resources/css/app.css`)
Use 6 named CSS custom properties on `.product-card` so the design stays a unit, not scattered:

```css
--pc-ink: #0E1116;          /* near-black, sharper than gray-900 */
--pc-ink-soft: #5B6271;      /* utility text (rating count) */
--pc-star: #E8B341;          /* antique gold (warm, not yellow) */
--pc-star-empty: #2A2F3A;    /* empty-star tint so row still reads as 5-star meter */
--pc-cta: #0E1116;           /* CTA fill */
--pc-cta-text: #F4EFE6;      /* off-white on CTA */
--pc-ring: var(--pc-star);   /* CTA ring matches the stars */
--pc-paper: #FFFFFF;         /* card surface */
--pc-radius: 14px;
```

(Do **not** reuse the existing `--color-primary` blue or the `--color-accent` orange — they would
compete with the gold.)

## Typography
- **Price:** Instrument Serif italic 600, `font-size: 1.375rem`, `line-height: 1.1`,
  `letter-spacing: -0.01em`, color `--pc-ink`. Struck-through compare price in Inter 500 0.8125rem
  `--pc-ink-soft` with `text-decoration: line-through`.
- **CTA label:** Inter 600 0.9375rem `--pc-cta-text`, `letter-spacing: 0.02em` so the two-word
  Arabic phrase reads as a unit.
- **Rating count + meta:** Inter 500 0.75rem `--pc-ink-soft`.

## Layout (the affected card region, ASCII)
```
┌────────────────────────────────────────┐
│ [image]    ● NEW    -30%               │
├────────────────────────────────────────┤
│ Product Name (line-clamp 2)            │
│                                        │
│ ★ ★ ★ ★ ★  (128)                      │  ← new rating row
│                                        │
│ 1,250 DA   ̶3̶,200̶ DA                  │  ← price, italic serif
│                                        │
│ ┌── gold hairline ring (1px #E8B341) ─┐│
│ │        اضغط هنا للطلب                 ││  ← ringed CTA
│ └──────────────────────────────────────┘│
└────────────────────────────────────────┘
```

## Signature element
The gold star row ↔ gold CTA ring echo. One memorable device, everything else quiet. Animation:
one orchestrated hover moment on the CTA (ring 1px → 1.5px + price nudges up 2px), 180ms ease-out.
Reduced motion: ring stays static, price does not move.

## Decisions
- **Star row always renders 5 stars**, even when the product has no reviews: empty stars use
  `--pc-star-empty` so the meter still reads as 5 slots. Rating count next to the stars.
- **CTA replaces the existing add-to-cart `<form>`** in `product-card.blade.php`. The form already
  posts to `route('cart.add')`; the new `<a>` (or `<button>` inside a form) preserves that POST +
  CSRF. **Recommended:** keep it as a `<button type="submit">` inside the existing `<form>` so the
  cart-add flow is unchanged — the ring is purely visual.
- **Out-of-stock state:** same gold-ringed button, label switches to `product.out_of_stock_label`,
  opacity 0.6, `disabled` attribute retained, no hover nudge. Visual language stays consistent.
- **No backend changes** beyond eager-loading the rating average on the three queries that render
  product cards (see Task 6).

## Task list (ordered)

1. **`resources/views/frontend/partials/product-card.blade.php`** — surgical edit inside the
   existing `<div class="p-3 sm:p-4">` body (the price + CTA region):
   a. **Insert a rating row** directly after the product name `<h3>` and before the price block:
      render 5 stars (filled with `--pc-star` for `floor($rating)`, half-star optional via a
      `:style="width: (fraction*100)+'%'"` overlay for half ratings; empty stars use
      `--pc-star-empty`), then the review count in parentheses. Use Material Symbols `star` /
      `star_half` / `star_outline` (already loaded) or inline SVG — keep it lightweight.
   b. **Wrap the price block** (`<p class="...font-extrabold text-primary">` + the struck-through
      compare price) in a `<div class="pc-price">`. Change the price `<p>` to use
      `font-instrument-serif italic` (the repo already loads Instrument Serif; otherwise add the
      utility). Remove `font-extrabold text-primary` (the blue) — color is `--pc-ink` now.
   c. **Restyle the CTA** `<button>` inside the existing `<form action="{{ route('cart.add') }}">`
      with classes `pc-cta`. Remove the existing `bg-brand-50 text-brand-600 hover:bg-brand-500
      hover:text-white` colors — replaced by `--pc-cta` fill + `--pc-cta-text` + `--pc-ring` border.
      Replace the icon `add_shopping_cart` with `shopping_bag` and the label `product.add_to_cart`
      with `product.press_to_order`. Preserve the disabled state for out-of-stock.
   d. **Out-of-stock branch** inside the button: same ringed style, `opacity-60 cursor-not-allowed`,
      label `product.out_of_stock_label`.

2. **`resources/css/app.css`** — append a single `.product-card` block containing the 6 tokens
   (scoped to `.product-card { --pc-* }`), then:
   - `.pc-price` — flex row, baseline alignment, `gap: 0.5rem`.
   - `.pc-price .pc-price__current` — Instrument Serif italic, `--pc-ink`, `font-size: 1.375rem`.
   - `.pc-price .pc-price__compare` — Inter 500 0.8125rem `--pc-ink-soft`, line-through.
   - `.pc-stars` — flex, `gap: 2px`, `color: var(--pc-star)`. `.pc-stars__count` —
     Inter 500 0.75rem `--pc-ink-soft`, `margin-inline-start: 6px`.
   - `.pc-cta` — `display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
     width: 100%; height: 2.75rem; border-radius: var(--pc-radius); background: var(--pc-cta);
     color: var(--pc-cta-text); border: 1px solid var(--pc-ring); transition: border-width 180ms
     ease-out, transform 180ms ease-out; font-weight: 600; letter-spacing: 0.02em;`.
   - `.pc-cta:hover:not(:disabled)` — `border-width: 1.5px;` and trigger `.pc-price__current
     { transform: translateY(-2px); }` (same 180ms ease-out).
   - `@media (prefers-reduced-motion: reduce)` — `.pc-cta, .pc-price__current { transition: none;
     } .pc-cta:hover:not(:disabled) .pc-price__current { transform: none; }`.
   - No `:focus-visible` change beyond the browser default; the gold ring provides the focus cue.

3. **`lang/ar.json`** — add (under the existing `product.*` block):
   `"product.press_to_order": "اضغط هنا للطلب"`.
   Mirror in **`lang/en.json`**: `"product.press_to_order": "Press here to order"`.
   Mirror in **`lang/fr.json`**: `"product.press_to_order": "Cliquez ici pour commander"`.

4. **Rating data on the model** — `App\Models\Catalog\Product` already has a `reviews()` relation
   (used elsewhere). Use `$product->reviews_avg_rating ?? 0` if `withAvg('reviews','rating')` is
   loaded; otherwise call it as `$product->reviews()->avg('rating')`. **Do not** query per card
   render — eager-load.

5. **Eager-load the rating on every query that renders product cards** (three call sites):
   - `app/Http/Controllers/ShopController.php` — both `index()` (search) and `show()` (related
     products) — add `->withAvg('reviews', 'rating')->withCount('reviews')` to the relevant
     `Product::query()` chains. The existing `ProductService::searchProducts()` should also
     accept/pass these.
   - `app/Http/Controllers/HomeController.php` — `featured` and `latest` product queries — add
     `->withAvg('reviews','rating')->withCount('reviews')`.
   - Anywhere else `product-card.blade.php` is included (wishlist, related-products list, cart
     suggestions) — add the same eager loads to those controllers.

6. **Out-of-stock icon swap** — change the icon inside the disabled branch from `block` to
   `do_not_disturb_on` so the icon itself signals "unavailable," not "error."

## Risks / checks
- **Rating accuracy.** `withAvg('reviews','rating')` returns a string-cast float; coerce with
  `(float) $product->reviews_avg_rating`. If the product has zero reviews, the value is `null` —
  fall back to `0` and render all 5 empty stars.
- **RTL.** Stars row, price, and CTA must respect `dir="rtl"`. The price flex row reverses
  naturally; the CTA icon + label flex is direction-aware by default. Test with `?dir=rtl`.
- **Touch targets.** CTA height stays at 2.75rem (44px+) — accessible tap target preserved.
- **No new global tokens** beyond the scoped `.product-card { --pc-* }` block, so this does not
  leak elsewhere.

## Validation
1. `./vendor/bin/pint` clean on changed PHP.
2. `npm run build` succeeds and the built CSS contains `.pc-cta`, `.pc-stars`, `.pc-price`, and the
   `--pc-star`/`--pc-ring` tokens.
3. Manual on `/ar/shop` and `/en/shop` (RTL + LTR):
   - Each card shows a 5-star row (filled per rating, empty remainder), plus the review count.
   - Price is italic serif, struck-through compare price to the right (LTR) / left (RTL).
   - CTA reads "اضغط هنا للطلب" (Arabic) / "Press here to order" (English) with a visible gold
     hairline ring; hover thickens the ring and lifts the price 2px.
   - Out-of-stock card: same ring, opacity 0.6, disabled, label "نفذ المخزون".
   - Mobile (≤640px): card scrolls cleanly, stars and CTA fit without overflow.
   - `prefers-reduced-motion: reduce` (Chrome DevTools rendering tab): no hover nudge.
4. `./vendor/bin/phpunit` — all 60 tests still green; add one targeted feature test:
   `tests/Feature/ProductCardTest.php` that visits `/ar/shop`, seeds one product with reviews and
   one without, asserts:
   - `data-rating="4.5000"` (or similar) appears on the with-reviews card,
   - all 5 star elements render on both cards,
   - the CTA `form action="/ar/cart"` and label "اضغط هنا للطلب" are present.
5. Lighthouse / DevTools spot-check: no CLS introduced by the new rating row; price shift is
   GPU-composited (transform-only).
