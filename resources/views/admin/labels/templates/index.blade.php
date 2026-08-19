@extends('admin.layout')

@section('title', 'قوالب ملصقات الطلبات')
@section('page_title', 'إدارة قوالب ملصقات الطلبات')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-on-surface">قوالب ملصقات الطلبات</h3>
            <p class="text-sm text-on-surface-variant">إدارة قوالب الملصقات المستخدمة للطباعة ولصقها على الشحنات والطرود</p>
        </div>
        <a href="{{ route('admin.order-labels.templates.create') }}"
           class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">add</span>
            إنشاء قالب ملصق جديد
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($templates as $template)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-lg text-on-surface flex items-center gap-2">
                                {{ $template->name }}
                                @if($template->is_default)
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full">الافتراضي</span>
                                @endif
                            </h4>
                            <p class="text-xs text-outline mt-1">{{ $template->description ?: 'لا يوجد وصف' }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs text-on-surface-variant my-4 bg-surface-container-low p-3 rounded-xl">
                        <div class="flex justify-between"><span>الأبعاد / المقاس:</span><strong class="text-on-surface">{{ $template->paper_size_label }}</strong></div>
                        <div class="flex justify-between"><span>عرض الباركود:</span><strong class="{{ $template->getSetting('show_barcode') ? 'text-emerald-600' : 'text-outline' }}">{{ $template->getSetting('show_barcode') ? 'مفعل' : 'معطل' }}</strong></div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-outline-variant">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.order-labels.templates.preview', [$template, 'print' => 1]) }}" target="_blank"
                           class="px-3 py-1.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-lg text-xs font-semibold transition-all flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            معاينة
                        </a>
                        <a href="{{ route('admin.order-labels.templates.edit', $template) }}"
                           class="p-1.5 text-primary hover:bg-primary/10 rounded-lg">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </a>
                    </div>

                    @if(!$template->is_default)
                        <form action="{{ route('admin.order-labels.templates.default', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary/20 rounded-lg text-xs font-bold transition-all">
                                تعيين كافتراضي
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
