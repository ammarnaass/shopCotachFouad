@extends('admin.layout')

@section('title', 'تعديل قالب فاتورة: ' . $template->name)
@section('page_title', 'تعديل قالب فاتورة: ' . $template->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-on-surface">تعديل قالب الفاتورة</h3>
        <a href="{{ route('admin.invoices.templates.index') }}" class="px-4 py-2 bg-surface-container-high text-on-surface rounded-xl text-sm font-semibold hover:bg-surface-container-highest">رجوع</a>
    </div>

    <form action="{{ route('admin.invoices.templates.update', $template) }}" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">اسم القالب</label>
                <input type="text" name="name" value="{{ $template->name }}" required class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">حجم الورق</label>
                <select name="paper_size" required class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                    @foreach(\App\Models\Documents\InvoiceTemplate::PAPER_SIZES as $sizeKey => $sizeLabel)
                        <option value="{{ $sizeKey }}" {{ $template->paper_size === $sizeKey ? 'selected' : '' }}>{{ $sizeLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-on-surface mb-1">وصف القالب</label>
            <input type="text" name="description" value="{{ $template->description }}" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
        </div>

        <div class="border-t border-outline-variant pt-4 space-y-4">
            <h4 class="font-bold text-sm text-on-surface">خيارات المظهر والعرض</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_logo]" value="1" {{ $template->getSetting('show_logo') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار الشعار
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_sku]" value="1" {{ $template->getSetting('show_sku') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار SKU المنتجات
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_product_image]" value="1" {{ $template->getSetting('show_product_image') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار صور المنتجات
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_discount]" value="1" {{ $template->getSetting('show_discount') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار الخصم
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_shipping]" value="1" {{ $template->getSetting('show_shipping') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار سعر الشحن
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer">
                    <input type="checkbox" name="settings[show_payment_method]" value="1" {{ $template->getSetting('show_payment_method') ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    إظهار طريقة الدفع
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-outline-variant pt-4">
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">اللون الرئيسي للقالب</label>
                <input type="color" name="settings[primary_color]" value="{{ $template->getSetting('primary_color', '#004ac6') }}" class="w-full h-10 p-1 border border-outline-variant rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">رسالة الشكر بالفوتر</label>
                <input type="text" name="settings[thank_you_message]" value="{{ $template->getSetting('thank_you_message') }}" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">تحديث القالب</button>
        </div>
    </form>
</div>
@endsection
