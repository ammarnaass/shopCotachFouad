# Plan: Fix Mobile Responsiveness — Slider, Profile, Orders

## Problem Summary

Three pages have significant mobile responsiveness issues at 375px viewport:
1. **Product cards**: Wishlist button invisible on mobile (hover-only); add-to-cart and wishlist buttons 40×40px
2. **Hero slider**: Navigation arrows 40×40px; dots overlap content
3. **Account page**: Sidebar nav buttons ~40px; address action buttons ~12px (critically small)
4. **Orders page**: Empty state wastes space; timeline has no scroll affordance; breadcrumb overflows
5. **Banner headings**: `text-4xl` (36px) too large on mobile

## Implementation Status

| Task | Status |
|------|--------|
| Task 1: Product card fix | ✅ DONE |
| Task 2: Hero slider fix | ✅ DONE |
| Task 3: Account page fix | ❌ REMAINING |
| Task 4: Orders page fix | ❌ REMAINING |
| Task 5: Banner heading fix | ❌ REMAINING |

---

## Task 3: Fix Account Page Touch Targets

**File**: `resources/views/frontend/account/index.blade.php`

### 3a. Sidebar nav buttons
Lines 65, 69, 73, 77, 81: `py-2.5` → `py-3` (12px each side = ~48px total with text)

### 3b. Address action buttons (critically small)
Lines 188, 193: Bare `<button>` with only `text-xs` — ~12px touch target.

**Fix**: Add proper padding and make them look like buttons:
```
class="text-xs text-brand-600 hover:underline font-semibold px-3 py-2 rounded-lg hover:bg-brand-50 inline-block"
```
Same for delete button with `text-rose-600`.

---

## Task 4: Fix Orders Page Mobile Issues

### 4a. Empty state padding
**File**: `resources/views/frontend/orders/index.blade.php`, line 84
`p-12` → `p-6 sm:p-12` — reduce mobile padding

### 4b. Timeline scroll affordance
**File**: `resources/views/frontend/orders/show.blade.php`, line 66
Add a CSS gradient fade on the right edge to indicate more content:
```
class="flex items-center justify-between overflow-x-auto pb-2 relative"
```
Add a `::after` pseudo-element or a gradient overlay div on the right edge.

### 4c. Breadcrumb overflow
**File**: `resources/views/frontend/orders/show.blade.php`, line 21
Add `overflow-x-auto whitespace-nowrap` to prevent page-level horizontal scroll.

---

## Task 5: Fix Banner Heading Size on Mobile

**File**: `resources/views/frontend/home.blade.php`

Line 260: `text-4xl md:text-5xl` → `text-2xl sm:text-3xl md:text-5xl` — step down more gradually

---

## Files Modified

| File | Tasks | Status |
|------|-------|--------|
| `resources/views/frontend/partials/product-card.blade.php` | 1a, 1b, 1c | ✅ DONE |
| `resources/views/frontend/home.blade.php` | 2a, 2b, 2c | ✅ DONE |
| `resources/views/frontend/home.blade.php` | 5 | ❌ REMAINING |
| `resources/views/frontend/account/index.blade.php` | 3a, 3b | ❌ REMAINING |
| `resources/views/frontend/orders/index.blade.php` | 4a | ❌ REMAINING |
| `resources/views/frontend/orders/show.blade.php` | 4b, 4c | ❌ REMAINING |

## Validation

1. Product cards: Wishlist icon visible on mobile without hover; buttons are 44×44px ✅
2. Hero slider: Arrows are 44px; dots don't overlap CTA buttons; padding reduces on mobile ✅
3. Account page: Nav buttons are ~48px tall; address action buttons have proper padding
4. Orders page: Empty state has less padding on mobile; timeline shows scroll hint; breadcrumb doesn't cause page scroll
5. Banner heading wraps gracefully on 375px
6. Hard refresh (Ctrl+Shift+R) to bypass cache
