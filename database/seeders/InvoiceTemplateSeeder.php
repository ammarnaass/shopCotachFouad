<?php

namespace Database\Seeders;

use App\Models\Documents\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'        => 'القالب الكلاسيكي',
                'slug'        => 'classic',
                'description' => 'قالب فواتير A4 كلاسيكي، مناسب لجميع أنواع المتاجر',
                'paper_size'  => 'a4',
                'is_default'  => true,
                'status'      => true,
                'settings'    => [
                    'primary_color'      => '#004ac6',
                    'show_logo'          => true,
                    'show_sku'           => true,
                    'show_product_image' => false,
                    'show_discount'      => true,
                    'show_shipping'      => true,
                    'show_payment_method'=> true,
                    'show_notes'         => true,
                    'show_customer_info' => true,
                    'thank_you_message'  => 'شكراً لتسوقكم من متجرنا',
                ],
            ],
            [
                'name'        => 'القالب العصري',
                'slug'        => 'modern',
                'description' => 'تصميم عصري بخلفية غامقة في الهيدر، يمنح مظهرًا احترافيًا',
                'paper_size'  => 'a4',
                'is_default'  => false,
                'status'      => true,
                'settings'    => [
                    'primary_color'      => '#0f172a',
                    'show_logo'          => true,
                    'show_sku'           => true,
                    'show_product_image' => true,
                    'show_discount'      => true,
                    'show_shipping'      => true,
                    'show_payment_method'=> true,
                    'show_notes'         => true,
                    'show_customer_info' => true,
                    'thank_you_message'  => 'نتمنى لكم تجربة ممتازة مع منتجاتنا',
                ],
            ],
            [
                'name'        => 'القالب البسيط (Minimal)',
                'slug'        => 'minimal',
                'description' => 'تصميم هادئ وبسيط بدون ألوان كثيرة، موفر للحبر',
                'paper_size'  => 'a4',
                'is_default'  => false,
                'status'      => true,
                'settings'    => [
                    'primary_color'      => '#334155',
                    'show_logo'          => true,
                    'show_sku'           => false,
                    'show_product_image' => false,
                    'show_discount'      => true,
                    'show_shipping'      => true,
                    'show_payment_method'=> true,
                    'show_notes'         => true,
                    'show_customer_info' => true,
                    'thank_you_message'  => 'شكراً لكم',
                ],
            ],
            [
                'name'        => 'قالب طابعة الإيصالات (Thermal 80mm)',
                'slug'        => 'thermal',
                'description' => 'قالب مخصص لطابعات الفواتير الحرارية 80 مم / 58 مم',
                'paper_size'  => 'thermal_80',
                'is_default'  => false,
                'status'      => true,
                'settings'    => [
                    'primary_color'      => '#000000',
                    'show_logo'          => false,
                    'show_sku'           => false,
                    'show_product_image' => false,
                    'show_discount'      => true,
                    'show_shipping'      => true,
                    'show_payment_method'=> true,
                    'show_notes'         => false,
                    'show_customer_info' => true,
                    'thank_you_message'  => 'شكراً لتسوقكم معنا!',
                ],
            ],
        ];

        foreach ($templates as $t) {
            InvoiceTemplate::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
