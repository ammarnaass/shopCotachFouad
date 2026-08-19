<?php

$file = 'G:\project progamming\shopCotachFouad\lang\ar.json';
$json = json_decode(file_get_contents($file), true);

$keys = array_keys($json);
$insertAfter = 'admin.slider.image_auto';
$newKeys = [];

foreach ($keys as $key) {
    $newKeys[$key] = $json[$key];
    if ($key === $insertAfter) {
        $newKeys['admin.slider.desktop'] = 'سطح المكتب';
        $newKeys['admin.slider.mobile_image'] = 'صورة الجوال';
        $newKeys['admin.slider.mobile_image_recommended'] = 'المقاس المقترح: 1080×1200';
        $newKeys['admin.slider.current_mobile_image'] = 'صورة الجوال الحالية';
        $newKeys['admin.slider.description'] = 'الوصف';
        $newKeys['admin.slider.button_target'] = 'فتح الرابط';
        $newKeys['admin.slider.button_target_same'] = 'نفس الصفحة';
        $newKeys['admin.slider.button_target_blank'] = 'صفحة جديدة';
        $newKeys['admin.slider.starts_at'] = 'بداية العرض';
        $newKeys['admin.slider.ends_at'] = 'نهاية العرض';
        $newKeys['admin.slider.starts_at_help'] = 'اترك فارغاً للظهور فوراً';
        $newKeys['admin.slider.ends_at_help'] = 'اترك فارغاً لعدم انتهاء العرض';
        $newKeys['admin.slider.reorder_success'] = 'تم تحديث الترتيب بنجاح';
        $newKeys['admin.slider.drag_hint'] = 'اسحب لترتيب الشرائح';
        $newKeys['admin.slider.immediate'] = 'فوري';
        $newKeys['admin.slider.no_end'] = 'لا ينتهي';
        $newKeys['admin.slider.always'] = 'دائم';
        $newKeys['admin.slider.delete_confirm'] = 'هل أنت متأكد من حذف هذه الشريحة؟';
        $newKeys['home.hero_slider_label'] = 'محتوى الهيرو المتحرك';
        $newKeys['home.slider_navigation'] = 'تنقل السلايدر';
        $newKeys['home.slide_selector'] = 'اختر شريحة';
        $newKeys['home.prev_slide'] = 'الشريحة السابقة';
        $newKeys['home.next_slide'] = 'الشريحة التالية';
        $newKeys['home.go_to_slide'] = 'الذهاب للشريحة :num';
    }
}

file_put_contents($file, json_encode($newKeys, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Done\n";