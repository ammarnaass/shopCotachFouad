<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstantBuyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Api\ShippingApiController;
use Illuminate\Support\Facades\Route;

// Language switch (no prefix, no middleware)
Route::get('/lang/{locale}', function (string $locale) {
    $locale = in_array($locale, ['ar', 'en', 'fr']) ? $locale : config('ecommerce.languages.default', 'ar');
    app(\App\Services\TranslationService::class)->setLocale($locale);
    return back();
})->name('lang.switch')->whereIn('locale', ['ar', 'en', 'fr']);

// Redirect bare paths (no locale prefix) to locale-prefixed versions
$redirectPaths = ['/admin', '/login', '/register', '/cart', '/orders', '/track', '/shop', '/wishlist'];
foreach ($redirectPaths as $path) {
    Route::get($path . '/{any?}', function () use ($path) {
        $locale = session('locale', app()->getLocale()) ?: config('ecommerce.languages.default', 'ar');
        $suffix = request()->path() !== ltrim($path, '/') ? '/' . substr(request()->path(), strlen(ltrim($path, '/')) + 1) : '';
        return redirect($locale . $path . $suffix, 301);
    })->where('any', '.*');
}

// SEO routes (no locale prefix)
Route::get('/sitemap.xml', function () {
    $products = \App\Models\Catalog\Product::active()
        ->with('category:id,slug')
        ->select('id', 'slug', 'name', 'created_at', 'updated_at')
        ->get();
    $categories = \App\Models\Catalog\Category::where('status', 'active')
        ->select('id', 'slug', 'name', 'updated_at')
        ->get();
    $pages = \App\Models\Content\Page::where('is_active', true)
        ->select('id', 'slug', 'name', 'updated_at')
        ->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($products as $product) {
        $url = route('shop.show', ['slug' => $product->slug], false);
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . e($url) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $product->updated_at->toDateString() . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.7</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    foreach ($categories as $category) {
        $url = route('shop.category', ['slug' => $category->slug], false);
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . e($url) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $category->updated_at->toDateString() . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.6</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    foreach ($pages as $page) {
        $url = route('page.show', ['slug' => $page->slug], false);
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . e($url) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $page->updated_at->toDateString() . '</lastmod>' . "\n";
        $xml .= '    <changefreq>monthly</changefreq>' . "\n";
        $xml .= '    <priority>0.5</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $txt = "User-agent: *\n";
    $txt .= "Allow: /\n";
    $txt .= "Disallow: /admin/\n";
    $txt .= "Disallow: /api/\n";
    $txt .= "\n";
    $txt .= "Sitemap: " . url('/sitemap.xml') . "\n";

    return response($txt, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// Locale-prefixed routes
Route::prefix('{locale?}')->whereIn('locale', ['ar', 'en', 'fr'])->middleware('locale')->group(function () {

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Static pages
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/about', fn() => redirect()->route('page.show', 'about'));
Route::get('/contact', fn() => redirect()->route('page.show', 'contact'));
Route::get('/faq', fn() => redirect()->route('page.show', 'faq'));
Route::get('/return', fn() => redirect()->route('page.show', 'return-policy'));
Route::get('/track', [PageController::class, 'track'])->name('track');
Route::post('/track', [PageController::class, 'track'])->name('track.submit');
Route::get('/api/countries/{code}/states', [PageController::class, 'states'])->name('api.countries.states');
Route::get('/currency/{code}', function (string $code) {
    $code = strtoupper($code);
    $countries = config('ecommerce.countries', []);
    if (!array_key_exists($code, $countries)) {
        $code = config('ecommerce.default_country', 'DZ');
    }
    session(['selected_country' => $code]);
    return back();
})->name('currency.switch');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/categories/{slug}', fn($slug) => redirect()->route('shop.category', $slug));

// Cart (guest + auth)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Newsletter
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::middleware('auth')->group(function () {
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Account
    Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account.index');
    Route::put('/account', [App\Http\Controllers\AccountController::class, 'updateProfile'])->name('account.update');
    Route::put('/account/password', [App\Http\Controllers\AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/account/address', [App\Http\Controllers\AccountController::class, 'storeAddress'])->name('account.address.store');
    Route::post('/account/address/{address}/default', [App\Http\Controllers\AccountController::class, 'setDefaultAddress'])->name('account.address.default');
    Route::delete('/account/address/{address}', [App\Http\Controllers\AccountController::class, 'destroyAddress'])->name('account.address.destroy');
});

// Instant Buy (works for guests and authenticated users)
Route::get('/instant', [InstantBuyController::class, 'create'])->name('instant.create');
Route::get('/instant/{slug}', [InstantBuyController::class, 'create'])->name('instant.buy');
Route::post('/instant/calculate', [InstantBuyController::class, 'calculate'])->name('instant.calculate');
Route::post('/instant/shipping-options', [InstantBuyController::class, 'shippingOptions'])->name('instant.shipping-options');
Route::post('/instant/coupon', [InstantBuyController::class, 'validateCoupon'])->name('instant.coupon');
Route::post('/instant/submit', [InstantBuyController::class, 'submit'])->name('instant.submit');
Route::get('/order/{orderNumber}/thanks', [InstantBuyController::class, 'thankyou'])->name('instant.thankyou');

// Embedded Instant Buy (on product page)
Route::post('/instant-buy/calculate', [App\Http\Controllers\InstantBuyOrderController::class, 'calculate'])->name('instant-buy.calculate');
Route::post('/instant-buy/shipping-options', [App\Http\Controllers\InstantBuyOrderController::class, 'shippingOptions'])->name('instant-buy.shipping-options');
Route::post('/instant-buy/coupon', [App\Http\Controllers\InstantBuyOrderController::class, 'validateCoupon'])->name('instant-buy.coupon');
Route::post('/instant-buy/submit', [App\Http\Controllers\InstantBuyOrderController::class, 'submit'])->name('instant-buy.submit');

// Admin
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/quick-setting', [App\Http\Controllers\Admin\DashboardController::class, 'quickSetting'])->name('quickSetting');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::post('/products/bulk-action', [App\Http\Controllers\Admin\ProductController::class, 'bulkAction'])->name('products.bulkAction');
    Route::get('/products/export/csv', [App\Http\Controllers\Admin\ProductController::class, 'export'])->name('products.export');
    Route::get('/products/{product}/gallery', [App\Http\Controllers\Admin\ProductController::class, 'gallery'])->name('products.gallery');
    Route::post('/products/{product}/images', [App\Http\Controllers\Admin\ProductController::class, 'uploadImages'])->name('products.images.upload');
    Route::patch('/products/{product}/images/{image}', [App\Http\Controllers\Admin\ProductController::class, 'updateImage'])->name('products.images.update');
    Route::delete('/products/{product}/images/{image}', [App\Http\Controllers\Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
    Route::post('/products/{product}/images/{image}/primary', [App\Http\Controllers\Admin\ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->except(['create', 'store', 'edit']);
    Route::post('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/notes', [App\Http\Controllers\Admin\OrderController::class, 'addNote'])->name('orders.notes.store');
    Route::delete('/orders/notes/{note}', [App\Http\Controllers\Admin\OrderController::class, 'deleteNote'])->name('orders.notes.delete');
    Route::post('/orders/bulk-action', [App\Http\Controllers\Admin\OrderController::class, 'bulkAction'])->name('orders.bulkAction');
    Route::get('/orders/{order}/invoice', [App\Http\Controllers\Admin\OrderPrintController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/label', [App\Http\Controllers\Admin\OrderPrintController::class, 'customerLabel'])->name('orders.label');
    Route::post('/orders/bulk-invoice', [App\Http\Controllers\Admin\OrderPrintController::class, 'bulkInvoice'])->name('orders.bulkInvoice');
    Route::post('/orders/bulk-label', [App\Http\Controllers\Admin\OrderPrintController::class, 'bulkLabel'])->name('orders.bulkLabel');
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);

    // Newsletter
    Route::get('/newsletter', [App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::delete('/newsletter/{subscriber}', [App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
    Route::delete('/newsletter/selected/destroy', [App\Http\Controllers\Admin\NewsletterController::class, 'destroySelected'])->name('newsletter.destroySelected');
    Route::get('/newsletter/export', [App\Http\Controllers\Admin\NewsletterController::class, 'export'])->name('newsletter.export');

    // Reviews
    Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/status', [App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
    Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Tags
    Route::get('/tags', [App\Http\Controllers\Admin\TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [App\Http\Controllers\Admin\TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [App\Http\Controllers\Admin\TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [App\Http\Controllers\Admin\TagController::class, 'destroy'])->name('tags.destroy');

    // Pages
    Route::resource('pages', App\Http\Controllers\Admin\PageController::class)->except(['show']);

    // Shipping (zones + methods + companies + labels + tracking)
    Route::get('/shipping', [App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('shipping.index');
    // Companies
    Route::get('/shipping/companies/create', [App\Http\Controllers\Admin\ShippingController::class, 'createCompany'])->name('shipping.company.create');
    Route::post('/shipping/companies', [App\Http\Controllers\Admin\ShippingController::class, 'storeCompany'])->name('shipping.company.store');
    Route::get('/shipping/companies/{company}/edit', [App\Http\Controllers\Admin\ShippingController::class, 'editCompany'])->name('shipping.company.edit');
    Route::put('/shipping/companies/{company}', [App\Http\Controllers\Admin\ShippingController::class, 'updateCompany'])->name('shipping.company.update');
    Route::delete('/shipping/companies/{company}', [App\Http\Controllers\Admin\ShippingController::class, 'destroyCompany'])->name('shipping.company.destroy');
    // Zones
    Route::get('/shipping/zones/create', [App\Http\Controllers\Admin\ShippingController::class, 'createZone'])->name('shipping.zone.create');
    Route::post('/shipping/zones', [App\Http\Controllers\Admin\ShippingController::class, 'storeZone'])->name('shipping.zone.store');
    Route::get('/shipping/zones/{zone}/edit', [App\Http\Controllers\Admin\ShippingController::class, 'editZone'])->name('shipping.zone.edit');
    Route::put('/shipping/zones/{zone}', [App\Http\Controllers\Admin\ShippingController::class, 'updateZone'])->name('shipping.zone.update');
    Route::delete('/shipping/zones/{zone}', [App\Http\Controllers\Admin\ShippingController::class, 'destroyZone'])->name('shipping.zone.destroy');
    // Methods
    Route::get('/shipping/methods/create', [App\Http\Controllers\Admin\ShippingController::class, 'createMethod'])->name('shipping.method.create');
    Route::post('/shipping/methods', [App\Http\Controllers\Admin\ShippingController::class, 'storeMethod'])->name('shipping.method.store');
    Route::get('/shipping/methods/{method}/edit', [App\Http\Controllers\Admin\ShippingController::class, 'editMethod'])->name('shipping.method.edit');
    Route::put('/shipping/methods/{method}', [App\Http\Controllers\Admin\ShippingController::class, 'updateMethod'])->name('shipping.method.update');
    Route::delete('/shipping/methods/{method}', [App\Http\Controllers\Admin\ShippingController::class, 'destroyMethod'])->name('shipping.method.destroy');
    Route::post('/shipping/zones/{zone}/methods', [App\Http\Controllers\Admin\ShippingController::class, 'storeMethodForZone'])->name('shipping.zone.method.store');
    // Labels (Waybills)
    Route::get('/shipping/labels/create', [App\Http\Controllers\Admin\ShippingController::class, 'createLabel'])->name('shipping.label.create');
    Route::post('/shipping/labels', [App\Http\Controllers\Admin\ShippingController::class, 'storeLabel'])->name('shipping.label.store');
    Route::get('/shipping/labels/{label}', [App\Http\Controllers\Admin\ShippingController::class, 'showLabel'])->name('shipping.label.show');
    Route::post('/shipping/labels/{label}/status', [App\Http\Controllers\Admin\ShippingController::class, 'updateLabelStatus'])->name('shipping.label.updateStatus');
    Route::post('/shipping/labels/{label}/tracking', [App\Http\Controllers\Admin\ShippingController::class, 'addTrackingUpdate'])->name('shipping.label.tracking');
    Route::get('/shipping/labels/{label}/pdf', [App\Http\Controllers\Admin\ShippingController::class, 'printLabel'])->name('shipping.label.pdf');
    Route::post('/shipping/bulk-ship', [App\Http\Controllers\Admin\ShippingController::class, 'bulkShip'])->name('shipping.bulkShip');
    // Pickup Offices
    Route::get('/shipping/pickups/create', [App\Http\Controllers\Admin\ShippingController::class, 'createPickup'])->name('shipping.pickup.create');
    Route::post('/shipping/pickups', [App\Http\Controllers\Admin\ShippingController::class, 'storePickup'])->name('shipping.pickup.store');
    Route::get('/shipping/pickups/{pickup}/edit', [App\Http\Controllers\Admin\ShippingController::class, 'editPickup'])->name('shipping.pickup.edit');
    Route::put('/shipping/pickups/{pickup}', [App\Http\Controllers\Admin\ShippingController::class, 'updatePickup'])->name('shipping.pickup.update');
    Route::delete('/shipping/pickups/{pickup}', [App\Http\Controllers\Admin\ShippingController::class, 'destroyPickup'])->name('shipping.pickup.destroy');

    // Payments
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');

    // Payment Methods
    Route::resource('payment-methods', App\Http\Controllers\Admin\PaymentMethodController::class)->except(['show']);
    Route::post('/payment-methods/{paymentMethod}/toggle', [App\Http\Controllers\Admin\PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');

    // Currencies
    Route::get('/currencies', [App\Http\Controllers\Admin\CurrencyController::class, 'index'])->name('currencies.index');
    Route::post('/currencies', [App\Http\Controllers\Admin\CurrencyController::class, 'update'])->name('currencies.update');
    Route::post('/currencies/rates', [App\Http\Controllers\Admin\CurrencyController::class, 'updateRates'])->name('currencies.rates.update');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/remove-image', [App\Http\Controllers\Admin\SettingsController::class, 'removeImage'])->name('settings.removeImage');

    // Languages
    Route::prefix('languages')->name('languages.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LanguageController::class, 'index'])->name('index');
        Route::get('/{language}/edit', [App\Http\Controllers\Admin\LanguageController::class, 'edit'])->name('edit');
        Route::post('/{language}', [App\Http\Controllers\Admin\LanguageController::class, 'update'])->name('update');
        Route::post('/{language}/toggle-active', [App\Http\Controllers\Admin\LanguageController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{language}/set-default', [App\Http\Controllers\Admin\LanguageController::class, 'setDefault'])->name('set-default');
        Route::get('/translations', [App\Http\Controllers\Admin\LanguageController::class, 'translations'])->name('translations');
        Route::post('/translations/bulk-update', [App\Http\Controllers\Admin\LanguageController::class, 'bulkUpdateTranslations'])->name('translations.bulk-update');
        Route::post('/translations/create', [App\Http\Controllers\Admin\LanguageController::class, 'createTranslation'])->name('translations.create');
        Route::post('/translations/{translation}', [App\Http\Controllers\Admin\LanguageController::class, 'updateTranslation'])->name('translations.update');
        Route::delete('/translations/{translation}', [App\Http\Controllers\Admin\LanguageController::class, 'deleteTranslation'])->name('translations.delete');
        Route::get('/settings', [App\Http\Controllers\Admin\LanguageController::class, 'settings'])->name('settings');
        Route::post('/{language}/settings', [App\Http\Controllers\Admin\LanguageController::class, 'updateSettings'])->name('update-settings');
    });

    // Slider
    Route::resource('slider', App\Http\Controllers\Admin\SliderController::class)->except(['show']);
    Route::post('/slider/{slider}/toggle', [App\Http\Controllers\Admin\SliderController::class, 'toggleActive'])->name('slider.toggle');

    // Customize
    Route::get('/customize', [App\Http\Controllers\Admin\CustomizeController::class, 'index'])->name('customize.index');
    Route::post('/customize', [App\Http\Controllers\Admin\CustomizeController::class, 'update'])->name('customize.update');
    Route::post('/customize/reset', [App\Http\Controllers\Admin\CustomizeController::class, 'reset'])->name('customize.reset');
    Route::post('/customize/remove-image', [App\Http\Controllers\Admin\CustomizeController::class, 'removeImage'])->name('customize.removeImage');

    // Instant Buy Settings
    Route::prefix('instant-buy')->name('instant-buy.')->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'index'])->name('settings');
        Route::post('/settings/general', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateGeneral'])->name('settings.general');
        Route::post('/settings/colors', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateColors'])->name('settings.colors');
        Route::post('/settings/fields', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateFields'])->name('settings.fields');
        Route::post('/settings/buttons', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateButtons'])->name('settings.buttons');
        Route::post('/settings/success', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateSuccess'])->name('settings.success');
        Route::post('/settings/reset', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'resetToDefaults'])->name('settings.reset');
        Route::get('/orders', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'orders'])->name('orders');
        Route::post('/orders/{order}/status', [App\Http\Controllers\Admin\InstantBuySettingsController::class, 'updateOrderStatus'])->name('orders.update-status');
    });
}); // end admin group
}); // end locale prefix group
