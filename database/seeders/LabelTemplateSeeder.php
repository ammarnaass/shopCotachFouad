<?php

namespace Database\Seeders;

use App\Models\Documents\LabelTemplate;
use Illuminate\Database\Seeder;

class LabelTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'        => 'ملصق شحن قياسي (100×150 mm)',
                'slug'        => 'classic',
                'description' => 'ملصق شحن متكامل يحتوي على باركود ورقم الطلب وبيانات العميل والمبلغ المطلوب',
                'paper_size'  => '100x150',
                'is_default'  => true,
                'status'      => true,
                'settings'    => [
                    'show_logo'           => true,
                    'show_barcode'        => true,
                    'show_qr'             => false,
                    'show_product_count'  => true,
                    'show_total'          => true,
                    'show_payment_method' => true,
                    'show_status'         => false,
                ],
            ],
            [
                'name'        => 'ملصق مدمج (80×50 mm)',
                'slug'        => 'compact',
                'description' => 'ملصق صغير الحجم مخصص للطرود الصغيرة',
                'paper_size'  => '80x50',
                'is_default'  => false,
                'status'      => true,
                'settings'    => [
                    'show_logo'           => false,
                    'show_barcode'        => true,
                    'show_qr'             => false,
                    'show_product_count'  => true,
                    'show_total'          => true,
                    'show_payment_method' => true,
                    'show_status'         => false,
                ],
            ],
            [
                'name'        => 'ملصق حراري (Thermal)',
                'slug'        => 'thermal',
                'description' => 'قالب ملصق حراري لطابعات الملصقات السريعة',
                'paper_size'  => '100x100',
                'is_default'  => false,
                'status'      => true,
                'settings'    => [
                    'show_logo'           => false,
                    'show_barcode'        => true,
                    'show_qr'             => false,
                    'show_product_count'  => true,
                    'show_total'          => true,
                    'show_payment_method' => true,
                    'show_status'         => false,
                ],
            ],
        ];

        foreach ($templates as $t) {
            LabelTemplate::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
