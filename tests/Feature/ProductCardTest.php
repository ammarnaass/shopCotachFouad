<?php

namespace Tests\Feature;

use App\Models\Catalog\Product;
use App\Models\Content\Review;
use App\Models\User\User;
use Tests\TestCase;

class ProductCardTest extends TestCase
{
    /**
     * A product with approved reviews renders a gold rating row with the
     * expected data-rating attribute, a 5-star meter, and the ringed CTA.
     */
    public function test_card_with_reviews_renders_rating_and_ringed_cta(): void
    {
        $product = Product::factory()->create([
            'name' => 'اداة ازالة شعر الوجة بالخيط',
            'slug' => 'thread-hair-removal-tool',
            'price' => 49000,
            'sale_price' => 43000,
            'stock' => 15,
            'status' => 'active',
        ]);

        // Three approved 5-star reviews from three different users -> avg = 5.0000.
        foreach (range(1, 3) as $i) {
            $user = User::factory()->create();

            Review::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => 5,
                'comment' => 'ممتاز',
                'status' => 'approved',
            ]);
        }

        $loaded = Product::with(['primaryImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($product->id);

        $html = view('frontend.partials.product-card', [
            'product' => $loaded,
            'symbol' => 'د.ج',
        ])->render();

        $this->assertStringContainsString('data-rating="5.0000"', $html);
        $this->assertStringContainsString('(3)', $html);
        $this->assertStringContainsString('class="pc-cta', $html);
        $this->assertStringContainsString(__t('product.press_to_order', [], 'ar'), $html);
        $this->assertStringContainsString('shopping_bag', $html);
        $this->assertStringContainsString('pc-price__compare', $html);
        $this->assertStringContainsString('pc-price__current', $html);
    }

    /**
     * A product with no reviews still renders 5 star slots (all empty) and
     * a zero review count.
     */
    public function test_card_without_reviews_renders_empty_star_meter(): void
    {
        $product = Product::factory()->create([
            'name' => 'منتج بدون تقييم',
            'slug' => 'no-review-product',
            'price' => 1000,
            'sale_price' => null,
            'stock' => 5,
            'status' => 'active',
        ]);

        $loaded = Product::with(['primaryImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($product->id);

        $html = view('frontend.partials.product-card', [
            'product' => $loaded,
            'symbol' => 'د.ج',
        ])->render();

        // 5 star slots always render (one Material Symbols span per slot).
        $this->assertSame(5, preg_match_all('/material-symbols-outlined pc-stars__slot/', $html));
        // ... and all 5 render as empty when there is no rating.
        $this->assertSame(5, preg_match_all('/pc-stars__slot--empty/', $html));
        $this->assertStringContainsString('(0)', $html);
        $this->assertStringContainsString('data-rating="0.0000"', $html);
        // No compare price when there is no discount.
        $this->assertStringNotContainsString('pc-price__compare', $html);
    }

    /**
     * Out-of-stock product shows a disabled CTA, the out-of-stock label,
     * the do_not_disturb_on icon, and the is-disabled class.
     */
    public function test_out_of_stock_card_renders_disabled_cta(): void
    {
        $product = Product::factory()->create([
            'name' => 'منتج نفد',
            'slug' => 'sold-out-product',
            'price' => 2000,
            'stock' => 0,
            'status' => 'active',
        ]);

        $loaded = Product::with(['primaryImage'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($product->id);

        $html = view('frontend.partials.product-card', [
            'product' => $loaded,
            'symbol' => 'د.ج',
        ])->render();

        $this->assertStringContainsString('is-disabled', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('do_not_disturb_on', $html);
        $this->assertStringContainsString(__t('product.out_of_stock_label', [], 'ar'), $html);
    }

    /**
     * The card links to the product details page via the slug.
     */
    public function test_card_links_to_product_page(): void
    {
        $product = Product::factory()->create([
            'name' => 'منتج الرابط',
            'slug' => 'link-test-product',
            'price' => 500,
            'stock' => 10,
            'status' => 'active',
        ]);

        $loaded = Product::find($product->id);

        $html = view('frontend.partials.product-card', [
            'product' => $loaded,
            'symbol' => 'د.ج',
        ])->render();

        $this->assertStringContainsString('/shop/link-test-product', $html);
    }
}
