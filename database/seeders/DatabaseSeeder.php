<?php

namespace Database\Seeders;

use App\Models\Catalog\Category;
use App\Models\Catalog\Coupon;
use App\Models\User\Permission;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\ProductOption;
use App\Models\Catalog\ProductOptionValue;
use App\Models\User\Role;
use App\Models\Shipping\ShippingCompany;
use App\Models\Shipping\ShippingZone;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LanguageSeeder::class);
        $this->call(TranslationSeeder::class);

        $this->command->info('Seeding roles & permissions...');
        $this->seedRolesAndPermissions();

        $this->command->info('Seeding users...');
        $this->seedUsers();

        $this->command->info('Seeding categories...');
        $this->seedCategories();

        $this->command->info('Seeding shipping...');
        $this->seedShipping();

        $this->command->info('Seeding coupons...');
        $this->seedCoupons();

        $this->command->info('Seeding pages...');
        $this->call(PageSeeder::class);

        $this->command->info('Seeding sliders...');
        $this->call(SliderSeeder::class);

        $this->command->info('Seeding products...');
        $this->seedProducts();

        $this->command->info('All seeded successfully!');
    }

    private function seedRolesAndPermissions(): void
    {
        // Roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'مدير', 'description' => 'مدير النظام - صلاحيات كاملة']
        );
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            ['display_name' => 'مدير متجر', 'description' => 'إدارة المتجر والطلبات']
        );
        $customerRole = Role::firstOrCreate(
            ['name' => 'customer'],
            ['display_name' => 'عميل', 'description' => 'عميل عادي']
        );

        // Permissions
        $permissions = [
            ['manage_products', 'إدارة المنتجات', 'products'],
            ['manage_orders', 'إدارة الطلبات', 'orders'],
            ['manage_users', 'إدارة المستخدمين', 'users'],
            ['manage_categories', 'إدارة التصنيفات', 'categories'],
            ['manage_coupons', 'إدارة الكوبونات', 'coupons'],
            ['manage_settings', 'إدارة الإعدادات', 'settings'],
        ];

        foreach ($permissions as [$name, $display, $group]) {
            $perm = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $display, 'group' => $group]
            );
            // Admin gets all
            $adminRole->permissions()->syncWithoutDetaching([$perm->id]);
        }

        // Manager gets products/orders/categories/coupons
        $managerPerms = Permission::whereIn('name', [
            'manage_products', 'manage_orders', 'manage_categories', 'manage_coupons'
        ])->pluck('id');
        $managerRole->permissions()->syncWithoutDetaching($managerPerms->toArray());
    }

    private function seedUsers(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@amarstore.com'],
            [
                'name' => 'مدير المتجر',
                'phone' => '0911111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $manager = User::firstOrCreate(
            ['email' => 'manager@amarstore.com'],
            [
                'name' => 'مدير المبيعات',
                'phone' => '0922222222',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $managerRole = Role::where('name', 'manager')->first();
        $manager->roles()->syncWithoutDetaching([$managerRole->id]);

        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'عميل تجريبي',
                'phone' => '0933333333',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedCategories(): void
    {
        $categories = [
            ['إلكترونيات', 'electronics', 'هواتف، لابتوبات، إكسسوارات تقنية'],
            ['ملابس رجالية', 'mens-clothing', 'قمصان، بناطيل، أحذية رجالية'],
            ['ملابس نسائية', 'womens-clothing', 'فساتين، بلوزات، أحذية نسائية'],
            ['أحذية', 'shoes', 'أحذية رياضية وكلاسيكية'],
            ['إكسسوارات', 'accessories', 'ساعات، حقائب، مجوهرات'],
            ['منزل ومطبخ', 'home-kitchen', 'أدوات منزلية ومطبخية'],
            ['عطور وتجميل', 'beauty', 'عطور، مستحضرات تجميل'],
            ['ألعاب أطفال', 'toys', 'ألعاب تعليمية وترفيهية'],
        ];

        foreach ($categories as $i => [$name, $slug, $description]) {
            Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => 'active',
                    'order' => $i + 1,
                ]
            );
        }
    }

    private function seedShipping(): void
    {
        $zones = [
            [
                'name' => 'الجزائر - العاصمة',
                'regions' => ['*'],
                'countries' => ['DZ'],
                'cities' => ['الجزائر', 'البليدة', 'بومرداس', 'تيزي وزو', 'البويرة', 'المدية', 'تيبازة'],
                'cost' => 400,
                'express_cost' => 700,
                'free_threshold' => 5000,
            ],
            [
                'name' => ' الجزائري - باقي الولايات',
                'regions' => ['*'],
                'countries' => ['DZ'],
                'cities' => ['*'],
                'cost' => 600,
                'express_cost' => 950,
                'free_threshold' => 7000,
            ],
        ];

        foreach ($zones as $zone) {
            ShippingZone::firstOrCreate(['name' => $zone['name']], $zone + ['status' => 'active']);
        }

        // Shipping companies
        $companies = [
            ['noest', 'نوست إكسبرس', 'https://www.noest-dz.com/tracking/{TRACKING}'],
            ['maystro', 'ميسترو ديليفري', 'https://maystro.com/track/{TRACKING}'],
        ];

        foreach ($companies as [$slug, $name, $url]) {
            ShippingCompany::firstOrCreate(
                ['name' => $name],
                [
                    'tracking_url' => $url,
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedCoupons(): void
    {
        $coupons = [
            ['WELCOME10', 'percent', 10, 500, null, 100, 'كوبون ترحيب - خصم 10%'],
            ['SAVE20', 'fixed', 200, 1000, null, 500, 'خصم ثابت 200 د.ج'],
            ['SUMMER25', 'percent', 25, 2000, 1000, 50, 'عرض الصيف - 25%'],
            ['FREESHIP', 'fixed', 500, 2000, null, null, 'شحن مجاني يعادل 500 د.ج'],
        ];

        foreach ($coupons as [$code, $type, $value, $minOrder, $maxDiscount, $limit, $desc]) {
            Coupon::firstOrCreate(
                ['code' => $code],
                [
                    'type' => $type,
                    'value' => $value,
                    'min_order' => $minOrder,
                    'max_discount' => $maxDiscount,
                    'usage_limit' => $limit,
                    'status' => 'active',
                    'expiry_date' => now()->addMonths(3),
                ]
            );
        }
    }

    private function seedProducts(): void
    {
        $products = [
            // Electronics
            ['هاتف سامسونج جالاكسي S24', 'samsung-galaxy-s24', 'هاتف ذكي بمواصفات عالية وكاميرا احترافية', 4500, 4200, 25, 'إلكترونيات', 'simple', true, 'phone'],
            ['لابتوب ديل XPS 13', 'dell-xps-13', 'لابتوب خفيف وأنيق للأعمال والبرمجة', 8500, 7999, 10, 'إلكترونيات', 'simple', true, 'laptop'],
            ['سماعات AirPods برو', 'airpods-pro', 'سماعات لاسلكية بميزة إلغاء الضوضاء', 1200, 1099, 50, 'إلكترونيات', 'simple', true, 'headphones'],
            ['شاحن سريع 65 واط', 'fast-charger-65w', 'شاحن سريع متوافق مع جميع الأجهزة', 150, 120, 100, 'إلكترونيات', 'simple', false, 'charger'],

            // Men's Clothing
            ['قميص رسمي أزرق', 'formal-shirt-blue', 'قميص رسمي بقماش قطني عالي الجودة', 250, 199, 30, 'ملابس رجالية', 'variable', true, 'shirt'],
            ['بنطلون جينز كلاسيك', 'classic-jeans', 'بنطلون جينز متين ومريح', 350, 299, 25, 'ملابس رجالية', 'variable', false, 'jeans'],
            ['بدلة رجالية سوداء', 'black-suit', 'بدلة رسمية للمناسبات', 2500, 2200, 10, 'ملابس رجالية', 'simple', true, 'suit'],

            // Women's Clothing
            ['فستان سهرة أحمر', 'red-evening-dress', 'فستان أنيق للمناسبات الخاصة', 1500, 1299, 15, 'ملابس نسائية', 'variable', true, 'dress'],
            ['بلوزة قطن صيفية', 'summer-blouse', 'بلوزة قطنية مريحة للصيف', 180, 150, 40, 'ملابس نسائية', 'variable', false, 'blouse'],
            ['حقيبة يد جلدية', 'leather-handbag', 'حقيبة يد أنيقة من الجلد الطبيعي', 800, 699, 20, 'إكسسوارات', 'simple', true, 'bag'],

            // Shoes
            ['حذاء رياضي نايك', 'nike-sneakers', 'حذاء رياضي مريح للجري والاستخدام اليومي', 650, 549, 30, 'أحذية', 'variable', true, 'shoes'],
            ['حذاء كلاسيكي جلد', 'classic-leather-shoes', 'حذاء جلد طبيعي للمناسبات الرسمية', 950, null, 20, 'أحذية', 'variable', false, 'shoes'],

            // Home
            ['طقم أواني طبخ 10 قطع', 'cookware-set-10', 'طقم أواني طبخ من الستانلس ستيل', 1200, 999, 15, 'منزل ومطبخ', 'simple', true, 'kitchen'],
            ['مكنسة كهربائية ذكية', 'smart-vacuum', 'مكنسة كهربائية ذكية بتحكم عن بعد', 1800, 1599, 8, 'منزل ومطبخ', 'simple', false, 'vacuum'],

            // Beauty
            ['عطر ديور سوفاج', 'dior-sauvage', 'عطر رجالي فاخر', 1500, 1299, 20, 'عطور وتجميل', 'simple', true, 'perfume'],
            ['مجموعة عناية بالبشرة', 'skincare-set', 'مجموعة كاملة للعناية اليومية', 450, 399, 25, 'عطور وتجميل', 'simple', false, 'skincare'],
        ];

        foreach ($products as $i => [$name, $slug, $desc, $price, $salePrice, $stock, $category, $type, $featured, $icon]) {
            $cat = Category::where('slug', $this->categorySlug($category))->first();

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cat?->id,
                    'name' => $name,
                    'description' => $desc . "\n\nمنتج أصلي 100% مع ضمان سنة كاملة. شحن سريع وتوصيل لكل مدن السودان. الدفع عند الاستلام متاح.",
                    'short_description' => $desc,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'stock' => $stock,
                    'type' => $type,
                    'status' => 'active',
                    'featured' => $featured,
                    'seo_title' => $name,
                    'seo_description' => $desc,
                ]
            );

            // Add product options for variable products
            if ($type === 'variable' && $product->options()->count() === 0) {
                $this->addProductOptions($product, $icon);
            }
        }
    }

    private function categorySlug(string $name): string
    {
        return match ($name) {
            'إلكترونيات' => 'electronics',
            'ملابس رجالية' => 'mens-clothing',
            'ملابس نسائية' => 'womens-clothing',
            'أحذية' => 'shoes',
            'إكسسوارات' => 'accessories',
            'منزل ومطبخ' => 'home-kitchen',
            'عطور وتجميل' => 'beauty',
            'ألعاب أطفال' => 'toys',
            default => 'electronics',
        };
    }

    private function addProductOptions(Product $product, string $type): void
    {
        if (in_array($type, ['shirt', 'dress', 'blouse', 'jeans', 'suit'])) {
            // Size option
            $sizeOption = $product->options()->create([
                'name' => 'المقاس',
                'type' => 'select',
                'required' => true,
                'order' => 1,
            ]);

            $sizes = [
                ['S', 0],
                ['M', 0],
                ['L', 50],
                ['XL', 100],
                ['XXL', 150],
            ];

            foreach ($sizes as [$size, $adjustment]) {
                $sizeOption->values()->create([
                    'value' => $size,
                    'price_adjustment' => $adjustment,
                    'stock' => 10,
                ]);
            }
        }

        if (in_array($type, ['shirt', 'dress', 'blouse', 'shoes'])) {
            // Color option
            $colorOption = $product->options()->create([
                'name' => 'اللون',
                'type' => 'color',
                'required' => true,
                'order' => 2,
            ]);

            $colors = [
                ['أسود', '#000000', 0],
                ['أبيض', '#FFFFFF', 0],
                ['أحمر', '#DC2626', 0],
                ['أزرق', '#2563EB', 20],
                ['أخضر', '#16A34A', 20],
            ];

            foreach ($colors as [$color, $code, $adjustment]) {
                $colorOption->values()->create([
                    'value' => $color,
                    'color_code' => $code,
                    'price_adjustment' => $adjustment,
                    'stock' => 15,
                ]);
            }
        }
    }
}
