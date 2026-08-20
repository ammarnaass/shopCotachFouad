@extends('admin.layout')

@section('title', $zone ? __t('admin.shipping.edit_zone') . ' ' . $zone->name : __t('admin.shipping.add_zone'))

@section('content')
@php
    use Illuminate\Support\Facades\Config;
    $countries = Config::get('ecommerce.countries', []);
    $allCompanies = \App\Models\Shipping\ShippingCompany::where('status', 'active')->orderBy('name')->get();
@endphp

<!-- Breadcrumb & Header -->
<div class="mb-stack-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <nav aria-label="Breadcrumb" class="flex text-outline font-label-md text-label-md mb-2">
            <ol class="flex items-center space-x-reverse space-x-2">
                <li><a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">{{ __t('admin.dashboard') }}</a></li>
                <li><span class="material-symbols-outlined text-[14px]">chevron_left</span></li>
                <li><a href="{{ route('admin.shipping.index', ['tab' => 'zones']) }}" class="hover:text-primary transition-colors">{{ __t('admin.shipping.page_title') }}</a></li>
                <li><span class="material-symbols-outlined text-[14px]">chevron_left</span></li>
                <li class="text-primary font-bold">{{ $zone ? __t('admin.shipping.edit_zone') : __t('admin.shipping.add_zone') }}</li>
            </ol>
        </nav>
        <h2 class="font-headline-md text-headline-md font-bold text-on-surface">{{ $zone ? __t('admin.shipping.edit_zone') : __t('admin.shipping.add_zone') }}</h2>
    </div>
    <a href="{{ route('admin.shipping.index', ['tab' => 'zones']) }}" class="flex items-center gap-2 px-6 py-2 border border-primary text-primary font-label-md rounded-lg hover:bg-primary/5 transition-all active:scale-95">
        <span class="material-symbols-outlined">arrow_back</span>
        {{ __t('admin.common.back') }}
    </a>
</div>

<!-- Form -->
<form method="POST" action="{{ $zone ? route('admin.shipping.zone.update', $zone) : route('admin.shipping.zone.store') }}" class="max-w-5xl mx-auto">
    @csrf
    @if($zone)@method('PUT')@endif

    <div class="bg-surface-container-lowest shadow-sm rounded-xl overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b border-outline-variant bg-surface-container-low/50">
            <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">public</span>
                {{ __t('admin.shipping.zone_data') }}
            </h3>
        </div>

        <!-- Card Body -->
        <div class="p-8 space-y-8">

            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-lg">
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.zone_name') }} <span class="text-error">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $zone->name ?? '') }}" required
                           placeholder="{{ __t('admin.shipping.zone_name_placeholder') }}"
                           class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg p-3 font-body-md focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all @error('name') border-error @enderror">
                    @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-[11px] text-outline">{{ __t('admin.shipping.zone_name_hint') }}</p>
                </div>
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.priority') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $zone->sort_order ?? 0) }}" min="0"
                           class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg p-3 font-body-md focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
                    <p class="text-[11px] text-outline">{{ __t('admin.shipping.priority_hint') }}</p>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.description') }}</label>
                    <textarea name="description" rows="3" placeholder="{{ __t('admin.shipping.zone_name_placeholder') }}..."
                              class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg p-3 font-body-md focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">{{ old('description', $zone->description ?? '') }}</textarea>
                </div>

                {{-- المنطقة الافتراضية --}}
                <div class="md:col-span-2">
                    <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all
                        {{ old('is_default', $zone->is_default ?? false) ? 'border-warning bg-warning/5' : 'border-outline-variant hover:bg-surface-container-high' }}">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1"
                               id="is_default_checkbox"
                               {{ old('is_default', $zone->is_default ?? false) ? 'checked' : '' }}
                               class="mt-0.5 w-5 h-5 rounded text-warning border-outline-variant focus:ring-warning transition-all shrink-0">
                        <div>
                            <span class="font-label-md text-label-md font-semibold text-on-surface block">
                                اجعلها المنطقة الافتراضية
                            </span>
                            <span class="text-[11px] text-outline block mt-0.5">
                                تُستخدم تلقائيًا عندما لا تطابق مدينة الزبون أي منطقة شحن أخرى معرّفة.
                                يمكن أن تكون منطقة واحدة فقط افتراضية بنفس الوقت.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <hr class="border-outline-variant/30">

            <!-- Company & Delivery Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-lg">
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.shipping_company') }}</label>
                    <select name="company_id" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg p-3 font-body-md focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
                        <option value="">— {{ __t('admin.shipping.no_company') }} —</option>
                        @foreach($allCompanies as $company)
                            <option value="{{ $company->id }}" {{ (string)old('company_id', $zone->company_id ?? '') === (string)$company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-outline">{{ __t('admin.shipping.no_company_hint') }}</p>
                </div>
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.delivery_type') }} <span class="text-error">*</span></label>
                    <select name="delivery_type" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg p-3 font-body-md focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
                        @php $dt = old('delivery_type', $zone->delivery_type ?? 'both'); @endphp
                        <option value="home" {{ $dt === 'home' ? 'selected' : '' }}>{{ __t('admin.shipping.home_only') }}</option>
                        <option value="office" {{ $dt === 'office' ? 'selected' : '' }}>{{ __t('admin.shipping.office_only') }}</option>
                        <option value="both" {{ $dt === 'both' ? 'selected' : '' }}>{{ __t('admin.shipping.both') }}</option>
                    </select>
                </div>
            </div>

            <hr class="border-outline-variant/30">

            <!-- Geographic Selection -->
            <div class="space-y-6">
                <!-- Countries -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-label-md text-label-md text-on-surface-variant block">{{ __t('admin.shipping.included_countries') }} <span class="text-error">*</span></label>
                        <span class="text-[11px] text-primary cursor-pointer hover:underline" onclick="selectAllCountries()">{{ __t('admin.shipping.select_all') }}</span>
                    </div>
                    @php
                        $selectedCountries = old('countries', $zone->countries ?? ['DZ']);
                        if (!is_array($selectedCountries)) $selectedCountries = [$selectedCountries];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant cursor-pointer transition-all hover:bg-surface-container-high {{ in_array('*', $selectedCountries) ? 'border-primary bg-primary/5' : '' }}">
                            <input type="checkbox" name="countries[]" value="*" {{ in_array('*', $selectedCountries) ? 'checked' : '' }} class="w-5 h-5 rounded text-primary border-outline-variant focus:ring-primary transition-all">
                            <span class="flex items-center gap-2 font-label-md text-label-md">
                                <span class="material-symbols-outlined text-primary text-lg">language</span>
                                {{ __t('admin.shipping.all_countries') }}
                            </span>
                        </label>
                        @foreach($countries as $code => $info)
                            @php $isCountryChecked = in_array($code, $selectedCountries) || in_array('*', $selectedCountries); @endphp
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant cursor-pointer transition-all hover:bg-surface-container-high country-card {{ $isCountryChecked ? 'border-primary bg-primary/5' : '' }}">
                                <input type="checkbox" name="countries[]" value="{{ $code }}" {{ $isCountryChecked ? 'checked' : '' }} class="w-5 h-5 rounded text-primary border-outline-variant focus:ring-primary transition-all country-toggle" data-country="{{ $code }}">
                                <span class="flex items-center gap-2 font-label-md text-label-md">
                                    <span class="text-xl">{{ $info['flag'] ?? '' }}</span>
                                    {{ $info['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Cities / States -->
                <div id="states-container" class="space-y-3">
                    <label class="font-label-md text-label-md text-on-surface-variant block">الولاية / المحافظة <span class="text-error">*</span></label>
                    @php
                        // تجميع كل المدن والمواقع المحددة مسبقًا بدقة
                        $assignedLocations = [];
                        if ($zone) {
                            if (is_array($zone->cities)) {
                                foreach ($zone->cities as $k => $v) {
                                    if (is_array($v)) {
                                        foreach ($v as $sub) $assignedLocations[] = (string)$sub;
                                    } else {
                                        $assignedLocations[] = (string)$v;
                                    }
                                }
                            }
                            if (is_array($zone->states)) {
                                foreach ($zone->states as $s) $assignedLocations[] = (string)$s;
                            }
                            if (is_array($zone->regions)) {
                                foreach ($zone->regions as $r) $assignedLocations[] = (string)$r;
                            }
                            if ($zone->locations) {
                                foreach ($zone->locations as $loc) {
                                    $assignedLocations[] = (string)$loc->value;
                                    if (str_contains($loc->value, ':')) {
                                        $parts = explode(':', $loc->value);
                                        $assignedLocations[] = (string)end($parts);
                                    }
                                }
                            }
                        }
                        $assignedLocations = array_unique(array_map('trim', $assignedLocations));
                        $assignedLocationsLower = array_map('mb_strtolower', $assignedLocations);
                    @endphp

                    @foreach($countries as $code => $info)
                        @if(in_array($code, $selectedCountries) || in_array('*', $selectedCountries))
                            <div class="country-states border border-outline-variant rounded-xl p-4 bg-surface-container-low/30 {{ in_array('*', $selectedCountries) && !in_array($code, $selectedCountries) ? 'hidden' : '' }}" data-country="{{ $code }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-label-md text-label-md font-bold text-on-surface">{{ $info['name'] }}</span>
                                        <span class="text-xs text-outline">({{ $code }})</span>
                                    </div>
                                    <button type="button" class="text-xs text-primary font-bold hover:underline" onclick="toggleAllCountryStates('{{ $code }}')">
                                        تحديد / إلغاء تحديد الكل
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-6 max-h-56 overflow-y-auto p-3 bg-surface-container-low rounded-xl border border-outline-variant/50" id="states-list-{{ $code }}">
                                    @php
                                        $oldForCountry = old("cities.$code");
                                        $isAllCitiesChecked = $oldForCountry !== null
                                            ? in_array('*', (array)$oldForCountry)
                                            : in_array('*', $assignedLocations);
                                    @endphp
                                    <label class="flex items-center gap-3 cursor-pointer group col-span-2 md:col-span-3 border-b border-outline-variant/30 pb-2 mb-1">
                                        <input type="checkbox" name="cities[{{ $code }}][]" value="*" {{ $isAllCitiesChecked ? 'checked' : '' }} class="w-5 h-5 rounded text-primary border-outline focus:ring-primary transition-all select-all-states">
                                        <span class="font-body-sm text-body-sm font-bold text-primary group-hover:text-primary">{{ __t('admin.shipping.all_cities') }} (كل ولايات الدولة)</span>
                                    </label>
                                    @foreach($info['states'] ?? [] as $stateCode => $stateName)
                                        @php
                                            $isChecked = false;
                                            if ($oldForCountry !== null) {
                                                $isChecked = in_array('*', (array)$oldForCountry)
                                                    || in_array((string)$stateName, (array)$oldForCountry)
                                                    || in_array((string)$stateCode, (array)$oldForCountry);
                                            } else {
                                                $isChecked = in_array('*', $assignedLocations)
                                                    || in_array((string)$stateCode, $assignedLocations)
                                                    || in_array((string)$stateName, $assignedLocations)
                                                    || in_array(mb_strtolower((string)$stateName), $assignedLocationsLower)
                                                    || in_array("{$code}:{$stateCode}", $assignedLocations);
                                            }
                                        @endphp
                                        <label class="flex items-center gap-3 cursor-pointer group state-item">
                                            <input type="checkbox" name="cities[{{ $code }}][]" value="{{ $stateCode }}" data-state-name="{{ $stateName }}" {{ $isChecked ? 'checked' : '' }} class="w-5 h-5 rounded text-primary border-outline focus:ring-primary transition-all state-checkbox">
                                            <span class="font-body-sm text-body-sm group-hover:text-primary {{ !$isChecked ? 'text-outline' : 'font-semibold text-on-surface' }}">{{ $stateCode }} - {{ $stateName }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="text-[11px] text-outline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">info</span>
                    {{ __t('admin.shipping.cities_hint') }}
                </p>
            </div>

            <hr class="border-outline-variant/30">

            <!-- Status Toggle -->
            <div class="flex items-center justify-between p-4 bg-tertiary-fixed/20 rounded-xl border border-tertiary-fixed-dim">
                    <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed-variant">
                        <span class="material-symbols-outlined">toggle_on</span>
                    </div>
                    <div>
                        <h4 class="font-label-md text-label-md font-bold text-on-surface">{{ __t('admin.shipping.zone_status') }}</h4>
                        <p class="text-[11px] text-outline">{{ __t('admin.shipping.zone_active_hint') }}</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="status" value="inactive">
                    <input type="checkbox" name="status" value="active" class="sr-only peer" {{ old('status', $zone->status ?? 'active') === 'active' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>

            @if($zone)
            <hr class="border-outline-variant/30">

            <!-- Zone Shipping Methods Preview & Management -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">local_shipping</span>
                            طرق الشحن التابعة لهذه المنطقة
                        </h3>
                        <p class="text-xs text-outline mt-0.5">يتم تحديث طرق الشحن تلقائيًا بناءً على الأسعار المدخلة أعلاه، ويمكنك أيضًا إضافة طرق مخصصة.</p>
                    </div>
                    <a href="{{ route('admin.shipping.method.create') }}?zone_id={{ $zone->id }}&return_to_zone={{ $zone->id }}" class="px-4 py-2 bg-primary-fixed text-on-primary-fixed-variant rounded-lg text-xs font-bold hover:bg-primary hover:text-white transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        إضافة طريقة شحن مخصصة
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @forelse($zone->methods as $m)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/60 hover:border-primary transition-all gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-on-surface text-sm">{{ $m->name }}</span>
                                        <span class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 rounded">{{ $m->getTypeLabel() }}</span>
                                    </div>
                                    <p class="text-xs text-outline mt-0.5">
                                        وقت التوصيل: {{ $m->estimated_days ?: 'غير محدد' }}
                                        @if($m->free_shipping_threshold)
                                            • مجاني فوق {{ number_format($m->free_shipping_threshold, 0) }} د.ج
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <span class="font-bold text-primary text-base">{{ number_format($m->base_cost, 2) }} {{ currentCurrencySymbol() }}</span>
                                
                                <!-- زر تفعيل / إخفاء طريقة الشحن -->
                                <form action="{{ route('admin.shipping.method.toggle', $m) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-2.5 py-1 rounded-full font-bold transition-all hover:opacity-80 flex items-center gap-1.5 {{ $m->status ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-200 text-gray-600 border border-gray-300' }}" title="{{ $m->status ? 'انقر لإخفاء وتعطيل هذه الطريقة' : 'انقر لتفعيل وإظهار هذه الطريقة' }}">
                                        <span class="w-2 h-2 rounded-full {{ $m->status ? 'bg-emerald-600' : 'bg-gray-500' }}"></span>
                                        {{ $m->status ? 'نشط (ظاهر)' : 'مخفي (معطل)' }}
                                    </button>
                                </form>

                                <!-- زر تعديل طريقة الشحن -->
                                <a href="{{ route('admin.shipping.method.edit', $m) }}?return_to_zone={{ $zone->id }}" class="p-1.5 text-primary hover:bg-primary-fixed rounded-lg transition-colors" title="تعديل طريقة الشحن">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>

                                <!-- زر حذف طريقة الشحن -->
                                <form action="{{ route('admin.shipping.method.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف طريقة الشحن هذه؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-error hover:bg-error-container/30 rounded-lg transition-colors" title="حذف طريقة الشحن">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-on-surface-variant text-center py-6 bg-surface rounded-xl border border-dashed border-outline-variant">لا توجد طرق شحن مخصصة بعد لهذه المنطقة. اضغط زر «إضافة طريقة شحن» أعلاه لإضافة طريقة شحن جديدة.</p>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6 border-t border-outline-variant/30">
                <button type="submit" class="w-full sm:w-auto px-10 py-3 bg-primary text-white font-title-lg rounded-xl shadow-md hover:shadow-lg hover:bg-primary/90 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    {{ $zone ? __t('admin.shipping.update_zone') : __t('admin.shipping.save_zone') }}
                </button>
                @if($zone && !$zone->isEverywhere())
                <a href="{{ route('admin.shipping.method.create') }}?zone_id={{ $zone->id }}&return_to_zone={{ $zone->id }}" class="w-full sm:w-auto px-8 py-3 border-2 border-primary-fixed-dim text-primary font-title-lg rounded-xl hover:bg-primary-fixed/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">add_road</span>
                    {{ __t('admin.shipping.add_shipping_methods') }}
                </a>
                @endif
                <div class="sm:mr-auto flex items-center gap-3">
                    @if($zone && !$zone->isEverywhere())
                    <button type="button" onclick="if(confirm('{{ __t('admin.shipping.confirm_delete_zone') }}')) document.getElementById('delete-zone-form').submit()" class="text-error font-label-md hover:underline px-4 py-2">
                        {{ __t('admin.shipping.delete_zone') }}
                    </button>
                    @endif
                    <a href="{{ route('admin.shipping.index', ['tab' => 'zones']) }}" class="px-6 py-2 border border-outline-variant text-on-surface-variant font-label-md rounded-lg hover:bg-surface-container-high transition-all">
                        {{ __t('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Cards -->
    <div class="mt-gutter grid grid-cols-1 md:grid-cols-3 gap-stack-md">
        <div class="p-4 bg-surface-container-high rounded-xl flex items-start gap-3">
            <span class="material-symbols-outlined text-primary">info</span>
            <div class="text-on-surface-variant">
                <p class="font-label-md text-label-md font-bold mb-1">{{ __t('admin.shipping.what_are_zones') }}</p>
                <p class="text-[11px] leading-relaxed">{{ __t('admin.shipping.zones_desc') }}</p>
            </div>
        </div>
        <div class="p-4 bg-surface-container-high rounded-xl flex items-start gap-3">
            <span class="material-symbols-outlined text-primary">psychology</span>
            <div class="text-on-surface-variant">
                <p class="font-label-md text-label-md font-bold mb-1">{{ __t('admin.shipping.how_priority_works') }}</p>
                <p class="text-[11px] leading-relaxed">{{ __t('admin.shipping.priority_desc') }}</p>
            </div>
        </div>
        <div class="p-4 bg-surface-container-high rounded-xl flex items-start gap-3">
            <span class="material-symbols-outlined text-primary">security</span>
            <div class="text-on-surface-variant">
                <p class="font-label-md text-label-md font-bold mb-1">{{ __t('admin.shipping.data_privacy') }}</p>
                <p class="text-[11px] leading-relaxed">{{ __t('admin.shipping.data_privacy_desc') }}</p>
            </div>
        </div>
    </div>
</form>

@if($zone)
<form method="POST" action="{{ route('admin.shipping.zone.destroy', $zone) }}" id="delete-zone-form" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@push('scripts')
<script>
function selectAllCountries() {
    const toggles = document.querySelectorAll('.country-toggle');
    const allChecked = Array.from(toggles).every(t => t.checked);
    toggles.forEach(t => {
        t.checked = !allChecked;
        t.dispatchEvent(new Event('change'));
    });
}

function toggleAllCountryStates(countryCode) {
    const container = document.getElementById('states-list-' + countryCode);
    if (!container) return;
    const checkboxes = container.querySelectorAll('.state-checkbox');
    const allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => {
        c.checked = !allChecked;
        c.dispatchEvent(new Event('change'));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const countryToggles = document.querySelectorAll('.country-toggle');
    const allToggle = document.querySelector('input[name="countries[]"][value="*"]');

    function syncStatesVisibility() {
        const selected = Array.from(countryToggles).filter(c => c.checked).map(c => c.dataset.country);
        const showAll = allToggle && allToggle.checked;

        document.querySelectorAll('.country-states').forEach(section => {
            const code = section.dataset.country;
            const shouldShow = showAll || selected.includes(code);
            section.classList.toggle('hidden', !shouldShow);
        });
    }

    countryToggles.forEach(t => t.addEventListener('change', syncStatesVisibility));
    if (allToggle) allToggle.addEventListener('change', syncStatesVisibility);

    document.querySelectorAll('.country-card input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const parent = this.closest('.country-card');
            if (parent) {
                if (this.checked) {
                    parent.classList.add('border-primary', 'bg-primary/5');
                    parent.classList.remove('border-outline-variant');
                } else {
                    parent.classList.remove('border-primary', 'bg-primary/5');
                    parent.classList.add('border-outline-variant');
                }
            }
        });
    });

    document.querySelectorAll('.state-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const label = this.closest('label');
            const span = label ? label.querySelector('span') : null;
            if (span) {
                if (this.checked) {
                    span.classList.add('font-semibold', 'text-on-surface');
                    span.classList.remove('text-outline');
                } else {
                    span.classList.remove('font-semibold', 'text-on-surface');
                    span.classList.add('text-outline');
                }
            }
        });
    });
});
</script>
@endpush
