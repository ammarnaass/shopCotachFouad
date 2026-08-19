# Hero Slider Not Visible — Root Cause & Fix

## Root Cause

The hero slider section is **invisible (0px height)** because:

1. The `<section class="hero-slider relative">` has no explicit height CSS
2. All child `.hero-slide` elements use `absolute inset-0` positioning
3. Absolutely-positioned children don't contribute to parent height
4. The `.hero-slider` CSS class is **never defined** — not in `app.css`, not via Tailwind utilities

**Result:** The entire hero section renders as a 0px-tall invisible strip on the homepage.

## Secondary Issue: Missing CSS for `.hero-slider`

No CSS exists for `.hero-slider` anywhere in the codebase. The class is referenced in:
- `hero-slider.blade.php:19` — `<section class="hero-slider relative" ...>`
- `hero-slider.js:206` — `document.querySelectorAll('.hero-slider')`
- `app.js:21` — `import './components/hero-slider.js'`

But the CSS stylesheet (`resources/css/app.css`) has **zero** rules for `.hero-slider`.

## Fix

Add CSS rules for `.hero-slider` in `resources/css/app.css` to give the section an explicit responsive height.

### File: `resources/css/app.css`

Add after the `/* ---- Missing Utility Classes ---- */` section (around line 835):

```css
/* ---- Hero Slider ---- */
.hero-slider {
    position: relative;
    width: 100%;
    min-height: 400px;
    overflow: hidden;
    background-color: #e1e2ed;
}

@media (min-width: 640px) {
    .hero-slider {
        min-height: 480px;
    }
}

@media (min-width: 1024px) {
    .hero-slider {
        min-height: 560px;
    }
}
```

### Why these values:
- **400px** mobile — ensures the slider is visible and tall enough for content
- **480px** sm (640px+) — slightly taller on tablets
- **560px** lg (1024px+) — desktop height for a hero banner
- **`background-color: #e1e2ed`** — fallback gray while the image loads (matches the project's `surface-container-highest` color)

## Validation Steps

1. Run `npm run build` to rebuild assets
2. Visit the homepage — the hero slider should now appear with a visible height
3. The slide image ("495 الف") should display with proper aspect ratio
4. On mobile, the mobile image should load (via `<source>` media query)

## No Other Code Changes Needed

The rest of the stack is correct:
- Controller (`HomeController.php:39-44`) fetches 1 visible slide ✓
- Redis cache stores the slide data ✓
- `home.blade.php:18` passes `$slides` to the partial ✓
- `hero-slider.blade.php:18` renders when `$slides` is not empty ✓
- `hero-slider.js` initializes the slider component ✓
- Slide images exist on disk ✓
