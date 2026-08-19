@extends('admin.layout')

@section('title', __t('admin.slider.edit_slide'))

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.slider.index') }}" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition">
        <span class="material-symbols-outlined text-sm">arrow_forward</span>
        {{ __t('admin.slider.back_to_list') }}
    </a>
    <h1 class="text-3xl font-bold mt-2">{{ __t('admin.slider.edit_slide') }}</h1>
</div>

<form method="POST" action="{{ route('admin.slider.update', $slide) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 mb-6">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.badge') }}</label>
                <input type="text" name="badge" value="{{ old('badge', $slide->badge) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary" placeholder="{{ __t('admin.slider.badge_placeholder') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.title') }} <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $slide->title) }}" required class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary @error('title') border-red-500 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.subtitle') }}</label>
                <textarea name="subtitle" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">{{ old('subtitle', $slide->subtitle) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.description') }}</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">{{ old('description', $slide->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.btn_text') }}</label>
                <input type="text" name="btn_text" value="{{ old('btn_text', $slide->btn_text) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary" placeholder="{{ __t('nav.shop_now') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.button_target') }}</label>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="button_target" value="_same" {{ old('button_target', $slide->button_target ?? '_same') === '_same' ? 'checked' : '' }} class="w-4 h-4 text-primary">
                        <span class="text-sm">{{ __t('admin.slider.button_target_same') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="button_target" value="_blank" {{ old('button_target', $slide->button_target ?? '_same') === '_blank' ? 'checked' : '' }} class="w-4 h-4 text-primary">
                        <span class="text-sm">{{ __t('admin.slider.button_target_blank') }}</span>
                    </label>
                </div>
             </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.animation_effect') }}</label>
                <select name="animation_effect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                    @foreach(\App\Models\Content\Slide::ANIMATION_EFFECTS as $value => $label)
                        <option value="{{ $value }}" {{ old('animation_effect', $slide->animation_effect) === $value ? 'selected' : '' }}>{{ __t('admin.slider.animation_' . $value) }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.slider.animation_effect_help') }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.entrance_effect') }}</label>
                <select name="entrance_effect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                    @foreach(\App\Models\Content\Slide::ENTRANCE_EFFECTS as $value => $label)
                        <option value="{{ $value }}" {{ old('entrance_effect', $slide->entrance_effect) === $value ? 'selected' : '' }}>{{ __t('admin.slider.entrance_' . $value) }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.slider.entrance_effect_help') }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.link') }}</label>
                <input type="url" name="link" value="{{ old('link', $slide->link) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-mono text-sm" placeholder="https://..." dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.image') }} <span class="text-xs text-on-surface-variant">({{ __t('admin.slider.desktop') }})</span></label>

                @if($slide->image)
                    <div class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-lg p-3 mb-2 flex items-center gap-3">
                        <a href="{{ $slide->image_url }}" target="_blank">
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="h-16 w-28 object-cover rounded border">
                        </a>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-on-surface-variant truncate" dir="ltr">{{ $slide->image }}</p>
                            <p class="text-xs text-green-600 mt-0.5"><span class="material-symbols-outlined text-xs">check_circle</span> {{ __t('admin.customize.current_image') }}</p>
                        </div>
                    </div>
                @endif

                <input type="file" name="image_file" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full text-sm @error('image_file') border-red-500 @enderror">
                <p class="text-xs text-on-surface-variant mt-1">
                    <span class="material-symbols-outlined ml-1 text-xs">info</span>JPEG, PNG, WEBP — {{ __t('admin.customize.up_to_2mb') }}
                </p>
                <p class="text-xs mt-1.5 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium">
                        <span class="material-symbols-outlined text-sm">aspect_ratio</span>
                        {{ __t('admin.slider.image_recommended') }}
                    </span>
                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium">
                        <span class="material-symbols-outlined text-sm">auto_fix</span>
                        {{ __t('admin.slider.image_auto') }}
                    </span>
                </p>
                @error('image_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('admin.customize.or_url') }}</label>
                <input type="url" name="image" value="{{ old('image', $slide->image && preg_match('#^https?://#i', $slide->image) ? $slide->image : '') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-mono text-sm" placeholder="https://..." dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.mobile_image') }}</label>

                @if($slide->mobile_image)
                    <div class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-lg p-3 mb-2 flex items-center gap-3">
                        <a href="{{ $slide->mobile_image_url }}" target="_blank">
                            <img src="{{ $slide->mobile_image_url }}" alt="{{ $slide->title }}" class="h-16 w-20 object-cover rounded border">
                        </a>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-on-surface-variant truncate" dir="ltr">{{ $slide->mobile_image }}</p>
                            <p class="text-xs text-green-600 mt-0.5"><span class="material-symbols-outlined text-xs">check_circle</span> {{ __t('admin.slider.current_mobile_image') }}</p>
                        </div>
                    </div>
                @endif

                <input type="file" name="mobile_image_file" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full text-sm @error('mobile_image_file') border-red-500 @enderror">
                <p class="text-xs text-on-surface-variant mt-1">
                    <span class="material-symbols-outlined ml-1 text-xs">info</span>JPEG, PNG, WEBP — {{ __t('admin.customize.up_to_2mb') }}
                </p>
                <p class="text-xs mt-1.5 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium">
                        <span class="material-symbols-outlined text-sm">aspect_ratio</span>
                        {{ __t('admin.slider.mobile_image_recommended') }}
                    </span>
                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium">
                        <span class="material-symbols-outlined text-sm">auto_fix</span>
                        {{ __t('admin.slider.image_auto') }}
                    </span>
                </p>
                @error('mobile_image_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('admin.customize.or_url') }}</label>
                <input type="url" name="mobile_image" value="{{ old('mobile_image', $slide->mobile_image && preg_match('#^https?://#i', $slide->mobile_image) ? $slide->mobile_image : '') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary font-mono text-sm" placeholder="https://..." dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.sort_order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.starts_at') }}</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $slide->starts_at ? $slide->starts_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.slider.starts_at_help') }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __t('admin.slider.ends_at') }}</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $slide->ends_at ? $slide->ends_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.slider.ends_at_help') }}</p>
            </div>
            <div>
                <label class="flex items-center gap-3 p-3 border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low mt-6">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                    <span class="text-sm font-semibold">{{ __t('admin.slider.is_active') }}</span>
                </label>
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 flex items-center justify-between">
        <a href="{{ route('admin.slider.index') }}" class="text-on-surface-variant hover:text-primary transition text-sm">{{ __t('common.cancel') }}</a>
        <button type="submit" class="bg-primary hover:bg-primary text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition active:scale-95">
            <span class="material-symbols-outlined">save</span>
            {{ __t('admin.slider.save') }}
        </button>
    </div>
</form>
@endsection