# خطة: شريط التنقل — تحكم وإخفاء وإعادة ترتيب العناصر

## المشكلة الحالية

شريط التنقل في `frontend/partials/header.blade.php` (سطر 77-95) يعرض العناصر بترتيب ثابت:
**الرئيسية → المنتجات → التصنيفات → الصفحات ← اتصل بنا**

- **التحكم/الإخفاء** موجود بالفعل عبر إعدادات `nav_show_home/products/categories/contact` في صفحة التخصيص.
- **إعادة ترتيب العناصر** غير موجودة — لا يمكن للمدير تغيير ترتيب العناصر في الشريط.

## الحل

إضافة نظام **سحب وإفلات (drag & drop)** لإعادة ترتيب عناصر شريط التنقل في لوحة التحكم، مع الحفاظ على التحكم بالإظهار/الإخفاء لكل عنصر.

### نموذج البيانات

تخزين سلسلة JSON جديدة في إعدادات `settings`:

```json
"nav_items_order"
```

تحتوي على مصفوفة مرتبة من مفاتيح العناصر:

```json
["home", "products", "cat-5", "cat-2", "page-3", "page-1", "contact"]
```

- `home`, `products`, `contact` → عناصر ثابتة
- `cat-{id}` → تصنيف فعال (يُضاف/يُحذف من القائمة)
- `page-{id}` → صفحة فعّالة (تُضاف/تُحذف من القائمة)

### الملفات المطلوب تعديلها

#### 1. `app/Http/Controllers/Admin/CustomizeController.php`

**في `index()`:**
- تحميل `nav_items_order` من الإعدادات (القيمة الافتراضية: `["home", "products", "contact"]`)

**في `update()`:**
- استقبال وحفظ `nav_items_order` كـ JSON
- عند تغيير التصنيفات/الصفحات المحددة (`nav_categories_list`، `nav_pages_list`)، تحديث `nav_items_order` تلقائياً بإضافة العناصر الجديدة أو إزالة المحذوفة

**التحقق:**
```php
'nav_items_order' => 'nullable|string|max:2000',
```

#### 2. `app/Providers/AppServiceProvider.php`

تحديث `View::composer('frontend.partials.header')`:

- قراءة `nav_items_order` من الإعدادات
- فك تشفيره واستخراج معرّفات التصنيفات (`cat-{id}`) والصفحات (`page-{id}`)
- جلب التصنيفات والصفحات بناءً على هذه المعرّفات (مصفوفة مرتبة)
- تمرير `navItemsOrder` و `navItems` (مصفوفة مرتبة من الكائنات) إلى الـ View

#### 3. `resources/views/frontend/partials/header.blade.php`

**استبدال** الجزء الثابت (سطر 77-95) بحلقة تمر على `navItems` المُرتبة:

```php
<nav class="hidden md:flex items-center gap-4">
    @foreach($navItems as $item)
        @if($item['type'] === 'home' && site('nav_show_home', '1') === '1')
            <a class="..." href="{{ route('home') }}">{{ __t('nav.home') }}</a>
        @elseif($item['type'] === 'products' && site('nav_show_products', '1') === '1')
            <a class="..." href="{{ route('shop.index') }}">{{ __t('nav.products') }}</a>
        @elseif($item['type'] === 'category' && site('nav_show_categories', '1') === '1')
            <a class="..." href="{{ route('shop.category', ['slug' => $item['data']->slug]) }}">{{ $item['data']->name }}</a>
        @elseif($item['type'] === 'page')
            <a class="..." href="{{ route('page.show', ['slug' => $item['data']->slug]) }}">{{ $item['data']->title }}</a>
        @elseif($item['type'] === 'contact' && site('nav_show_contact', '1') === '1')
            <a class="..." href="{{ route('page.show', ['slug' => 'contact']) }}">{{ __t('nav.contact') }}</a>
        @endif
    @endforeach
</nav>
```

#### 4. `resources/views/admin/customize/index.blade.php`

**قسم `header` tab:** استبدال واجهة checkboxes الحالية بواجهة سحب وإفلات شاملة:

**الجزء العلوي — عناصر التنقل الحالية (مرتبة):**
- قائمة سحب وإفلات لكل عنصر (`drag_indicator`)
- مفتاح toggle لكل عنصر (إظهار/إخفاء)
- زر حذف للعناصر القابلة للحذف (التصنيفات والصفحات)

**الجزء السفلي — إضافة عناصر جديدة:**
- قائمة التصنيفات المتاحة (غير المضافة بعد) — زر إضافة
- قائمة الصفحات المتاحة (غير المضافة بعد) — زر إضافة

**Alpine.js Component:**
```javascript
x-data="{
    items: {{ $navItemsOrder }},  // ['home', 'products', 'cat-5', 'contact']
    allCategories: {{ $allCategories }},  // كل التصنيفات
    allPages: {{ $allPages }},  // كل الصفحات
    toggleMap: { home: 'nav_show_home', products: 'nav_show_products', ... },
    toggleVisibility(key) { ... },
    addItem(type, id) { ... },
    removeItem(idx) { ... },
    moveUp(idx) { ... },
    moveDown(idx) { ... },
    dragIdx: null,
    swap(i) { ... }
}"
```

**حقل مخفي:**
```html
<input type="hidden" name="nav_items_order" :value="JSON.stringify(items)">
```

#### 5. ملفات اللغة

إضافة مفاتيح جديدة في `lang/ar.json`، `lang/en.json`، `lang/fr.json`:

```
"admin.customize.nav_reorder": "إعادة ترتيب عناصر التنقل"
"admin.customize.nav_reorder_hint": "اسحب لإعادة ترتيب العناصر، واستخدم المفتاح لإظهار/إخفاء"
"admin.customize.nav_add_category": "إضافة تصنيف"
"admin.customize.nav_add_page": "إضافة صفحة"
"admin.customize.nav_no_more_categories": "لا توجد تصنيفات أخرى"
"admin.customize.nav_no_more_pages": "لا توجد صفحات أخرى"
```

### التوافق مع الإعدادات الحالية

- `nav_show_home/products/categories/contact` → **تبقى كما هي** (تتحكم بإظهار العنصر)
- `nav_categories_list` → **تبقى كما هي** (تحدد أي التصنيفات متاحة)
- `nav_pages_list` → **تبقى كما هي** (تحدد أي الصفحات متاحة)
- `nav_items_order` → **جديد** (يحدد ترتيب العناصر المعروضة)

### منطق التحميل (Fallback)

إذا `nav_items_order` غير موجود أو فارغ:
1. بناء القائمة تلقائياً من الإعدادات الحالية: `["home", "products", ...cat_ids, ...page_ids, "contact"]`
2. هذا يضمن عدم كسر أي متجر موجود

### ملاحظات التنفيذ

- لا حاجة لـ migration — الإعدادات تُخزّن في جدول `settings` كـ key/value
- لا حاجة لإضافة مكتبات خارجية — Alpine.js + Tailwind CSS كافيان (نفس نمط `home_section_order` الموجود)
- يُستخدم نمط السحب والإفلات المدمج في Alpine.js (`@dragstart/@dragover/@drop`) كما في قسم التخصيص

### التحقق من العمل

1. فتح لوحة التحكم → التخصيص → تبويب "شريط التنقل"
2. سحب عناصر التنقل لإعادة ترتيبها
3. تبديل مفاتيح الإظهار/الإخفاء
4. إضافة/حذف تصنيف أو صفحة
5. حفظ → التحقق من الترتيب الجديد في الموقع
6. التحقق من أن RTL يعرض العناصر بالترتيب الصحيح
