@extends('admin.layout')

@section('title', 'إعدادات الطباعة والمستندات')
@section('page_title', 'إعدادات الطباعة والمستندات')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-on-surface">إعدادات الطباعة والافتراضيات</h3>
            <p class="text-sm text-on-surface-variant">تحديد القوالب والمقاسات الافتراضية للفواتير والملصقات عند الطباعة السريعة</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.printing.update') }}" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30 space-y-6">
        @csrf

        {{-- Invoices Default --}}
        <div class="space-y-4">
            <h4 class="font-bold text-base text-on-surface flex items-center gap-2 border-b border-outline-variant pb-2">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                إعدادات الفواتير الافتراضية
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1">القالب الافتراضي للفاتورة</label>
                    <select name="default_invoice_template" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                        <option value="">-- اختر قالب افتراضي --</option>
                        @foreach($invoiceTemplates as $tpl)
                            <option value="{{ $tpl->id }}" {{ $printing['default_invoice_template'] == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->paper_size_label }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1">حجم الورق الافتراضي للفاتورة</label>
                    <select name="default_invoice_size" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                        @foreach(\App\Models\Documents\InvoiceTemplate::PAPER_SIZES as $sKey => $sLabel)
                            <option value="{{ $sKey }}" {{ $printing['default_invoice_size'] === $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Labels Default --}}
        <div class="space-y-4 border-t border-outline-variant pt-4">
            <h4 class="font-bold text-base text-on-surface flex items-center gap-2 border-b border-outline-variant pb-2">
                <span class="material-symbols-outlined text-primary">label</span>
                إعدادات ملصقات الطلبات الافتراضية
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1">القالب الافتراضي للملصق</label>
                    <select name="default_label_template" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                        <option value="">-- اختر قالب افتراضي --</option>
                        @foreach($labelTemplates as $lTpl)
                            <option value="{{ $lTpl->id }}" {{ $printing['default_label_template'] == $lTpl->id ? 'selected' : '' }}>{{ $lTpl->name }} ({{ $lTpl->paper_size_label }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1">حجم ورق الملصق الافتراضي</label>
                    <select name="default_label_size" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                        @foreach(\App\Models\Documents\LabelTemplate::PAPER_SIZES as $lKey => $lLabel)
                            <option value="{{ $lKey }}" {{ $printing['default_label_size'] === $lKey ? 'selected' : '' }}>{{ $lLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- General Options --}}
        <div class="space-y-4 border-t border-outline-variant pt-4">
            <h4 class="font-bold text-base text-on-surface">خيارات إضافية</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer p-3 bg-surface-container-low rounded-xl">
                    <input type="checkbox" name="show_logo" value="1" {{ $printing['show_logo'] ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    عرض شعار المتجر على المستندات مالم يُلغَ من القالب
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer p-3 bg-surface-container-low rounded-xl">
                    <input type="checkbox" name="show_barcode" value="1" {{ $printing['show_barcode'] ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    توليد الـ Barcode تلقائياً على الملصقات والفواتير
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer p-3 bg-surface-container-low rounded-xl">
                    <input type="checkbox" name="show_qr" value="1" {{ $printing['show_qr'] ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    عرض QR Code ينقل لرابط الطلب
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface cursor-pointer p-3 bg-surface-container-low rounded-xl">
                    <input type="checkbox" name="auto_generate_invoice" value="1" {{ $printing['auto_generate_invoice'] ? 'checked' : '' }} class="rounded border-outline-variant text-primary">
                    توليد رقم الفاتورة تلقائياً عند فتح الطلب أو الطباعة
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
            <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">حفظ الإعدادات</button>
        </div>
    </form>
</div>
@endsection
