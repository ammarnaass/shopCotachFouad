<?php

namespace App\Providers;

use App\Models\Cart\Wishlist;
use App\Models\Catalog\Category;
use App\Models\Content\Page;
use App\Models\InstantBuy\InstantBuyOrder;
use App\Models\Order\Order;
use App\Models\Settings\Setting;
use App\Services\CartService;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TranslationService::class, function () {
            return new TranslationService;
        });
    }

    public function boot(): void
    {
        // Share wishlist count with every frontend view (lightweight, cached for request)
        View::composer('frontend.*', function ($view) {
            $wishlistCount = auth()->check()
                ? Wishlist::where('user_id', auth()->id())->count()
                : 0;
            $view->with('wishlistCount', $wishlistCount);

            try {
                $cartCount = app(CartService::class)->getCart()->total_items;
            } catch (\Throwable $e) {
                $cartCount = 0;
            }
            $view->with('cartCount', $cartCount);
        });

        // Share categories and pages with all frontend views (as a Collection, not array)
        View::composer('frontend.partials.header', function ($view) {
            $selectedCatIds = json_decode(Setting::get('nav_categories_list', '[]'), true) ?: [];
            if (! empty($selectedCatIds)) {
                $categories = Category::whereIn('id', $selectedCatIds)
                    ->where('status', 'active')
                    ->orderBy('order')
                    ->get();
            } else {
                $categories = Category::where('status', 'active')
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->take(6)
                    ->get();
            }

            $selectedPageIds = json_decode(Setting::get('nav_pages_list', '[]'), true) ?: [];
            if (! empty($selectedPageIds)) {
                $pages = Page::whereIn('id', $selectedPageIds)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            } else {
                $pages = collect();
            }

            $navItemsOrderJson = Setting::get('nav_items_order', '');
            $navItemsOrder = $navItemsOrderJson ? json_decode($navItemsOrderJson, true) : [];
            $catMap = $categories->keyBy('id');
            $pageMap = $pages->keyBy('id');

            if (! empty($navItemsOrder)) {
                $builtins = ['home', 'products', 'contact'];
                foreach ($builtins as $b) {
                    if (! in_array($b, $navItemsOrder)) {
                        $navItemsOrder[] = $b;
                    }
                }
                $navItems = [];
                foreach ($navItemsOrder as $key) {
                    if (in_array($key, $builtins)) {
                        $navItems[] = ['type' => $key, 'data' => null, 'key' => $key];
                    } elseif (preg_match('/^cat-(\d+)$/', $key, $m) && isset($catMap[(int) $m[1]])) {
                        $cat = $catMap[(int) $m[1]];
                        $navItems[] = ['type' => 'category', 'data' => $cat, 'key' => $key];
                    } elseif (preg_match('/^page-(\d+)$/', $key, $m) && isset($pageMap[(int) $m[1]])) {
                        $page = $pageMap[(int) $m[1]];
                        $navItems[] = ['type' => 'page', 'data' => $page, 'key' => $key];
                    }
                }
            } else {
                $navItems = [];
                $navItems[] = ['type' => 'home', 'data' => null, 'key' => 'home'];
                $navItems[] = ['type' => 'products', 'data' => null, 'key' => 'products'];
                foreach ($categories as $cat) {
                    $navItems[] = ['type' => 'category', 'data' => $cat, 'key' => 'cat-'.$cat->id];
                }
                foreach ($pages as $page) {
                    $navItems[] = ['type' => 'page', 'data' => $page, 'key' => 'page-'.$page->id];
                }
                $navItems[] = ['type' => 'contact', 'data' => null, 'key' => 'contact'];
            }

            $view->with([
                'navCategories' => $categories,
                'navPages' => $pages,
                'navItems' => $navItems,
                'navItemsOrder' => $navItemsOrder,
            ]);
        });

        // Share stats with all admin views (needed by the layout for notifications)
        View::composer('admin.layout', function ($view) {
            $stats = [
                'pending_orders' => Order::where('status', 'pending')->count(),
                'pending_instant_orders' => InstantBuyOrder::where('status', 'new')->count(),
            ];
            $view->with('stats', $stats);
        });

        // Blade directive: renders a category icon (FA or Material Symbols)
        // Usage: @categoryIcon($category->icon ?? 'local_offer', 'text-2xl text-brand-600')
        Blade::directive('categoryIcon', function ($expression) {
            return "<?php
                \$__iconArgs = [{$expression}];
                \$iconVal = \$__iconArgs[0] ?? 'local_offer';
                \$cls = \$__iconArgs[1] ?? '';
                if (\$iconVal && (str_starts_with(\$iconVal, 'fa-') || str_starts_with(\$iconVal, 'fas ') || str_starts_with(\$iconVal, 'fab ') || str_starts_with(\$iconVal, 'far '))) {
                    echo '<i class=\"fa-solid ' . e(\$iconVal) . ' ' . e(\$cls) . '\"></i>';
                } elseif (empty(\$iconVal)) {
                    echo '<span class=\"material-symbols-outlined ' . e(\$cls) . '\">local_offer</span>';
                } else {
                    echo '<span class=\"material-symbols-outlined ' . e(\$cls) . '\">' . e(\$iconVal) . '</span>';
                }
            ?>";
        });
    }
}
