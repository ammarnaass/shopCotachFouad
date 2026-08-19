# Fix: Slider images not showing on the frontend hero

## Context
The animated hero slider was added (per-slide `animation_effect` / `entrance_effect`, global timing,
seeder, translations, tests). After rollout the user reports: **"الصور لا تظهر في شرائج" — images don't
appear in the slides** (frontend home hero, Arabic locale).

## Investigation findings (verified)
- Image upload works: files exist at `storage/app/public/slides/desktop/*.png` and `.../mobile/*.png`
  (valid ~1.8 MB PNGs). `public/storage` → `storage/app/public` symlink is present and correct.
- Stored column value is a relative path, e.g. `slides/desktop/gVk7WI4Z4obtjumCl4tH.png`.
- Blade builds `asset('storage/' . $slide['image'])` → valid, reachable URL (confirmed via tinker).
- `HomeController` passes real slides; `SliderController` stores/serves images correctly.
- **Root cause is layout, not data:** slides are `absolute inset-0` and their parent
  `.hero-slider-track` has no height, so it collapses to 0px and the absolutely-positioned slides
  render at 0px height → images invisible. This is pre-existing (slide structure predates the
  animation work); animations are unrelated.

## Fix (CSS-only, minimal, safe)
File: `resources/css/app.css`

Make the track fill the already-sized `.hero-slider` so the absolutely-positioned slides get real height:

```css
.hero-slider-track {
    position: absolute;
    inset: 0;
}
```

Notes:
- `.hero-slider` is `position: relative` with `min-height: 400/480/560px`, so an `absolute; inset: 0`
  track fills the used height. (Do NOT use `height: 100%` — `min-height` on the parent does not give a
  definite height for percentage resolution in all browsers; `inset: 0` is robust.)
- Keep the existing Tailwind `overflow-hidden relative` on the element; CSS `position: absolute` from
  the rule above overrides Tailwind's `relative` (intended).
- The `<nav class="hero-nav ...">` dots/arrows are siblings of the track inside `.hero-slider`, so they
  stay positioned relative to `.hero-slider` and are unaffected.
- `hero-slider.js` only attaches touch/swipe listeners to `.hero-slider-track`; positioning change has
  no effect on that logic.

## Why this is the right minimal change
- No markup change, no JS change, no migration.
- Fixes both the image and the gradient fallback (both live inside the 0px slide).
- Preserves existing responsive heights (mobile 400px → desktop 560px) since the track follows the
  hero's `min-height`.

## Validation
1. `npm run build` succeeds and the built CSS contains `.hero-slider-track{position:absolute;inset:0}`
   (or equivalent minified form).
2. Manually load the homepage (`/ar` and `/en`): each slide's image is visible and fills the slide
   (`object-cover`); text overlay still centered; prev/next + dots still positioned correctly.
3. Animation behavior intact: switching slides still plays the configured `data-effect` transition
   and staggered `data-entrance` content reveal.
4. Regression: `vendor/bin/phpunit` still green (existing 60 tests).

## Open question (non-blocking)
If the user also means the **admin slider list/index** thumbnails, those use `$slide->image_url`
directly and are already fine; confirm if any admin preview needs attention. Frontend hero is the
reported issue and the scope of this fix.
