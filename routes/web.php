<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CustomizeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\InstantBuySettingsController;
use App\Http\Controllers\Admin\InvoiceTemplateController;
use App\Http\Controllers\Admin\LabelTemplateController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\OrderPrintController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PrintingSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstantBuyController;
use App\Http\Controllers\InstantBuyOrderController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Content\Page;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Route;

// Language switch (no prefix, no middleware)
Route::get('/lang/{locale}', function (string $locale) {
    $locale = in_array($locale, ['ar', 'en', 'fr']) ? $locale : config('ecommerce.languages.default', 'ar');
    app(TranslationService::class)->setLocale($locale);

    return back();
})->name('lang.switch')->whereIn('locale', ['ar', 'en', 'fr']);

// Redirect bare paths (no locale prefix) to locale-prefixed versions
$redirectPaths = ['/admin', '/login', '/register', '/cart', '/orders', '/track', '/shop', '/wishlist'];
foreach ($redirectPaths as $path) {
    Route::get($path.'/{any?}', function () use ($path) {
        $locale = session('locale', app()->getLocale()) ?: config('ecommerce.languages.default', 'ar');
        $suffix = request()->path() !== ltrim($path, '/') ? '/'.substr(request()->path(), strlen(ltrim($path, '/')) + 1) : '';

        return redirect($locale.$path.$suffix, 301);
    })->where('any', '.*');
}

// SEO routes (no locale prefix)
Route::get('/sitemap.xml', function () {
    $products = Product::active()
        ->with('category:id,slug')
        ->select('id', 'slug', 'name', 'created_at', 'updated_at')
        ->get();
    $categories = Category::where('status', 'active')
        ->select('id', 'slug', 'name', 'updated_at')
        ->get();
    $pages = Page::where('is_active', true)
        ->select('id', 'slug', 'name', 'updated_at')
        ->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

    foreach ($products as $product) {
        $url = route('shop.show', ['slug' => $product->slug], false);
        $xml .= '  <url>'."\n";
        $xml .= '    <loc>'.e($url).'</loc>'."\n";
        $xml .= '    <lastmod>'.$product->updated_at->toDateString().'</lastmod>'."\n";
        $xml .= '    <changefreq>weekly</changefreq>'."\n";
        $xml .= '    <priority>0.7</priority>'."\n";
        $xml .= '  </url>'."\n";
    }

    foreach ($categories as $category) {
        $url = route('shop.category', ['slug' => $category->slug], false);
        $xml .= '  <url>'."\n";
        $xml .= '    <loc>'.e($url).'</loc>'."\n";
        $xml .= '    <lastmod>'.$category->updated_at->toDateString().'</lastmod>'."\n";
        $xml .= '    <changefreq>weekly</changefreq>'."\n";
        $xml .= '    <priority>0.6</priority>'."\n";
        $xml .= '  </url>'."\n";
    }

    foreach ($pages as $page) {
        $url = route('page.show', ['slug' => $page->slug], false);
        $xml .= '  <url>'."\n";
        $xml .= '    <loc>'.e($url).'</loc>'."\n";
        $xml .= '    <lastmod>'.$page->updated_at->toDateString().'</lastmod>'."\n";
        $xml .= '    <changefreq>monthly</changefreq>'."\n";
        $xml .= '    <priority>0.5</priority>'."\n";
        $xml .= '  </url>'."\n";
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
    $txt .= 'Sitemap: '.url('/sitemap.xml')."\n";

    return response($txt, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// Locale-prefixed routes
Route::prefix('{locale?}')->whereIn('locale', ['ar', 'en', 'fr'])->middleware('locale')->group(function () {

    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Static pages
    Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
    Route::get('/about', fn () => redirect()->route('page.show', 'about'));
    Route::get('/contact', fn () => redirect()->route('page.show', 'contact'));
    Route::get('/faq', fn () => redirect()->route('page.show', 'faq'));
    Route::get('/return', fn () => redirect()->route('page.show', 'return-policy'));
    Route::get('/track', [PageController::class, 'track'])->name('track');
    Route::post('/track', [PageController::class, 'track'])->name('track.submit');
    Route::get('/api/countries/{code}/states', [PageController::class, 'states'])->name('api.countries.states');
    Route::get('/currency/{code}', function (string $code) {
        $code = strtoupper($code);
        $countries = config('ecommerce.countries', []);
        if (! array_key_exists($code, $countries)) {
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
    Route::get('/categories/{slug}', fn ($slug) => redirect()->route('shop.category', $slug));

    // Newsletter
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

    // Cart (guest + auth)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [App\Modules\Checkout\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

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
        Route::get('/account', [AccountController::class, 'index'])->name('account.index');
        Route::put('/account', [AccountController::class, 'updateProfile'])->name('account.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
        Route::post('/account/address', [AccountController::class, 'storeAddress'])->name('account.address.store');
        Route::post('/account/address/{address}/default', [AccountController::class, 'setDefaultAddress'])->name('account.address.default');
        Route::delete('/account/address/{address}', [AccountController::class, 'destroyAddress'])->name('account.address.destroy');
    });

    // Instant Buy is now loaded from App\Modules\InstantBuy\routes\web.php


    // Embedded Instant Buy (on product page)
    Route::post('/instant-buy/calculate', [InstantBuyOrderController::class, 'calculate'])->name('instant-buy.calculate');
    Route::post('/instant-buy/shipping-options', [InstantBuyOrderController::class, 'shippingOptions'])->name('instant-buy.shipping-options');
    Route::post('/instant-buy/coupon', [InstantBuyOrderController::class, 'validateCoupon'])->name('instant-buy.coupon');
    Route::post('/instant-buy/submit', [InstantBuyOrderController::class, 'submit'])->name('instant-buy.submit');

    // Admin
    Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/quick-setting', [DashboardController::class, 'quickSetting'])->name('quickSetting');
        Route::resource('products', ProductController::class);
        Route::post('/products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulkAction');
        Route::get('/products/export/csv', [ProductController::class, 'export'])->name('products.export');
        Route::get('/products/{product}/gallery', [ProductController::class, 'gallery'])->name('products.gallery');
        Route::post('/products/{product}/images', [ProductController::class, 'uploadImages'])->name('products.images.upload');
        Route::patch('/products/{product}/images/{image}', [ProductController::class, 'updateImage'])->name('products.images.update');
        Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::post('/products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::resource('categories', CategoryController::class);
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->except(['create', 'store', 'edit']);
        Route::post('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order}/notes', [App\Http\Controllers\Admin\OrderController::class, 'addNote'])->name('orders.notes.store');
        Route::delete('/orders/notes/{note}', [App\Http\Controllers\Admin\OrderController::class, 'deleteNote'])->name('orders.notes.delete');
        Route::post('/orders/bulk-action', [App\Http\Controllers\Admin\OrderController::class, 'bulkAction'])->name('orders.bulkAction');
        Route::get('/orders/{order}/invoice', [OrderPrintController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/label', [OrderPrintController::class, 'customerLabel'])->name('orders.label');
        Route::post('/orders/bulk-invoice', [OrderPrintController::class, 'bulkInvoice'])->name('orders.bulkInvoice');
        Route::post('/orders/bulk-label', [OrderPrintController::class, 'bulkLabel'])->name('orders.bulkLabel');
        Route::resource('coupons', CouponController::class);
        Route::resource('users', UserController::class);

        // Newsletter
        Route::get('/newsletter', [App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::delete('/newsletter/{subscriber}', [App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
        Route::delete('/newsletter/selected/destroy', [App\Http\Controllers\Admin\NewsletterController::class, 'destroySelected'])->name('newsletter.destroySelected');
        Route::get('/newsletter/export', [App\Http\Controllers\Admin\NewsletterController::class, 'export'])->name('newsletter.export');

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Tags
        Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
        Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

        // Pages
        Route::resource('pages', App\Http\Controllers\Admin\PageController::class)->except(['show']);

        // Shipping (zones + methods + companies + labels + tracking)
        Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
        // Companies
        Route::get('/shipping/companies/create', [ShippingController::class, 'createCompany'])->name('shipping.company.create');
        Route::post('/shipping/companies', [ShippingController::class, 'storeCompany'])->name('shipping.company.store');
        Route::get('/shipping/companies/{company}/edit', [ShippingController::class, 'editCompany'])->name('shipping.company.edit');
        Route::put('/shipping/companies/{company}', [ShippingController::class, 'updateCompany'])->name('shipping.company.update');
        Route::delete('/shipping/companies/{company}', [ShippingController::class, 'destroyCompany'])->name('shipping.company.destroy');
        // Zones
        Route::get('/shipping/zones/create', [ShippingController::class, 'createZone'])->name('shipping.zone.create');
        Route::post('/shipping/zones', [ShippingController::class, 'storeZone'])->name('shipping.zone.store');
        Route::get('/shipping/zones/{zone}/edit', [ShippingController::class, 'editZone'])->name('shipping.zone.edit');
        Route::put('/shipping/zones/{zone}', [ShippingController::class, 'updateZone'])->name('shipping.zone.update');
        Route::delete('/shipping/zones/{zone}', [ShippingController::class, 'destroyZone'])->name('shipping.zone.destroy');
        // Methods
        Route::get('/shipping/methods/create', [ShippingController::class, 'createMethod'])->name('shipping.method.create');
        Route::post('/shipping/methods', [ShippingController::class, 'storeMethod'])->name('shipping.method.store');
        Route::get('/shipping/methods/{method}/edit', [ShippingController::class, 'editMethod'])->name('shipping.method.edit');
        Route::put('/shipping/methods/{method}', [ShippingController::class, 'updateMethod'])->name('shipping.method.update');
        Route::delete('/shipping/methods/{method}', [ShippingController::class, 'destroyMethod'])->name('shipping.method.destroy');
        Route::post('/shipping/zones/{zone}/methods', [ShippingController::class, 'storeMethodForZone'])->name('shipping.zone.method.store');
        // Labels (Waybills)
        Route::get('/shipping/labels/create', [ShippingController::class, 'createLabel'])->name('shipping.label.create');
        Route::post('/shipping/labels', [ShippingController::class, 'storeLabel'])->name('shipping.label.store');
        Route::get('/shipping/labels/{label}', [ShippingController::class, 'showLabel'])->name('shipping.label.show');
        Route::post('/shipping/labels/{label}/status', [ShippingController::class, 'updateLabelStatus'])->name('shipping.label.updateStatus');
        Route::post('/shipping/labels/{label}/tracking', [ShippingController::class, 'addTrackingUpdate'])->name('shipping.label.tracking');
        Route::get('/shipping/labels/{label}/pdf', [ShippingController::class, 'printLabel'])->name('shipping.label.pdf');
        Route::post('/shipping/bulk-ship', [ShippingController::class, 'bulkShip'])->name('shipping.bulkShip');
        // Pickup Offices
        Route::get('/shipping/pickups/create', [ShippingController::class, 'createPickup'])->name('shipping.pickup.create');
        Route::post('/shipping/pickups', [ShippingController::class, 'storePickup'])->name('shipping.pickup.store');
        Route::get('/shipping/pickups/{pickup}/edit', [ShippingController::class, 'editPickup'])->name('shipping.pickup.edit');
        Route::put('/shipping/pickups/{pickup}', [ShippingController::class, 'updatePickup'])->name('shipping.pickup.update');
        Route::delete('/shipping/pickups/{pickup}', [ShippingController::class, 'destroyPickup'])->name('shipping.pickup.destroy');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

        // Payment Methods
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
        Route::post('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');

        // Currencies
        Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::post('/currencies', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::post('/currencies/rates', [CurrencyController::class, 'updateRates'])->name('currencies.rates.update');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/remove-image', [SettingsController::class, 'removeImage'])->name('settings.removeImage');

        // Languages
        Route::prefix('languages')->name('languages.')->group(function () {
            Route::get('/', [LanguageController::class, 'index'])->name('index');
            Route::get('/{language}/edit', [LanguageController::class, 'edit'])->name('edit');
            Route::post('/{language}', [LanguageController::class, 'update'])->name('update');
            Route::post('/{language}/toggle-active', [LanguageController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{language}/set-default', [LanguageController::class, 'setDefault'])->name('set-default');
            Route::get('/translations', [LanguageController::class, 'translations'])->name('translations');
            Route::post('/translations/bulk-update', [LanguageController::class, 'bulkUpdateTranslations'])->name('translations.bulk-update');
            Route::post('/translations/create', [LanguageController::class, 'createTranslation'])->name('translations.create');
            Route::post('/translations/{translation}', [LanguageController::class, 'updateTranslation'])->name('translations.update');
            Route::delete('/translations/{translation}', [LanguageController::class, 'deleteTranslation'])->name('translations.delete');
            Route::get('/settings', [LanguageController::class, 'settings'])->name('settings');
            Route::post('/{language}/settings', [LanguageController::class, 'updateSettings'])->name('update-settings');
        });

        // Slider
        Route::resource('slider', SliderController::class)->except(['show']);
        Route::patch('/slider/{slider}/toggle', [SliderController::class, 'toggleActive'])->name('slider.toggle');
        Route::post('/slider/reorder', [SliderController::class, 'reorder'])->name('slider.reorder');

        // Customize
        Route::get('/customize', [CustomizeController::class, 'index'])->name('customize.index');
        Route::post('/customize', [CustomizeController::class, 'update'])->name('customize.update');
        Route::post('/customize/reset', [CustomizeController::class, 'reset'])->name('customize.reset');
        Route::post('/customize/remove-image', [CustomizeController::class, 'removeImage'])->name('customize.removeImage');

        // Instant Buy Settings
        Route::prefix('instant-buy')->name('instant-buy.')->group(function () {
            Route::get('/settings', [InstantBuySettingsController::class, 'index'])->name('settings');
            Route::post('/settings/general', [InstantBuySettingsController::class, 'updateGeneral'])->name('settings.general');
            Route::post('/settings/colors', [InstantBuySettingsController::class, 'updateColors'])->name('settings.colors');
            Route::post('/settings/fields', [InstantBuySettingsController::class, 'updateFields'])->name('settings.fields');
            Route::post('/settings/buttons', [InstantBuySettingsController::class, 'updateButtons'])->name('settings.buttons');
            Route::post('/settings/success', [InstantBuySettingsController::class, 'updateSuccess'])->name('settings.success');
            Route::post('/settings/reset', [InstantBuySettingsController::class, 'resetToDefaults'])->name('settings.reset');
            Route::get('/orders', [InstantBuySettingsController::class, 'orders'])->name('orders');
            Route::post('/orders/{order}/status', [InstantBuySettingsController::class, 'updateOrderStatus'])->name('orders.update-status');
        });

        // Footer Management
        Route::prefix('footer')->name('footer.')->group(function () {
            Route::get('/', [FooterController::class, 'index'])->name('index');
            Route::post('/sections', [FooterController::class, 'storeSection'])->name('sections.store');
            Route::put('/sections/{section}', [FooterController::class, 'updateSection'])->name('sections.update');
            Route::delete('/sections/{section}', [FooterController::class, 'destroySection'])->name('sections.destroy');
            Route::post('/sections/reorder', [FooterController::class, 'reorderSections'])->name('sections.reorder');
            Route::post('/sections/{section}/links', [FooterController::class, 'storeLink'])->name('links.store');
            Route::put('/links/{link}', [FooterController::class, 'updateLink'])->name('links.update');
            Route::delete('/links/{link}', [FooterController::class, 'destroyLink'])->name('links.destroy');
            Route::post('/socials', [FooterController::class, 'storeSocial'])->name('socials.store');
            Route::put('/socials/{social}', [FooterController::class, 'updateSocial'])->name('socials.update');
            Route::delete('/socials/{social}', [FooterController::class, 'destroySocial'])->name('socials.destroy');
        });

        // Invoice Templates
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/templates/{template}/preview', [InvoiceTemplateController::class, 'preview'])->name('templates.preview');
            Route::post('/templates/{template}/default', [InvoiceTemplateController::class, 'setDefault'])->name('templates.default');
            Route::resource('templates', InvoiceTemplateController::class);
        });

        // Order Label Templates
        Route::prefix('order-labels')->name('order-labels.')->group(function () {
            Route::get('/templates/{template}/preview', [LabelTemplateController::class, 'preview'])->name('templates.preview');
            Route::post('/templates/{template}/default', [LabelTemplateController::class, 'setDefault'])->name('templates.default');
            Route::resource('templates', LabelTemplateController::class);
        });

        // Printing Settings
        Route::get('/settings/printing', [PrintingSettingsController::class, 'index'])->name('settings.printing');
        Route::post('/settings/printing', [PrintingSettingsController::class, 'update'])->name('settings.printing.update');
    }); // end admin group
}); // end locale prefix group
