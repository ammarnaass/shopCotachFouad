<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store General Settings
    |--------------------------------------------------------------------------
    */
    'store' => [
        'name' => env('APP_NAME', 'Amar Store'),
        'currency' => env('STORE_CURRENCY', 'DZD'),
        'currency_symbol' => env('STORE_CURRENCY_SYMBOL', 'د.ج'),
        'tax_rate' => env('STORE_TAX_RATE', 0),
        'free_shipping_threshold' => env('STORE_FREE_SHIPPING_THRESHOLD', 5000),
        'default_country' => env('STORE_DEFAULT_COUNTRY', 'DZ'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Countries & States (North Africa focus)
    |--------------------------------------------------------------------------
    */
    'countries' => [
        'DZ' => [
            'name' => 'الجزائر',
            'name_en' => 'Algeria',
            'dial_code' => '+213',
            'currency' => 'DZD',
            'currency_symbol' => 'د.ج',
            'rate_to_usd' => 0.0075,
            'states' => [
                '01' => 'أدرار', '02' => 'الشلف', '03' => 'الأغواط', '04' => 'أم البواقي',
                '05' => 'باتنة', '06' => 'بجاية', '07' => 'بسكرة', '08' => 'بشار',
                '09' => 'البليدة', '10' => 'البويرة', '11' => 'تمنراست', '12' => 'تبسة',
                '13' => 'تلمسان', '14' => 'تيارت', '15' => 'تيزي وزو', '16' => 'الجزائر',
                '17' => 'الجلفة', '18' => 'جيجل', '19' => 'سطيف', '20' => 'سعيدة',
                '21' => 'سكيكدة', '22' => 'سيدي بلعباس', '23' => 'عنابة', '24' => 'قالمة',
                '25' => 'قسنطينة', '26' => 'المدية', '27' => 'مستغانم', '28' => 'المسيلة',
                '29' => 'معسكر', '30' => 'ورقلة', '31' => 'وهران', '32' => 'البيض',
                '33' => 'إليزي', '34' => 'برج بوعريريج', '35' => 'بومرداس', '36' => 'الطارف',
                '37' => 'تندوف', '38' => 'تيسمسيلت', '39' => 'الوادي', '40' => 'خنشلة',
                '41' => 'سوق أهراس', '42' => 'تيبازة', '43' => 'ميلة', '44' => 'عين الدفلى',
                '45' => 'النعامة', '46' => 'عين تموشنت', '47' => 'غرداية', '48' => 'غليزان',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cash on Delivery (COD) Settings
    |--------------------------------------------------------------------------
    */
    'cod' => [
        'enabled' => env('COD_ENABLED', true),
        'display_name' => 'الدفع عند الاستلام',
        'description' => 'ادفع نقدا عند استلام طلبك',
        'min_order' => env('COD_MIN_ORDER', 500),
        'max_order' => env('COD_MAX_ORDER', 50000),
        'extra_fee' => env('COD_EXTRA_FEE', 0),
        'fee_label' => 'رسوم الدفع عند الاستلام',
        'allowed_cities' => ['*'],
        'blocked_cities' => [],
        'excluded_categories' => [],
        'excluded_products' => [],
        'phone_confirmation' => env('COD_PHONE_CONFIRMATION', true),
        'confirmation_method' => 'sms',
        'auto_confirm_after' => 24,
        'deposit_enabled' => false,
        'deposit_percentage' => 20,
        'notify_admin_on_order' => true,
        'notify_customer_on_confirm' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Settings
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'default_company' => env('SHIPPING_DEFAULT_COMPANY', 'noest'),
        'free_threshold' => env('SHIPPING_FREE_THRESHOLD', 5000),
        'default_country' => env('STORE_DEFAULT_COUNTRY', 'DZ'),

        'zones' => [
            // ===== المدن الكبرى (400 د.ج) =====
            ['name' => 'الجزائر', 'countries' => ['DZ'], 'cities' => ['16'], 'cost' => 400, 'express_cost' => 700, 'free_threshold' => 5000],
            ['name' => 'وهران', 'countries' => ['DZ'], 'cities' => ['31'], 'cost' => 400, 'express_cost' => 700, 'free_threshold' => 5000],
            ['name' => 'قسنطينة', 'countries' => ['DZ'], 'cities' => ['25'], 'cost' => 400, 'express_cost' => 700, 'free_threshold' => 5000],
            ['name' => 'عنابة', 'countries' => ['DZ'], 'cities' => ['23'], 'cost' => 400, 'express_cost' => 700, 'free_threshold' => 5000],
            ['name' => 'سطيف', 'countries' => ['DZ'], 'cities' => ['19'], 'cost' => 400, 'express_cost' => 700, 'free_threshold' => 5000],

            // ===== الولايات الساحلية والقريبة (500 د.ج) =====
            ['name' => 'بجاية', 'countries' => ['DZ'], 'cities' => ['06'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'تيزي وزو', 'countries' => ['DZ'], 'cities' => ['15'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'البليدة', 'countries' => ['DZ'], 'cities' => ['09'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'بومرداس', 'countries' => ['DZ'], 'cities' => ['35'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'البويرة', 'countries' => ['DZ'], 'cities' => ['10'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'المدية', 'countries' => ['DZ'], 'cities' => ['26'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'تيبازة', 'countries' => ['DZ'], 'cities' => ['42'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'سكيكدة', 'countries' => ['DZ'], 'cities' => ['21'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'جيجل', 'countries' => ['DZ'], 'cities' => ['18'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'قالمة', 'countries' => ['DZ'], 'cities' => ['24'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'الطارف', 'countries' => ['DZ'], 'cities' => ['36'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'مستغانم', 'countries' => ['DZ'], 'cities' => ['27'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'غليزان', 'countries' => ['DZ'], 'cities' => ['48'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'عين تموشنت', 'countries' => ['DZ'], 'cities' => ['46'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'تلمسان', 'countries' => ['DZ'], 'cities' => ['13'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'الشلف', 'countries' => ['DZ'], 'cities' => ['02'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'بسكرة', 'countries' => ['DZ'], 'cities' => ['07'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'المسيلة', 'countries' => ['DZ'], 'cities' => ['28'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'ميلة', 'countries' => ['DZ'], 'cities' => ['43'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'عين الدفلى', 'countries' => ['DZ'], 'cities' => ['44'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'تيارت', 'countries' => ['DZ'], 'cities' => ['14'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'برج بوعريريج', 'countries' => ['DZ'], 'cities' => ['34'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'النعامة', 'countries' => ['DZ'], 'cities' => ['45'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'سوق أهراس', 'countries' => ['DZ'], 'cities' => ['41'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'خنشلة', 'countries' => ['DZ'], 'cities' => ['40'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'تيسمسيلت', 'countries' => ['DZ'], 'cities' => ['38'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],
            ['name' => 'معسكر', 'countries' => ['DZ'], 'cities' => ['29'], 'cost' => 500, 'express_cost' => 800, 'free_threshold' => 5000],

            // ===== الولايات الداخلية والصعبة الوصول (600 د.ج) =====
            ['name' => 'باتنة', 'countries' => ['DZ'], 'cities' => ['05'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'الجلفة', 'countries' => ['DZ'], 'cities' => ['17'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'أم البواقي', 'countries' => ['DZ'], 'cities' => ['04'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'سيدي بلعباس', 'countries' => ['DZ'], 'cities' => ['22'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'الأغواط', 'countries' => ['DZ'], 'cities' => ['03'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'الوادي', 'countries' => ['DZ'], 'cities' => ['39'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'بشار', 'countries' => ['DZ'], 'cities' => ['08'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'تبسة', 'countries' => ['DZ'], 'cities' => ['12'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'البيض', 'countries' => ['DZ'], 'cities' => ['32'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'سعيدة', 'countries' => ['DZ'], 'cities' => ['20'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],
            ['name' => 'ورقلة', 'countries' => ['DZ'], 'cities' => ['30'], 'cost' => 600, 'express_cost' => 950, 'free_threshold' => 5000],

            // ===== الولايات الصحراوية (700 د.ج) =====
            ['name' => 'أدرار', 'countries' => ['DZ'], 'cities' => ['01'], 'cost' => 700, 'express_cost' => 1100, 'free_threshold' => 5000],
            ['name' => 'تمنراست', 'countries' => ['DZ'], 'cities' => ['11'], 'cost' => 700, 'express_cost' => 1100, 'free_threshold' => 5000],
            ['name' => 'تندوف', 'countries' => ['DZ'], 'cities' => ['37'], 'cost' => 700, 'express_cost' => 1100, 'free_threshold' => 5000],
            ['name' => 'غرداية', 'countries' => ['DZ'], 'cities' => ['47'], 'cost' => 700, 'express_cost' => 1100, 'free_threshold' => 5000],
            ['name' => 'إليزي', 'countries' => ['DZ'], 'cities' => ['33'], 'cost' => 700, 'express_cost' => 1100, 'free_threshold' => 5000],
        ],

        'companies' => [
            'noest' => [
                'name' => 'Noest Express',
                'tracking_url' => 'https://www.noest-dz.com/tracking/{TRACKING}',
                'api_enabled' => false,
            ],
            'maystro' => [
                'name' => 'Maystro Delivery',
                'tracking_url' => 'https://maystro.com/track/{TRACKING}',
                'api_enabled' => false,
            ],
        ],

        'options' => [
            'standard' => ['name' => 'عادي', 'days' => '3-5'],
            'express' => ['name' => 'سريع', 'days' => '1-2'],
            'same_day' => ['name' => 'فوري', 'days' => '0', 'cities' => ['الجزائر']],
        ],
    ],

    'languages' => [
        'supported' => ['ar', 'en', 'fr'],
        'default' => env('APP_LOCALE', 'ar'),
        'cookie_name' => 'locale',
        'cookie_minutes' => 43200,
        'hide_default_prefix' => true,
    ],

    // Root-level aliases for backward compatibility
    'default_country' => env('STORE_DEFAULT_COUNTRY', 'DZ'),
    'default_currency' => env('STORE_CURRENCY', 'DZD'),
    'default_currency_symbol' => env('STORE_CURRENCY_SYMBOL', 'د.ج'),

];
