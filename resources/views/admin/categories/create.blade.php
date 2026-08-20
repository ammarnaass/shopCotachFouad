@extends('admin.layout')

@section('title', __t('admin.categories.add_new'))

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.categories.index') }}" class="w-10 h-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_forward</span>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.categories.add_new') }}</h1>
            <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.categories.description') }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Info Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-6 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">category</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.categories.basic_info') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.categories.name') }} <span class="text-error">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-error @enderror" placeholder="مثال: بروتينات ومكملات">
                        @error('name')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.categories.icon') }}</label>
                        <input type="text" name="icon" value="{{ old('icon') }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('icon') border-error @enderror" placeholder="fitness_center أو shopping_bag">
                        <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.categories.icon_help') }}</p>
                        @error('icon')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.categories.parent') }}</label>
                        <select name="parent_id" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-medium text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('parent_id') border-error @enderror">
                            <option value="">{{ __t('admin.categories.none') }} (تصنيف رئيسي)</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.categories.parent_help') }}</p>
                        @error('parent_id')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.categories.description') }}</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('description') border-error @enderror" placeholder="وصف موجز للتصنيف لظهوره في نتائج البحث ومحركات البحث">{{ old('description') }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Settings --}}
        <div class="space-y-6">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-5 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">tune</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.categories.settings') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.categories.order') }}</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono font-bold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('order') border-error @enderror">
                        <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.categories.order_help') }}</p>
                        @error('order')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('common.status') }} <span class="text-error">*</span></label>
                        <select name="status" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('status') border-error @enderror">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __t('common.active') }}</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __t('common.inactive') }}</option>
                        </select>
                        @error('status')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-5 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">image</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.categories.image') }}</h2>
                </div>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-on-surface file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer border border-outline-variant/60 rounded-xl p-2 @error('image') border-error @enderror">
                <p class="text-xs text-on-surface-variant mt-2">{{ __t('admin.categories.image_help') }}</p>
                @error('image')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-2.5">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined text-lg">save</span>
                    <span>{{ __t('admin.categories.save') }}</span>
                </button>
                <a href="{{ route('admin.categories.index') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-on-surface font-semibold text-sm hover:bg-surface-container transition-colors text-center">
                    {{ __t('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

