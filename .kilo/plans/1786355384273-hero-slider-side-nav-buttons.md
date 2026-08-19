# Hero Slider — Side Navigation Buttons

## Goal
Move the hero slider's **prev/next** toggle buttons out of the bottom-centered nav cluster and place them **flush against the left and right edges of the slide, vertically centered**. Keep the **dots** as a separate bottom-center indicator. JS bindings and behavior stay unchanged — this is a layout-only change.

## Current state
`resources/views/frontend/partials/hero-slider.blade.php` lines 95–116 render a single `.hero-nav` cluster: `[prev] [dots] [next]`, positioned `absolute bottom-6 left-1/2 -translate-x-1/2`.

`resources/js/components/hero-slider.js` queries `.hero-prev`, `.hero-next`, `.hero-dot` by class — these selectors **must not change**. `disableControls()` (lines 46–50) hides `.hero-prev`, `.hero-next`, `.hero-dots` when `slides.length <= 1` — keep these class names intact.

## Decision (confirmed with user)
- prev/next → left/right edges, vertically centered.
- dots → stay bottom-center, on their own.

## Files to edit
Only **one** file: `resources/views/frontend/partials/hero-slider.blade.php`.

No JS, CSS, or translation changes needed. Reuse existing translation keys `home.prev_slide` / `home.next_slide`. RTL is already handled in JS (`isRtl`); the chevron icons already point the correct logical way (`chevron_left`/`chevron_right`) — do **not** swap them per direction; leave the icon mapping as-is so JS RTL logic stays consistent.

## Concrete changes in the Blade partial

### 1. Split the nav into two separate clusters

Replace the existing `@if(count($slides) > 1) … @endif` block (lines 95–117) with **two** nav elements:

**A. Side control — prev (left edge)**
```
<nav class="hero-nav-side absolute top-1/2 -translate-y-1/2 z-20 left-3 sm:left-4"
     aria-label="{{ __t('home.slider_navigation') }}">
    <button type="button"
            class="hero-prev inline-flex items-center justify-center w-11 h-11 rounded-full bg-black/30 backdrop-blur-md text-white hover:bg-black/50 hover:scale-110 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black/50"
            aria-label="{{ __t('home.prev_slide') }}">
        <span class="material-symbols-outlined text-xl">chevron_left</span>
    </button>
</nav>
```

**B. Side control — next (right edge)**
```
<nav class="hero-nav-side absolute top-1/2 -translate-y-1/2 z-20 right-3 sm:right-4"
     aria-label="{{ __t('home.slider_navigation') }}">
    <button type="button"
            class="hero-next inline-flex items-center justify-center w-11 h-11 rounded-full bg-black/30 backdrop-blur-md text-white hover:bg-black/50 hover:scale-110 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black/50"
            aria-label="{{ __t('home.next_slide') }}">
        <span class="material-symbols-outlined text-xl">chevron_right</span>
    </button>
</nav>
```

**C. Dots cluster — bottom center (unchanged role, isolated)**
```
<div class="hero-dots absolute bottom-6 left-1/2 -translate-x-1/2 inline-flex gap-1.5 z-20"
     role="tablist" aria-label="{{ __t('home.slide_selector') }}">
    @foreach($slides as $index => $slide)
        <button type="button"
                role="tab"
                aria-selected="{{ $index === 0 }}"
                aria-label="{{ __t('home.go_to_slide', ['num' => $index + 1]) }}"
                class="hero-dot w-2.5 h-2.5 rounded-full transition-all {{ $index === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/75' }}"
                data-index="{{ $index }}">
        </button>
    @endforeach
</div>
```

### 2. Preserve the `<=1 slide` guard
Keep the whole block wrapped in `@if(count($slides) > 1) … @endif` so the nav doesn't render for single-slide sliders. JS `disableControls()` still hides stray buttons defensively — harmless.

## Design decisions & rationale

- **Vertical center** via `top-1/2 -translate-y-1/2` — single source of truth, no calc, no CSS edit.
- **Edge offset** `left-3 sm:left-4` / `right-3 sm:right-4` — tighter on mobile (less overlap with slide content), comfortable on desktop. No fixed px that could fight container padding.
- **Background `bg-black/30`** (was `bg-white/20`) — against varied hero imagery, a darker translucent pill reads more reliably than a light one; `backdrop-blur-md` keeps it feeling like glass, not a hard chip.
- **Size `w-11 h-11` + `text-xl`** (was `w-10 h-10` + `text-lg`) — side arrows deserve a slightly larger hit target than the bottom cluster did, since they are now the sole prev/next affordance.
- **`hover:scale-110`** addition — the bottom version had no feedback beyond color; isolated side buttons benefit from a small transform to confirm clickability.
- **`focus-visible:ring-*`** instead of `focus:ring-*` — keeps keyboard ring only on keyboard nav, not on mouse click, matching modern a11y practice (was `focus:ring-2 focus:ring-white`).
- **`z-20`** preserved so buttons sit above the gradient overlay (`z-10` content) and dots.
- **Dots container** is now a bare `<div>` (not wrapped in `<nav>`), keeping its `role="tablist"` — functionally identical to before, just not co-housed with the prev/next buttons.

## RTL note
The site is RTL (`document.dir === 'rtl'`, handled in JS line 22). The Blade uses literal `left-3`/`right-3` + `chevron_left`/`chevron_right`. Do **not** attempt to mirror these in Blade:
- Tailwind `left-*`/`right-*` are physical sides, not logical — the button sits on the physical left edge regardless of RTL. That is the **desired** behavior here (a button on each physical edge).
- JS already flips logical "prev"/"next" direction for RTL via `isRtl` (lines 80–86, 133–137). The physical placement of buttons is independent of that logic, so no conflict.
- If Tailwind logical equivalents are preferred later (`start-3`/`end-3`), that's a separate refactor and out of scope.

## Validation

1. **Build assets**: `npm run build` (Vite) — confirm no errors. If dev:`npm run dev` and visually check.
2. **Visual check** on desktop (≥sm): both arrows visible at vertical center of left/right edges; dots centered at bottom.
3. **Mobile check** (<sm): arrows still visible, not clipped, don't overlap dots. Dots not overlapping arrows.
4. **Keyboard**: Tab to prev/next — `focus-visible:ring` appears. Arrow Left/Right still changes slides (JS unchanged).
5. **RTL**: with `dir="rtl"`, arrows remain on physical left/right edges; Left arrow still means "previous" (JS handles the flip).
6. **Single slide**: nav block hidden entirely; no orphan arrows.
7. **Reduced motion**: `hover:scale-110` is a hover transition, not a motion preference — acceptable, but verify it doesn't jar. If it does, gate behind `motion-safe:hover:scale-110` (optional follow-up, not required).
8. **Hover pause**: autoplay still pauses on hover/focus (JS unchanged).

## Out of scope
- Changing JS logic, animation effects, autoplay timing.
- Touch/swipe behavior (already works).
- Adding new translation keys.
- Touching `resources/js/components/hero-slider.js` or any CSS file.
- Logical-property RTL refactor (`start-*`/`end-*`).

## Risk
Low. Pure presentational refactor of one Blade partial. Selectors the JS depends on (`.hero-prev`, `.hero-next`, `.hero-dot`, `.hero-dots`) are all preserved. No data flow or state changes.
