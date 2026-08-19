<?php

namespace Database\Seeders;

use App\Models\Content\Slide;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'عروض هيرو الشتاء',
                'subtitle' => 'خصومات تصل إلى 50% على كل المنتجات',
                'description' => 'عروض موسمية محدودة على التجهيزات الشتوية',
                'badge' => 'خصم 50%',
                'image' => null,
                'mobile_image' => null,
                'link' => 'https://example.com',
                'btn_text' => 'تسوق الآن',
                'button_target' => '_same',
                'sort_order' => 1,
                'is_active' => true,
                'animation_effect' => 'fade',
                'entrance_effect' => 'fade-up',
            ],
            [
                'title' => 'مجموعة الأزياء الجديدة',
                'subtitle' => 'صيحات هذا الموسم مباشرة إليك',
                'description' => 'اكتشف أحدث صيحات الأزياء من العلامات العالمية',
                'badge' => 'جديد',
                'image' => null,
                'mobile_image' => null,
                'link' => 'https://example.com/new-arrivals',
                'btn_text' => 'عرض المجموعة',
                'button_target' => '_same',
                'sort_order' => 2,
                'is_active' => true,
                'animation_effect' => 'slide-left',
                'entrance_effect' => 'fade-right',
            ],
            [
                'title' => 'شحن مجاني',
                'subtitle' => 'على جميع الطلبات فوق 5000 د.ج',
                'description' => 'توصيل سريع لجميع المدن، الدفع عند الاستلام',
                'badge' => 'شحن مجاني',
                'image' => null,
                'mobile_image' => null,
                'link' => 'https://example.com/free-shipping',
                'btn_text' => 'تفاصيل الشحن',
                'button_target' => '_same',
                'sort_order' => 3,
                'is_active' => true,
                'animation_effect' => 'zoom',
                'entrance_effect' => 'zoom',
            ],
        ];

        foreach ($slides as $slide) {
            Slide::firstOrCreate(
                ['title' => $slide['title']],
                $slide
            );
        }

        $this->command->info('Seeded '.count($slides).' slider slides with animation effects.');
    }
}
