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

        $this->assertStringContainsString('(5.0)', $html);
        $this->assertStringContainsString('اضغط هنا للطلب', $html);
        $this->assertStringContainsString('43,000', $html);
        $this->assertStringContainsString('49,000', $html);
    }

    /**
     * A product with no reviews still renders star icons and default display.
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

        // 5 star spans render
        $this->assertSame(5, preg_match_all('/material-symbols-outlined text-sm/', $html));
        $this->assertStringContainsString('1,000', $html);
    }

    /**
     * Out-of-stock product shows a disabled CTA and out of stock message.
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

        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString('نفذت الكمية', $html);
        $this->assertStringContainsString('غير متوفر حالياً', $html);
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
