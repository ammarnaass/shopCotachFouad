# إصلاح تبديل المظهر (فاتح/داكن) و تداخل الألوان في المتجر

## السياق والمشكلة الجذرية

المتجر يعتمد على **Tailwind 4** (بدون `tailwind.config.js` — كل التصميم في `@theme` داخل `resources/css/app.css`). لوحة التحكم (`admin.customize`) تعرض 4 خيارات مظهر عبر `CustomizeController::themes()`: `light` / `dark` / `colorful` / `minimal`. عند الحفظ، القيمة تُخزَّن في إعداد `site_theme`.

تم تحديد **4 أسباب جذرية** مترابطة تسبب "تداخل الألوان" وعدم تطبيق المظهر في المتجر:

### 1. (حاسم) اختصار `dark:` في Tailwind 4 لا يستجيب لـ class
في Tailwind 4، اختصار `dark:` يعمل افتراضياً عبر `@media (prefers-color-scheme: dark)` (أي تفضيل نظام التشغيل)، **لا** عبر كلاس `html.dark`. لا يوجد في أي مكان CSS أو JS تعريف `@custom-variant dark (&:where(.dark, .dark *));`.

نتيجة: مئات فئات `dark:*` في `header.blade.php`, `shop/index.blade.php`, `shop/show.blade.php`, `shop/category.blade.php`, `alpine-components.blade.php`, `footer.blade.php` تعتمد على تبديل class — لكنها **لا تعمل إطلاقاً** عند اختيار "داكن" من لوحة التحكم. فقط القواعد المكتوبة يدوياً `html.dark …` (في `app.css` الأسطر 645-879 و`product-card.css`) تعمل. هذا يُنتج ظهوراً جزئياً للألوان الداكنة مع بقاء عناصر فاتحة = "تداخل الألوان".

### 2. كلاس `dark` لا يُضاف إلى `<html>` إطلاقاً
`layout.blade.php` (السطور 1-10):
```php
$themeClass = match($appTheme) {
    'colorful' => 'theme-colorful',
    'minimal'  => 'theme-minimal',
    default    => '',   // ← dark و light كلاهما يسقطان هنا → ''
};
```
عند اختيار "داكن"، يصبح `class="scroll-smooth "` بدون `dark`. كل قواعد `html.dark` (المكتوبة يدوياً واختصار `dark:` إن修复ناها) لن تُطبَّق.

### 3. تضارب اسم مفتاح الإعداد
- `layout.blade.php:2`: `site('theme', site('site_theme', 'light'))`
- `CustomizeController::index:45`: `'theme' => $this->settings->get('site_theme', 'light')`
- `CustomizeController::update:240-242`: يحفظ `$key='theme'` ثم ينسخ إلى `site_theme`.

الإدخال `name="theme"` في النموذج يُخزَّن فعلياً كمفتاح `theme` (السطر 239) **و** كمفتاح `site_theme` (السطر 241). لكن القِراءة في الـ layout تفضل `theme` أولاً ثم `site_theme` كfallback. بما أن `theme` يُحفظ دائماً (السطر 239)، فإن الـ fallback نادراً ما يُلجأ إليه — لكن وجود مفتاحين يُربك المطور ويزيد خطر الانفصال (مثلاً عند `reset()` الذي يُعيد فقط `site_theme`).

### 4. عبث العميل بـ localStorage + toggle ميت
`resources/js/alpine/stores/theme.js`:
- `init()` يحذف `localStorage.theme` و`amar:theme` عند كل تحميل
- `toggle(){}` **دالة فارغة**
- لا يوجد أي زر تبديل في الواجهة (`grep theme.toggle` // `dark_mode` في blade = لا شيء)

القرار التصميمي: **المظهر يتحكم به الأدمن فقط** (لا توجد أداة تبديل للزبون). تم تأكيد ذلك عبر عدم وجود أي زر toggle في blade ووجود `localStorage.removeItem` كإجراء "نظافة".

### ملاحظة: علامات `check` التي يراها المستخدم
في `customize/index.blade.php:94` علامة الصح تظهر لكل راديو `checked`، لكن المستخدم يرى 3 علامات `check` متتالية في نصه — هذا يوحي بأن CSS `peer-checked` للعلامة (السطور 94, 80) معطوب لأنه يعتمد على `.peer` + `peer-checked:` وهي فئات Tailwind قد تكون متأثرة أيضاً بغياب الـ variant الصحيح لو كانت داخل نطاق dark — لكنها فعلياً تعمل في اللوحة (اللوحة تستخدم Layout مختلف `admin.layout` بدون `dark`). الشكوى "هذه الاوان و معايير لا تطبق في متجر" = المظهر واللون المُختاران لا ينعكسان على واجهة المتجر العامة (`frontend/layout.blade.php`).

---

## القرارات (مُحددة)

1. **تفعيل اختصار `dark:` عبر class** عبر إضافة `@custom-variant dark (&:where(.dark, .dark *));` في `resources/css/app.css`. هذا يجعل جميع فئات `dark:*` الموجودة تعمل عند وجود `class="dark"` على `<html>` — موحِّداً كل التبديل مع القواعد اليدوية `html.dark` الموجودة. (موصى به: خيار أقل توتراً، لا يتطلب لمس مئات ملفات blade).

2. **توحيد مفتاح الإعداد على `site_theme` فقط**:
   - إزالة التخزين المكرر لمفتاح `theme` في `CustomizeController::update` (السطر 239 لكلاسي `theme`) والاكتفاء بالكتابة إلى `site_theme`.
   - تبسيط قراءة `layout.blade.php:2` إلى `site('site_theme', 'light')`.
   - إضافة `'theme'` إلى مصفوفة `reset()` في `CustomizeController` (يحذفها أو يُعيد كتابة كليهما إلى `light`).

   - **تنبيه المُنفِّذ**: التحقق من عدم وجود قراءات أخرى لـ `site('theme'…)` (بدون `_theme`) عبر `grep -rn "site('theme'" app/ resources/`. إن وُجدت، تُوحَّد أيضاً إلى `site_theme`.

3. **تطبيق كلاس `dark` على `<html>`** داخل `layout.blade.php` عبر توسيع `$themeClass` match:
```php
$themeClass = match($appTheme) {
    'dark'      => 'dark',
    'colorful'  => 'theme-colorful',
    'minimal'   => 'theme-minimal',
    default     => '',
};
```
الاحتفاظ بـ `data-theme="{{ $appTheme }}"` لتشخيص أداة المطورين.

4. **معالجة تضارب المظاهر مع داكن**: "ملون" و"بسيط" هما مظاهر فاتحة. سؤال تصميم: هل يمكن اختيار "داكن" + "ملون" معاً؟ حالياً هما متنافيان (radio واحد). لذلك `dark` و`theme-colorful` لا يجتمعان أبداً → آمن. يبقى التعامل مع `colorful`/`minimal` في الوضع الداكن خارج النطاق (لا يوجد طلب).

5. **عدم إضافة زر تبديل للزبون** (متفق: الفهم التصميمي = أدمن فقط). لكن:
   - تحديث `theme.js` ليكون متسقاً: إما حذفه (لا دور فعلي) أو إبقاؤه كقراءة-only من `document.documentElement` (لا toggle). **توصية**: إبقاؤه قراءة-only ووضع وضوح تعليق "Admin-controlled; do not toggle".

6. **عدم إضافة مظاهر درجة الحرارة المعتمد على النظام**: `dark:` سيُشغَّل عبر class فقط الآن. لا `prefers-color-scheme`. هذا متسق مع نموذج "الأدمن يقرر".

---

## المهام (بالترتيب)

- [ ] **T1 — تخصيص dark variant**: في `resources/css/app.css`، مباشرة بعد `@import 'tailwindcss';` (السطر 13 تقريباً) أضف:
  ```css
  @custom-variant dark (&:where(.dark, .dark *));
  ```
  ثم إعادة بناء أصول Vite (`npm run build` أو `php artisan vite:build` حسب الإعداد).

- [ ] **T2 — تطبيق كلاس dark على html**: في `resources/views/frontend/layout.blade.php` السطر 3-7 عدّل الـ match ليشمل فرع `'dark' => 'dark'`.

- [ ] **T3 — توحيد مفتاح الإعداد**:
  - `app/Http/Controllers/Admin/CustomizeController.php`:
    - `update()`: أزل `$this->settings->set($key, ...)` للمفتاح `theme` (أو غيِّره ليُكتب إلى `site_theme` مباشرة). الأبسط: قبل حلقة `foreach($data …)`، `(unset) $data['theme']; $this->settings->set('site_theme', $data['theme'] ?? 'light', 'customize');` ثم أكمل الحلقة على بقية المفاتيح.
    - `reset()`: أضف `'theme' => 'light'` لحذف القيمة اليتيمة إن وُجدت.
  - `layout.blade.php:2`: استبدل بـ `$appTheme = site('site_theme', 'light');`.
  - شغّل `grep -rn "site('theme'" app/ resources/` للتحقق من أي مرجع يتيم لـ `theme` (بدون `_theme`). وحّدها.

- [ ] **T4 — تنظيف theme.js**: في `resources/js/alpine/stores/theme.js`:
  - أبقِ `dark: document.documentElement.classList.contains('dark')` للقراءة.
  - أضف تعليق قصير يوضح أن الإدارة هي من تتحكم (Admin-panel controlled).
  - أبطل جسم `toggle(){}` بتعليق "// Admin-controlled — no client toggle".
  - احذف استدعاءات `localStorage.removeItem` (لم تعد ضرورية بعد إزالة آلية تفضيل العميل) **أو** أبقها كإجراء دفاعي. اختيار تنفيذي: أبقها (أكثر أماناً).

- [ ] **T5 — التحقق البصري (لا تعديل)**: افتح المتجر في 4 حالات (light/dark/colorful/minimal) وتأكد:
  - وضع داكن: كل العناصر (header قوائم، قوائم البحث، بطاقات المنتج `pc*`، صفحة المنتج، الفوتر) تأخذ ألوانها الداكنة (لا عناصر بيضاء متبقية).
  - وضع ملون/بسيط: الخلفيات والألوان الموسومة تتغير.
  - بعد تخصيص لون primary/accent: تنعكس على الأزرار والشارات.

## التحقق (Validation)

- `npm run build` (أو الأمر الفعلي في `package.json` — تحقق منه) ينجح بدون أخطاء CSS.
- `php artisan view:clear && php artisan cache:clear` بعد التغييرات (تُنظف view cache).
- فتح المتجر بصفتك زائر، تغيير المظهر من اللوحة، حفظ، ثم تحديث الصفحة: يتغير المظهر فوراً على المتجر.
- نعم/لا FOUC: بما أن `$themeClass` يُحقن في `<html>` من الخادم، لا يجب أن يكون هناك وميض.
- لا `console.error` في DevTools بشأن Alpine.
- اختبر مسار `reset()`: يعيد المظهر إلى فاتح ويختفي كلاس `dark`.

## المخاطر والملاحظات

- **خطر تكرار黑暗 من النظام**: بعد T1، المظهر الداكن يتبع `class` الآن فقط؛ لن يظهر الليلي تلقائياً على أنظمة الوضع الداكن إلا إذا اختار الأدمن "داكن". هذا هو السلوك المرغوب.
- **ملفات خارج نطاق Vite**: لو توجد ملفات `*.css` أخرى محمَّلة بـ `<link>` مباشر (لا ضمن `app.css`) وتستخدم `dark:`، يجب تخصيص variant فيها أيضاً. تحقق من `grep -rn "link.*\.css" resources/views/frontend/`.
- ** blade admin layout**: لوحة التحكم (`admin/layout.blade.php`) قد لا تحتوي على كلاس `dark` — بالتالي فئات `dark:` فيها لن تعمل، وهذا مقصود (اللوحة فاتحة). تحقق أن الإصلاح لا يؤثر على شكل اللوحة. (المتوقع: لا تأثير لأن اللوحة لا تضيف `dark` إلى html).
- **`welcome.blade.php`**: يحتوي CSS Tailwind مُجمَّع inline (سطر 16) — ربما نسخة قديمة. تجاهله (صفحة welcome نادراً تُستخدم في الإنتاج).
- **لا tracos للنصوص (RTL)**: التغييرات لا تلمس الاتجاه. `data-theme` و`$themeClass` مستقلان عن `dir`.

## خارج النطاق

- إضافة زر تبديل داكن/فاتح للزبون.
- دمج `colorful`/`minimal` مع الوضع الداكن (حالياً متنافيان).
- تغيير لوحة الألوان نفسها (palette) — فقط إصلاح آلية التطبيق.
- تحديث ترجمات اللوحة.
