@extends('admin.layout')

@section('title', $page ? __t('admin.pages.edit_page') . ': ' . $page->title : __t('admin.pages.add_new'))

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.pages.index') }}" class="w-10 h-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_forward</span>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ $page ? __t('admin.pages.edit_page') : __t('admin.pages.add_new') }}</h1>
                @if($page)
                    <span class="bg-surface-container-low text-on-surface-variant font-mono font-bold text-xs px-2.5 py-0.5 rounded-lg">{{ $page->title }}</span>
                @endif
            </div>
            <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.pages.manage_pages') }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ $page ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
    @csrf
    @if($page) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Main Page Info Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-6 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">description</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.pages.title_field') }} والمحتوى</h2>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.title_field') }} <span class="text-error">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" required
                                   class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('title') border-error @enderror" placeholder="مثال: سياسة الخصوصية">
                            @error('title')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.slug') }} <span class="text-error">*</span></label>
                            <div class="relative">
                                <span class="absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant font-mono text-xs select-none">/page/</span>
                                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}" required
                                       class="w-full ps-16 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('slug') border-error @enderror" placeholder="privacy-policy">
                            </div>
                            @error('slug')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.intro') }}</label>
                        <textarea name="intro" rows="2"
                                  class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="مقدمة سريعة تظهر أعلى الصفحة">{{ old('intro', $page->intro ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.content') }}</label>
                        <textarea name="content" rows="12"
                                  class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="محتوى الصفحة بتنسيق نصي أو JSON">{{ old('content', $page->content ?? '') }}</textarea>
                        <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.pages.supports_json') }}</p>
                    </div>
                </div>
            </div>

            {{-- SEO Settings Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-6 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">travel_explore</span>
                    <h2 class="font-bold text-lg text-on-surface">إعدادات محركات البحث (SEO)</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.seo_title') }}</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}"
                               class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="{{ __t('admin.pages.seo_title_placeholder') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.seo_description') }}</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="وصف مخصص يظهر في نتائج بحث جوجل">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Settings --}}
        <div class="space-y-6">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-5 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">tune</span>
                    <h2 class="font-bold text-lg text-on-surface">خيارات الصفحة</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.icon') }}</label>
                        <input type="text" name="icon" value="{{ old('icon', $page->icon ?? '') }}"
                               class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="shield, info, help, policy...">
                        <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.pages.icon_help') }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.color') }}</label>
                        <select name="color" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="blue" {{ old('color', $page->color ?? '') === 'blue' ? 'selected' : '' }}>Blue (أزرق)</option>
                            <option value="green" {{ old('color', $page->color ?? '') === 'green' ? 'selected' : '' }}>Green (أخضر)</option>
                            <option value="purple" {{ old('color', $page->color ?? '') === 'purple' ? 'selected' : '' }}>Purple (بنفسجي)</option>
                            <option value="indigo" {{ old('color', $page->color ?? '') === 'indigo' ? 'selected' : '' }}>Indigo (نيلي)</option>
                            <option value="red" {{ old('color', $page->color ?? '') === 'red' ? 'selected' : '' }}>Red (أحمر)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.pages.sort_order') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0"
                               class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono font-bold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded-lg border-outline-variant text-primary focus:ring-primary/20">
                            <span class="text-sm font-bold text-on-surface">{{ __t('admin.pages.published') }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-outline-variant/30 flex flex-col gap-2.5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-lg">save</span>
                        <span>{{ $page ? __t('common.update') : __t('admin.pages.save') }}</span>
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-on-surface font-semibold text-sm hover:bg-surface-container transition-colors text-center">
                        {{ __t('common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection