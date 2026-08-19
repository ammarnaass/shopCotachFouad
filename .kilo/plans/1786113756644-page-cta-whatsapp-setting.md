# Fix: Pull WhatsApp link in page.blade.php CTA from settings

## Context
The footer already correctly pulls all contact info from admin settings via `site()` helper:
- `site('contact_phone', ...)` — line 185
- `site('contact_email', ...)` — line 192
- `site('contact_whatsapp')` — line 205
- `site('contact_address', ...)` — line 213
- `site('contact_working_hours', ...)` — line 221

However, `resources/views/frontend/page.blade.php` line 107 has a **hardcoded** WhatsApp link:
```html
<a href="https://wa.me/2490674784859" target="_blank" class="btn btn-lg bg-green-500 hover:bg-green-600 text-white shadow-lg">
```

This appears on every static page CTA (return policy, shipping, FAQ, privacy, terms, contact) and should use the WhatsApp number from admin settings instead.

## Change

**File:** `resources/views/frontend/page.blade.php` (line 107)

Replace hardcoded WhatsApp URL with dynamic `site()` call:

```php
@php
    $ctaWa = preg_replace('/[^0-9]/', '', site('contact_whatsapp', site('social_whatsapp', '')));
    $ctaWa = ltrim($ctaWa, '0');
    if (strlen($ctaWa) < 12) $ctaWa = '213' . $ctaWa;
@endphp
<a href="https://wa.me/{{ $ctaWa }}" target="_blank" class="btn btn-lg bg-green-500 hover:bg-green-600 text-white shadow-lg">
```

## Validation
- Open any static page (e.g. `/page/faq`, `/page/privacy`)
- Verify the WhatsApp button links to the number from admin settings (not the hardcoded `2490674784859`)
- Run `php artisan view:clear` to clear compiled views
