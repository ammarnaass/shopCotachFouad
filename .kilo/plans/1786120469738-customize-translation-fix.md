# Newsletter Section — Show Only on Homepage

## Problem
The newsletter section currently appears on **all frontend pages** (except login/register). The user wants it to appear **only on the homepage**.

## Current Code
`resources/views/frontend/partials/footer.blade.php` line 9:
```php
@if(site('show_newsletter', '1') === '1' && !$isAuthPage)
```

This hides it on auth pages but shows it everywhere else.

## Fix

### File: `resources/views/frontend/partials/footer.blade.php`
**Line 8-9:** Add a homepage check.

**Before:**
```php
@php $isAuthPage = request()->is('*login') || request()->is('*register') || request()->routeIs('login') || request()->routeIs('register'); @endphp
@if(site('show_newsletter', '1') === '1' && !$isAuthPage)
```

**After:**
```php
@php $isAuthPage = request()->is('*login') || request()->is('*register') || request()->routeIs('login') || request()->routeIs('register'); @endphp
@if(site('show_newsletter', '1') === '1' && !$isAuthPage && request()->routeIs('home'))
```

This adds `&& request()->routeIs('home')` so the newsletter only renders when the current route is `home`.

## Validation
- Visit `/ar` (homepage) → newsletter should show
- Visit `/ar/shop`, `/ar/shop/category`, `/ar/instant`, etc. → newsletter should NOT show
- Visit `/ar/login`, `/ar/register` → newsletter should NOT show (existing behavior preserved)
