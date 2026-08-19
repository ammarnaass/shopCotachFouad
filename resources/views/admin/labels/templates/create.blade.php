@extends('admin.layout')

@section('title', 'إنشاء قالب ملصق جديد')
@section('page_title', 'إنشاء قالب ملصق جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-on-surface">إعدادات قالب الملصق الجديد</h3>
        <a href="{{ route('admin.order-labels.templates.index') }}" class="px-4 py-2 bg-surface-container-high text-on-surface rounded-xl text-sm font-semibold hover:bg-surface-container-highest">رجوع</a>
    </div>

    <form action="{{ route('admin.order-labels.templates.store') }}" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">اسم القالب</label>
                <input type="text" name="name" required placeholder="مثال: ملصق طرد حراري 100x150" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">المقاس / الأبعاد</label>
                <select name="paper_size" id="paper_size_select" required onchange="toggleCustomDimensions(this.value)" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                    @foreach(\App\Models\Documents\LabelTemplate::PAPER_SIZES as $sizeKey => $sizeLabel)
                        <option value="{{ $sizeKey }}">{{ $sizeLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="custom_dimensions_row" class="grid grid-cols-2 gap-4 hidden">
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">العرض (مم)</label>
                <input type="number" name="custom_width" placeholder="100" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">الارتفاع (مم)</label>
                <input type="number" name="custom_height" placeholder="150" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-on-surface mb-1">وصف الملصق</label>
            <input type="text" name="description" placeholder="ملاحظات حول هذا القالب" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
        </div>

        <div class="border-t border-outline-variant pt-4 space-y-4">
            <h4 class="font-bold text-sm text-on-surface">العناصر المعروضة على الملصق</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_logo]" value="1" checked class="rounded border-outline-variant text-primary">
                    إظهار الشعار
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_barcode]" value="1" checked class="rounded border-outline-variant text-primary">
                    إظهار الـ Barcode
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_qr]" value="1" class="rounded border-outline-variant text-primary">
                    إظهار الـ QR Code
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_total]" value="1" checked class="rounded border-outline-variant text-primary">
                    إظهار المبلغ المطلوب
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_payment_method]" value="1" checked class="rounded border-outline-variant text-primary">
                    إظهار طريقة الدفع
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_product_count]" value="1" checked class="rounded border-outline-variant text-primary">
                    إظهار عدد المنتجات
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">حفظ القالب</button>
        </div>
    </form>
</div>

<script>
    function toggleCustomDimensions(val) {
        document.getElementById('custom_dimensions_row').classList.toggle('hidden', val !== 'custom');
    }
</script>
@endsection
