@extends('admin.layout')

@section('title', 'إدارة الفوتر')
@section('page_title', 'إدارة فوتر المتجر')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-on-surface">أقسام وروابط الفوتر</h3>
            <p class="text-sm text-on-surface-variant">إدارة الهيكل والروابط وحسابات التواصل التي تظهر في أسفل صفحات المتجر</p>
        </div>
        <button onclick="document.getElementById('add-section-modal').classList.remove('hidden')"
                class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">add</span>
            إضافة قسم جديد
        </button>
    </div>

    {{-- Social Accounts Section --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4 border-b border-outline-variant pb-3">
            <h4 class="font-bold text-lg text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">share</span>
                حسابات التواصل الاجتماعي
            </h4>
            <button onclick="document.getElementById('add-social-modal').classList.remove('hidden')"
                    class="px-3 py-1.5 bg-surface-container-high text-on-surface rounded-lg text-xs font-semibold hover:bg-surface-container-highest transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">add</span>
                إضافة حساب
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($socials as $social)
                <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color: {{ $social->platform_color }}">
                            {{ strtoupper(substr($social->platform, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-on-surface">{{ $social->platform_label }}</p>
                            <a href="{{ $social->url }}" target="_blank" class="text-xs text-primary hover:underline truncate max-w-[140px] block" dir="ltr">{{ $social->url }}</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <form action="{{ route('admin.footer.socials.destroy', $social) }}" method="POST" onsubmit="return confirm('هل انت متاكد من الحذف؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-error hover:bg-error/10 rounded-lg">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-on-surface-variant text-sm">
                    لم يتم إضافة أي حسابات تواصل بعد.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Footer Sections & Links --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sections as $section)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-outline-variant/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-outline-variant pb-3 mb-4">
                        <div>
                            <h4 class="font-bold text-base text-on-surface">{{ $section->title }}</h4>
                            <span class="text-[11px] px-2 py-0.5 bg-primary/10 text-primary font-semibold rounded-full">{{ $section->type_name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <form action="{{ route('admin.footer.sections.destroy', $section) }}" method="POST" onsubmit="return confirm('حذف القسم وسيتم حذف كافة الروابط المرتبطة به؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-error hover:bg-error/10 rounded-lg" title="حذف">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($section->type === 'links')
                        <div class="space-y-2 mb-4">
                            @forelse($section->links as $link)
                                <div class="p-2 bg-surface-container-low rounded-lg flex items-center justify-between text-sm">
                                    <div>
                                        <span class="font-medium text-on-surface">{{ $link->title }}</span>
                                        <span class="text-xs text-outline block" dir="ltr">{{ $link->url }}</span>
                                    </div>
                                    <form action="{{ route('admin.footer.links.destroy', $link) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-error hover:opacity-80 p-1">
                                            <span class="material-symbols-outlined text-[16px]">close</span>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-outline text-center py-3">لا يوجد روابط داخل هذا القسم</p>
                            @endforelse
                        </div>
                        <button onclick="openAddLinkModal({{ $section->id }}, '{{ e($section->title) }}')"
                                class="w-full py-2 bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            إضافة رابط
                        </button>
                    @elseif($section->type === 'custom_html')
                        <div class="p-3 bg-surface-container-low rounded-xl text-xs font-mono overflow-x-auto text-on-surface mb-3">
                            {{ Str::limit($section->custom_html, 150) }}
                        </div>
                    @else
                        <div class="p-3 bg-surface-container-low rounded-xl text-xs text-on-surface-variant">
                            يعرض محتوى تلقائي مخصص للنوع: <strong>{{ $section->type_name }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl block mb-2 text-outline">view_compact</span>
                <p>لا توجد أقسام فوتر مضافة حالياً. اضغط "إضافة قسم جديد" للبدء.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Add Section Modal --}}
<div id="add-section-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h4 class="font-bold text-lg text-on-surface">إضافة قسم جديد بالفوتر</h4>
        <form action="{{ route('admin.footer.sections.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">عنوان القسم</label>
                <input type="text" name="title" required placeholder="مثال: روابط مهمة" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">نوع القسم</label>
                <select name="type" required class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                    <option value="links">روابط مخصصة</option>
                    <option value="categories">الأقسام الرئيسية تلقائياً</option>
                    <option value="contact">معلومات التواصل</option>
                    <option value="social">أيقونات التواصل</option>
                    <option value="store_info">نبذة عن المتجر</option>
                    <option value="custom_html">HTML مخصص</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">الترتيب</label>
                <input type="number" name="sort_order" value="0" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('add-section-modal').classList.add('hidden')" class="px-4 py-2 text-sm text-outline hover:bg-surface-container-low rounded-xl">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">حفظ</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Link Modal --}}
<div id="add-link-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h4 class="font-bold text-lg text-on-surface" id="link-modal-title">إضافة رابط للقسم</h4>
        <form id="add-link-form" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">عنوان الرابط</label>
                <input type="text" name="title" required placeholder="مثال: سياسة الخصوصية" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">الرابط (URL)</label>
                <input type="text" name="url" required placeholder="/privacy أو https://..." class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary" dir="ltr">
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">طريقة الفتح</label>
                <select name="target" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                    <option value="_self">في نفس النافذة (_self)</option>
                    <option value="_blank">في نافذة جديدة (_blank)</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('add-link-modal').classList.add('hidden')" class="px-4 py-2 text-sm text-outline hover:bg-surface-container-low rounded-xl">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">إضافة</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Social Modal --}}
<div id="add-social-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h4 class="font-bold text-lg text-on-surface">إضافة حساب تواصل اجتماعي</h4>
        <form action="{{ route('admin.footer.socials.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">المنصة</label>
                <select name="platform" required class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary">
                    @foreach(\App\Models\Content\FooterSocial::PLATFORMS as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface mb-1">رابط الحساب (URL)</label>
                <input type="url" name="url" required placeholder="https://facebook.com/yourpage" class="w-full p-2.5 border border-outline-variant rounded-xl text-sm outline-none focus:border-primary" dir="ltr">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('add-social-modal').classList.add('hidden')" class="px-4 py-2 text-sm text-outline hover:bg-surface-container-low rounded-xl">إلغاء</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90">حفظ</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddLinkModal(sectionId, title) {
        document.getElementById('link-modal-title').innerText = 'إضافة رابط إلى: ' + title;
        document.getElementById('add-link-form').action = '/ar/admin/footer/sections/' + sectionId + '/links';
        document.getElementById('add-link-modal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
