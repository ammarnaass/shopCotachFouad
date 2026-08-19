@extends('frontend.layout')

@section('title', $product->seo_title ?? $product->name . ' - ' . site('store_name'))
@section('description', $product->seo_description ?? $product->short_description ?? Str::limit(strip_tags($product->description), 160))
@section('og_type', 'product')
@section('canonical', route('shop.show', ['slug' => $product->slug]))

@php
    use Illuminate\Support\Str;
    $ibSettings = \App\Models\InstantBuy\InstantBuySetting::firstOrCreate([], []);
    $countries = config('ecommerce.countries', []);
    $defaultCountry = old('country_code', auth()->user()->country_code ?? session('selected_country', config('ecommerce.store.default_country', 'DZ')));
    $defaultState = old('state_code', auth()->user()->defaultAddress?->state_code ?? '');
    $countrySymbol = $countries[$defaultCountry]['currency_symbol'] ?? config('ecommerce.store.currency_symbol', __t('common.currency'));
    $hasOptions = $product->options->count() > 0;
    $hasCustomFields = $product->customFields->count() > 0;
    $mainImage = $product->images->first();
    $imageList = $product->images->map(fn($i) => asset('storage/' . $i->image))->values();
    $authUserData = auth()->user() ? auth()->user()->only(['name', 'email', 'phone', 'country_code', 'state_code']) : [];
    $ibS = fn($key) => $ibSettings->$key ?? null;

    // JSON-LD Product schema (PRD §38)
    $schema = [
        '@context' => 'https://schema.org/',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $imageList->all(),
        'description' => $product->short_description ?? Str::limit(strip_tags((string) $product->description), 160),
        'sku' => $product->sku,
        'category' => $product->category?->name,
        'brand' => ['@type' => 'Brand', 'name' => site('store_name')],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('shop.show', ['slug' => $product->slug]),
            'priceCurrency' => config('ecommerce.countries.' . $defaultCountry . '.currency', 'DZD'),
            'price' => (float) $product->final_price,
            'availability' => $product->stock > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        ],
    ];
    if ($product->reviews_count ?? 0) {
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) ($product->reviews_avg_rating ?? 0),
            'reviewCount' => (int) $product->reviews_count,
        ];
    }
@endphp

@push('head')
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@push('styles')
<style>
    .product-detail input[type="text"],
    .product-detail input[type="email"],
    .product-detail input[type="tel"],
    .product-detail input[type="number"],
    .product-detail select,
    .product-detail textarea {
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .product-detail input:focus,
    .product-detail select:focus,
    .product-detail textarea:focus {
        border-color: var(--color-primary, #3B82F6);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    .product-detail .summary-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
    }
    [x-cloak] { display: none !important; }
    .thumb-active { border-color: rgb(59 130 246) !important; }
    .price-pulse { animation: pricePulse 0.3s ease; }
    @keyframes pricePulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 product-detail text-on-surface transition-colors"
     x-data="instantBuyForm"
      x-init="setup(@json([
          'id' => $product->id,
          'name' => $product->name,
          'basePrice' => $product->price,
          'finalPrice' => $product->final_price,
          'salePrice' => $product->sale_price,
          'images' => $imageList,
          'stock' => $product->stock,
          'sku' => $product->sku,
          'weight' => $product->weight ?? 0,
          'countries' => $countries,
          'defaultCountry' => $defaultCountry,
          'defaultState' => $defaultState,
          'defaultSymbol' => $countrySymbol,
          'authUser' => $authUserData,
          'conversionRate' => conversionRate(),
          'ibEnabled' => (bool) $ibS('is_enabled'),
          'selectedOptions' => $hasOptions ? $product->options->mapWithKeys(fn($o) => [$o->id => null])->toArray() : [],
          'optionsAdjustments' => $hasOptions ? $product->options->reduce(function ($carry, $o) { foreach ($o->values as $v) { $carry[(int) $v->id] = (float) $v->price_adjustment; } return $carry; }, []) : [],
          'customFieldPrice' => (float) ($product->customFields->whereIn('type', ['text', 'textarea'])->first()?->price_effect ?? 0),
          'categoryName' => $product->category?->name,
          'categorySlug' => $product->category?->slug,
          'storeCountry' => config('ecommerce.store.default_country', 'DZ'),
          'validationRules' => collect([
              ['field' => 'countryCode', 'enabled' => $ibS('field_country_enabled'), 'required' => $ibS('field_country_required')],
              ['field' => 'city', 'enabled' => $ibS('field_city_enabled'), 'required' => $ibS('field_city_required')],
              ['field' => 'form.phone', 'enabled' => $ibS('field_phone_enabled'), 'required' => $ibS('field_phone_required')],
              ['field' => 'form.address', 'enabled' => $ibS('field_address_enabled'), 'required' => $ibS('field_address_required')],
              ['field' => 'form.first_name', 'enabled' => $ibS('field_first_name_enabled'), 'required' => $ibS('field_first_name_required')],
              ['field' => 'form.last_name', 'enabled' => $ibS('field_last_name_enabled'), 'required' => $ibS('field_last_name_required')],
              ['field' => 'form.email', 'enabled' => $ibS('field_email_enabled'), 'required' => $ibS('field_email_required')],
              ['field' => 'stateCode', 'enabled' => $ibS('field_state_enabled'), 'required' => $ibS('field_state_required')],
              ['field' => 'form.district', 'enabled' => $ibS('field_district_enabled'), 'required' => $ibS('field_district_required')],
              ['field' => 'form.zip', 'enabled' => $ibS('field_zip_enabled'), 'required' => $ibS('field_zip_required')],
          ])->where('enabled', true)->where('required', true)->values()->map(fn($r) => ['field' => $r['field']])->values()->toArray(),
          'routes' => [
              'calculateNew' => route('instant-buy.calculate'),
              'calculate' => route('instant.calculate'),
              'shippingOptionsNew' => route('instant-buy.shipping-options'),
              'shippingOptions' => route('instant.shipping-options'),
              'couponNew' => route('instant-buy.coupon'),
              'coupon' => route('instant.coupon'),
          ],
          't' => [
              'fillRequired' => __t('shop.show.fill_required'),
              'couponInvalid' => __t('shop.show.coupon_invalid'),
              'couponError' => __t('shop.show.coupon_error'),
              'orderError' => __t('shop.show.order_error'),
              'generalError' => __t('shop.show.general_error'),
              'submitError' => __t('shop.show.submit_error'),
          ],
      ]))"
      :style='ibEnabled && {{ $ibS('is_enabled') ? 'true' : 'false' }} ? { backgroundColor: "{{ $ibS('form_bg_color') }}", border: "{{ $ibS('form_border_width') }}px solid {{ $ibS('form_border_color') }}", borderRadius: "{{ $ibS('form_border_radius') }}px", boxShadow: "{{ $ibS('form_shadow') }}" } : {}'>


    {{-- ============ BREADCRUMB ============ --}}
    <div class="bg-white border-b border-gray-200">
        <div class="container-app py-3">
            <nav class="flex items-center gap-2 text-sm text-gray-500 flex-wrap">
                <a href="{{ route('home') }}" class="hover:text-blue-600 flex items-center gap-1">
                    <span class="material-symbols-outlined ms-1">home</span>{{ __t('nav.home') }}
                </a>
                <span class="material-symbols-outlined text-xs">chevron_right</span>
                <a href="{{ route('shop.index') }}" class="hover:text-blue-600">{{ __t('nav.products') }}</a>
                @if($product->category)
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('shop.category', ['slug' => $product->category->slug]) }}" class="hover:text-blue-600">
                        {{ $product->category->name }}
                    </a>
                @endif
                <span class="material-symbols-outlined text-xs">chevron_right</span>
                <span class="text-gray-700 font-semibold truncate max-w-[200px]">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <div class="container-app py-6 md:py-8">
        <form method="POST" :action="ibEnabled ? '{{ route('instant-buy.submit') }}' : '{{ route('instant.submit') }}'"
              @submit.prevent="submitForm($event)" id="instant-buy-form" class="grid lg:grid-cols-3 gap-6">
            @csrf
            <input type="hidden" name="product_id" :value="product.id">
            <input type="hidden" name="weight" :value="weight">

            {{-- ============ LEFT COLUMN: PRODUCT + OPTIONS ============ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Product card --}}
                <div class="bg-white border border-outline-variant rounded-2xl shadow-sm overflow-hidden">
                    <div class="grid md:grid-cols-2 gap-0">
                        {{-- Images --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-6">
                            <div class="aspect-square bg-white rounded-xl overflow-hidden mb-3 relative border border-gray-200">
                                <template x-for="(img, i) in images" :key="i">
                                    <img :src="img" :alt="product.name"
                                         x-show="activeImage === i"
                                         x-transition.opacity.duration.300ms
                                         class="w-full h-full object-cover absolute inset-0"
                                         loading="lazy">
                                </template>
                                <template x-if="images.length === 0">
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <span class="material-symbols-outlined text-6xl">image</span>
                                    </div>
                                </template>
                                <template x-if="product.discountPercent > 0">
                                    <span class="absolute top-3 left-3 bg-gradient-to-l from-red-500 to-rose-600 text-white px-3 py-1.5 rounded-full text-sm font-extrabold shadow-lg z-10">
                                        -<span x-text="product.discountPercent"></span>%
                                    </span>
                                </template>
                            </div>
                            <template x-if="images.length > 1">
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="(img, i) in images" :key="i">
                                        <button type="button" @click="activeImage = i"
                                                :class="activeImage === i ? 'thumb-active ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-400'"
                                                class="aspect-square rounded-lg overflow-hidden border-2 transition">
                                            <img :src="img" :alt="product.name" class="w-full h-full object-cover" loading="lazy">
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Info --}}
                        <div class="p-5 md:p-6 flex flex-col">
                            <template x-if="product.categoryName">
                                <a :href="'/category/' + product.categorySlug" class="text-xs text-blue-600 font-bold uppercase tracking-wide mb-2 hover:underline">
                                    <span class="material-symbols-outlined ms-1">local_offer</span><span x-text="product.categoryName"></span>
                                </a>
                            </template>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-3">{{ $product->name }}</h1>

                            {{-- Rating --}}
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex text-yellow-400 text-sm gap-0.5">
                                    <template x-for="i in 5" :key="i">
                                        <span class="material-symbols-outlined text-sm">star</span>
                                    </template>
                                </div>
                                <span class="text-xs text-gray-500">({{ __t('product.reviews_count', ['count' => $product->reviews->count() ?? 0]) }})</span>
                            </div>

                            {{-- Price (live) --}}
                            <div class="flex items-baseline gap-3 mb-4 flex-wrap">
                                <span class="text-3xl font-extrabold text-blue-600 price-pulse" :key="grandTotal">
                                    <span x-text="formatMoney(displayPrice)"></span>
                                    <span x-text="currencySymbol"></span>
                                </span>
                                <template x-if="product.salePrice && product.salePrice > 0 && product.salePrice < product.basePrice">
                                    <span class="text-lg text-gray-400 line-through" x-text="formatMoney(product.basePrice) + ' ' + currencySymbol"></span>
                                </template>
                                <template x-if="product.discountPercent > 0">
                                    <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full">
                                        -<span x-text="product.discountPercent"></span>%
                                    </span>
                                </template>
                            </div>

                            <template x-if="product.shortDescription">
                                <p class="text-gray-600 text-sm leading-relaxed mb-4" x-text="product.shortDescription"></p>
                            </template>

                            {{-- Stock --}}
                            <div class="flex items-center gap-2 mb-4 text-sm">
                                <span class="material-symbols-outlined text-green-500">check_circle</span>
                                <span class="text-gray-700">{{ __t('shop.show.in_stock') }} (<span x-text="product.stock"></span> {{ __t('shop.show.pieces') }})</span>
                            </div>

                            {{-- Quantity --}}
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-sm font-semibold text-gray-700">{{ __t('product.quantity') }}:</span>
                                <div class="flex items-center border-2 border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button" @click="quantity = Math.max(1, quantity - 1); recalculate()"
                                            class="w-10 h-10 hover:bg-gray-100 transition disabled:opacity-30"
                                            :disabled="quantity <= 1">−</button>
                                    <input type="number" name="quantity" x-model.number="quantity" min="1" :max="product.stock"
                                           @input="quantity = Math.max(1, Math.min(product.stock, quantity)); recalculate()"
                                           class="w-14 h-10 text-center font-bold border-0 focus:outline-none">
                                    <button type="button" @click="quantity = Math.min(product.stock, quantity + 1); recalculate()"
                                            class="w-10 h-10 hover:bg-gray-100 transition disabled:opacity-30"
                                            :disabled="quantity >= product.stock">+</button>
                                </div>
                            </div>

                            <template x-if="product.sku">
                                <div class="text-xs text-gray-500 mt-auto pt-3 border-t">
                                    <span class="font-semibold">SKU:</span> <span x-text="product.sku"></span>
                                    <span class="mx-2">•</span>
                                    <span class="font-semibold">{{ __t('shop.show.weight') }}:</span> <span x-text="product.weight + ' {{ __t('shop.show.kg') }}'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                @if($hasOptions)
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">tune</span>
                            {{ __t('product.specifications') }}
                        </h2>
                        @foreach($product->options as $option)
                            <div class="mb-4 last:mb-0">
                                <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    {{ $option->name }}
                                    <span class="text-xs text-gray-500 font-normal"
                                          x-show="selectedOptions[{{ $option->id }}] && getOptionAdjustment({{ $option->id }}) !== 0"
                                          x-transition>
                                        (<span x-text="(getOptionAdjustment({{ $option->id }}) > 0 ? '+' : '') + formatMoney(getOptionAdjustment({{ $option->id }}))"></span>)
                                    </span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($option->values as $value)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="options[{{ $option->id }}]" value="{{ $value->id }}"
                                                   @change="selectedOptions[{{ $option->id }}] = $event.target.value; recalculate()"
                                                   :checked="String(selectedOptions[{{ $option->id }}]) === '{{ $value->id }}'"
                                                   class="peer sr-only">
                                            <span class="inline-block px-4 py-2 border-2 border-gray-200 rounded-lg text-sm font-semibold transition
                                                       peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                                       hover:border-gray-400">
                                                {{ $value->value }}
                                                @if($value->price_adjustment > 0)
                                                    <span class="text-xs text-gray-500">(+{{ number_format(convertPrice($value->price_adjustment), 0) }})</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Custom Fields --}}
                @if($hasCustomFields)
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">edit_note</span>
                            {{ __t('instant.custom_fields') }}
                        </h2>
                        @foreach($product->customFields as $field)
                            <div class="mb-4 last:mb-0">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ $field->name }}
                                    @if($field->required)<span class="text-red-500">*</span>@endif
                                    <span class="text-xs text-gray-500 font-normal"
                                          x-show="{{ (float) $field->price_effect }} > 0">
                                        (+<span x-text="formatMoney({{ (float) $field->price_effect }})"></span>)
                                    </span>
                                </label>
                                @if($field->type === 'text')
                                    <input type="text" name="custom_text" x-model="customText" @input="recalculate()"
                                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg"
                                           placeholder="{{ $field->placeholder ?? __t('shop.show.custom_field_placeholder') . ' ' . $field->name }}">
                                @elseif($field->type === 'textarea')
                                    <textarea name="custom_text" x-model="customText" @input="recalculate()" rows="3"
                                              class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg"
                                              placeholder="{{ $field->placeholder ?? '' }}"></textarea>
                                @elseif($field->type === 'file')
                                    <input type="file" name="custom_file" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg text-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Description Tabs --}}
                <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5" x-data="{ tab: 'description' }">
                    <div class="flex border-b mb-4">
                        <button type="button" @click="tab = 'description'"
                                :class="tab === 'description' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                class="px-4 py-2 font-semibold border-b-2 transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">notes</span> {{ __t('product.description') }}
                        </button>
                        <button type="button" @click="tab = 'reviews'"
                                :class="tab === 'reviews' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                class="px-4 py-2 font-semibold border-b-2 transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">star</span> {{ __t('product.reviews') }} ({{ $product->reviews->count() ?? 0 }})
                        </button>
                        <button type="button" @click="tab = 'shipping'"
                                :class="tab === 'shipping' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                class="px-4 py-2 font-semibold border-b-2 transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">local_shipping</span> {{ __t('shop.show.tab_shipping') }}
                        </button>
                    </div>
                    <div x-show="tab === 'description'" class="prose prose-sm max-w-none text-gray-700 leading-loose">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                    <div x-show="tab === 'reviews'" x-cloak>
                        <p class="text-gray-500 text-center py-8">{{ __t('product.no_reviews') }}. {{ __t('product.be_first_review') }}!</p>
                    </div>
                    <div x-show="tab === 'shipping'" x-cloak>
                        <div class="space-y-3 text-sm text-gray-700">
                            <p class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-blue-600 mt-1">local_shipping</span>
                                <span>{{ __t('shop.show.shipping_desc') }}</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-green-600 mt-1">shield</span>
                                <span>{{ __t('shop.show.cod_desc') }}</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-amber-600 mt-1">schedule</span>
                                <span>{{ __t('shop.show.shipping_times') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ RIGHT COLUMN: ORDER FORM (sticky) ============ --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-4">

                    {{-- ===== Customer Info ===== --}}
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
                            <span class="material-symbols-outlined text-blue-600">account_circle</span>
                            {{ __t('instant.customer_info') }}
                        </h2>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                @if($ibS('field_first_name_enabled'))
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_first_name_label') ?? __t('instant.first_name') }} {{ $ibS('field_first_name_required') ? '*' : '' }}</label>
                                    <input type="text" name="first_name" {{ $ibS('field_first_name_required') ? 'required' : '' }} x-model="form.first_name"
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                           placeholder="{{ $ibS('field_first_name_placeholder') ?? __t('instant.first_name_placeholder') }}">
                                </div>
                                @endif
                                @if($ibS('field_last_name_enabled'))
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_last_name_label') ?? __t('instant.last_name') }} {{ $ibS('field_last_name_required') ? '*' : '' }}</label>
                                    <input type="text" name="last_name" {{ $ibS('field_last_name_required') ? 'required' : '' }} x-model="form.last_name"
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                           placeholder="{{ $ibS('field_last_name_placeholder') ?? __t('instant.last_name_placeholder') }}">
                                </div>
                                @endif
                            </div>
                            @if($ibS('field_email_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_email_label') ?? __t('common.email') }} {{ $ibS('field_email_required') ? '*' : '(' . __t('common.optional') . ')' }}</label>
                                <input type="email" name="email" x-model="form.email" {{ $ibS('field_email_required') ? 'required' : '' }}
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                       placeholder="{{ $ibS('field_email_placeholder') ?? 'example@mail.com' }}">
                            </div>
                            @endif
                            @if($ibS('field_phone_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_phone_label') ?? __t('instant.phone') }} {{ $ibS('field_phone_required') ? '*' : '' }}</label>
                                <div class="flex gap-1" dir="ltr">
                                    <input type="text" :value="dialCode" readonly
                                           class="w-16 px-2 py-2 border-2 border-gray-200 rounded-lg bg-gray-50 text-center font-semibold text-xs">
                                    <input type="tel" name="phone" {{ $ibS('field_phone_required') ? 'required' : '' }} x-model="form.phone"
                                           class="flex-1 px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                           placeholder="{{ $ibS('field_phone_placeholder') ?? '5XXXXXXXX' }}">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== Shipping Address ===== --}}
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-4 flex items-center gap-2 text-gray-800">
                            <span class="material-symbols-outlined text-blue-600">local_shipping</span>
                            {{ __t('instant.shipping_data') }}
                        </h2>
                        <div class="space-y-3">
                            @if($ibS('field_country_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_country_label') ?? __t('common.country') }} {{ $ibS('field_country_required') ? '*' : '' }}</label>
                                <select name="country_code" {{ $ibS('field_country_required') ? 'required' : '' }} x-model="countryCode" @change="onCountryChange()"
                                        class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm bg-white">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}">{{ $info['flag'] ?? '' }} {{ $info['name'] }} - {{ $info['name_en'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            @if($ibS('field_state_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_state_label') ?? __t('common.state') }} {{ $ibS('field_state_required') ? '*' : '' }}</label>
                                <select name="state_code" x-model="stateCode" @change="onStateChange()" {{ $ibS('field_state_required') ? 'required' : '' }}
                                        class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm bg-white">
                                    <option value="">— {{ __t('common.select') }} —</option>
                                    <template x-for="(name, code) in currentStates" :key="code">
                                        <option :value="code" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                            @endif
                            @if($ibS('field_city_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_city_label') ?? __t('instant.city') }} {{ $ibS('field_city_required') ? '*' : '' }}</label>
                                <input type="text" name="city" {{ $ibS('field_city_required') ? 'required' : '' }} x-model="city" @input.debounce.400ms="if(city&&countryCode)this.fetchShippingOptions();this.recalculate()"
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                       placeholder="{{ $ibS('field_city_placeholder') ?? __t('instant.city_placeholder') }}">
                            </div>
                            @endif
                            @if($ibS('field_district_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_district_label') ?? __t('instant.district') }} {{ $ibS('field_district_required') ? '*' : '' }}</label>
                                <input type="text" name="district" x-model="form.district" {{ $ibS('field_district_required') ? 'required' : '' }}
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                       placeholder="{{ $ibS('field_district_placeholder') ?? __t('instant.district_placeholder') }}">
                            </div>
                            @endif
                            @if($ibS('field_address_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_address_label') ?? __t('instant.address') }} {{ $ibS('field_address_required') ? '*' : '' }}</label>
                                <textarea name="address" {{ $ibS('field_address_required') ? 'required' : '' }} x-model="form.address" rows="2"
                                          class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                          placeholder="{{ $ibS('field_address_placeholder') ?? __t('instant.address_placeholder') }}"></textarea>
                            </div>
                            @endif
                            @if($ibS('field_zip_enabled'))
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $ibS('field_zip_label') ?? __t('instant.zip') }} {{ $ibS('field_zip_required') ? '*' : '' }}</label>
                                <input type="text" name="zip" x-model="form.zip" {{ $ibS('field_zip_required') ? 'required' : '' }}
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                       placeholder="{{ $ibS('field_zip_placeholder') ?? '11111' }}">
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== Shipping Method (dynamic from admin settings) ===== --}}
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i class="fas fa-shipping-fast text-blue-600"></i>
                            {{ __t('instant.shipping_method') }}
                        </h2>
                        <template x-if="shippingOptions.length === 0">
                            <div class="text-center py-4 text-sm text-gray-500">
                                <i class="fas fa-truck text-gray-300 text-2xl mb-2"></i>
                                <p>{{ __t('instant.select_city_shipping') }}</p>
                            </div>
                        </template>
                        <template x-if="shippingOptions.length > 0">
                            <div class="space-y-2">
                                <template x-for="(opt, idx) in shippingOptions" :key="idx">
                                    <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition"
                                           :class="selectedShippingOption?.type === opt.type && selectedShippingOption?.company_id === opt.company_id ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-400'">
                                        <input type="radio" name="shipping_method" :value="opt.type"
                                               x-model="shippingMethod"
                                               @change="selectShippingOption(opt)"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-sm" x-text="opt.label"></div>
                                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                                <span x-text="opt.company_name"></span>
                                                <template x-if="opt.estimated_days">
                                                    <span>• 🕐 <span x-text="opt.estimated_days"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold whitespace-nowrap"
                                              :class="opt.is_free ? 'text-green-600' : 'text-gray-700'"
                                              x-text="opt.is_free ? '{{ __t("common.free") }}' : formatMoney(opt.cost) + ' ' + currencySymbol">
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </template>
                        <template x-if="fixedCompany">
                            <p class="text-xs text-blue-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-info-circle"></i>
                                <span>{{ __t('instant.ships_via') }} <span x-text="fixedCompany.name"></span></span>
                            </p>
                        </template>
                    </div>

                    {{-- ===== Payment Method ===== --}}
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i class="fas fa-credit-card text-blue-600"></i>
                            {{ __t('instant.payment_method') }}
                        </h2>
                        <div class="space-y-2">
                            @if($ibS('show_bank_transfer'))
                            <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition"
                                   :class="paymentMethod === 'cod' ? 'border-green-600 bg-green-50' : 'border-gray-200 hover:border-gray-400'">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" @change="recalculate()"
                                       class="w-4 h-4 text-green-600">
                                <div class="flex-1">
                                    <div class="font-semibold text-sm">{{ __t('instant.cod') }}</div>
                                    <div class="text-xs text-gray-500">{{ __t('instant.cod_desc') }}</div>
                                </div>
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                            </label>
                            <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition"
                                   :class="paymentMethod === 'bank' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-400'">
                                <input type="radio" name="payment_method" value="bank" x-model="paymentMethod" @change="recalculate()"
                                       class="w-4 h-4 text-blue-600">
                                <div class="flex-1">
                                    <div class="font-semibold text-sm">{{ __t('instant.bank_transfer') }}</div>
                                    <div class="text-xs text-gray-500">{{ __t('instant.bank_transfer_desc') }}</div>
                                </div>
                                <i class="fas fa-university text-blue-600"></i>
                            </label>
                            @else
                            <input type="hidden" name="payment_method" value="cod">
                            <div class="flex items-center gap-3 p-3 border-2 border-green-600 bg-green-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-semibold text-sm">{{ __t('instant.cod') }}</div>
                                    <div class="text-xs text-gray-500">{{ __t('instant.cod_cash_desc') }}</div>
                                </div>
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== Notes ===== --}}
                    @if($ibS('field_notes_enabled'))
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i class="fas fa-note-sticky text-blue-600"></i>
                            {{ $ibS('field_notes_label') ?? __t('instant.notes') }} @if(!$ibS('field_notes_required'))({{ __t('common.optional') }})@endif
                        </h2>
                        <textarea name="notes" rows="2" x-model="form.notes"
                                  class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm"
                                  placeholder="{{ $ibS('field_notes_placeholder') ?? __t('instant.notes_placeholder') }}"></textarea>
                    </div>
                    @endif

                    {{-- ===== Coupon ===== --}}
                    @if($ibS('field_coupon_enabled'))
                    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-5">
                        <h2 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i class="fas fa-ticket text-blue-600"></i>
                            {{ $ibS('field_coupon_label') ?? __t('instant.coupon') }}
                        </h2>
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode" placeholder="{{ $ibS('field_coupon_placeholder') ?? __t('instant.coupon_placeholder') }}"
                                   class="flex-1 px-3 py-2 border-2 border-gray-200 rounded-lg text-sm font-mono uppercase">
                            <button type="button" @click="applyCoupon()" :disabled="couponLoading || !couponCode"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-50">
                                <i x-show="!couponLoading" class="fas fa-check"></i>
                                <i x-show="couponLoading" class="fas fa-spinner fa-spin"></i>
                            </button>
                        </div>
                        <template x-if="couponMessage">
                            <p class="text-xs text-green-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i>
                                <span x-text="couponMessage"></span>
                            </p>
                        </template>
                        <template x-if="couponError">
                            <p class="text-xs text-red-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="couponError"></span>
                            </p>
                        </template>
                    </div>
                    @endif

                    {{-- ===== Order Summary (live) ===== --}}
                    <div class="summary-card border-2 border-green-200 rounded-2xl p-5">
                        <h2 class="text-base font-bold mb-3 flex items-center gap-2 text-gray-800">
                            <i class="fas fa-receipt text-green-600"></i>
                            {{ __t('instant.order_summary') }}
                            <span x-show="loading" class="text-xs text-gray-400 me-auto">
                                <i class="fas fa-spinner fa-spin"></i> {{ __t('common.loading') }}
                            </span>
                        </h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __t('product.name') }} (<span x-text="quantity"></span>×)</span>
                                <span class="font-semibold" x-text="formatMoney(subtotal) + ' ' + currencySymbol"></span>
                            </div>
                            <template x-if="discount > 0">
                                <div class="flex justify-between text-red-600">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-tag text-xs"></i> {{ __t('instant.discount') }}
                                        <template x-if="appliedCoupon">
                                            <span class="badge bg-red-100 text-red-600 text-[10px] px-1.5 py-0 rounded me-1" x-text="appliedCoupon.code"></span>
                                        </template>
                                    </span>
                                    <span class="font-semibold" x-text="'- ' + formatMoney(discount) + ' ' + currencySymbol"></span>
                                </div>
                            </template>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __t('instant.shipping') }} <span x-text="selectedShippingOption ? '(' + selectedShippingOption.label + ')' : ''"></span></span>
                                <span class="font-semibold" :class="shippingCost === 0 ? 'text-green-600' : ''"
                                      x-text="shippingCost === 0 ? '{{ __t("common.free") }}' : formatMoney(shippingCost) + ' ' + currencySymbol"></span>
                            </div>
                            <template x-if="paymentMethod === 'cod' && codFee > 0">
                                <div class="flex justify-between text-amber-600">
                                    <span><i class="fas fa-money-bill text-xs ms-1"></i>{{ __t('instant.cod_fee') }}</span>
                                    <span class="font-semibold" x-text="formatMoney(codFee) + ' ' + currencySymbol"></span>
                                </div>
                            </template>
                            <hr class="border-green-200 my-2">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-gray-800">{{ __t('instant.total') }}</span>
                                <span class="text-2xl font-extrabold text-green-600 price-pulse" :key="grandTotal"
                                      x-text="formatMoney(grandTotal) + ' ' + currencySymbol"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Trust & Submit ===== --}}
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-xs text-gray-500 px-2">
                            <i class="fas fa-shield-halved text-green-600 mt-0.5"></i>
                            <span>{{ __t('instant.secure_notice') }}</span>
                        </div>

                        <button type="submit" :disabled="submitting || !canSubmit"
                                class="w-full py-4 rounded-xl font-sora font-extrabold text-base sm:text-lg shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                :class="canSubmit ? 'bg-primary-container text-on-primary-container hover:bg-inverse-primary hover:scale-[1.02] active:scale-95 shadow-md' : 'bg-surface-dim text-secondary'">
                            <span x-show="!submitting" class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">check_circle</span>
                                <span>{{ __t('instant.complete_order') }} — <span x-text="formatMoney(grandTotal) + ' ' + currencySymbol"></span></span>
                            </span>
                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined animate-spin">progress_activity</span>
                                <span>{{ __t('common.processing') }}</span>
                            </span>
                        </button>

                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-shield-halved text-green-500"></i>
                                <span>{{ __t('instant.secure_payment') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-truck-fast text-blue-500"></i>
                                <span>{{ __t('instant.fast_shipping') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-rotate-left text-purple-500"></i>
                                <span>{{ __t('instant.guaranteed_return') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-headset text-amber-500"></i>
                                <span>{{ __t('instant.support_24_7') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ SUCCESS OVERLAY ============ --}}
    <div x-show="submitted" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div @click.outside="resetForm()"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center"
             style="border: {{ $ibS('form_border_width') }}px solid {{ $ibS('form_border_color') }};
                    border-radius: {{ $ibS('form_border_radius') }}px;">
            <div style="font-size: {{ $ibS('success_icon_size') }}px; color: {{ $ibS('success_icon_color') }}">✅</div>
            <h3 style="color: {{ $ibS('success_title_color') }}; font-size: 18px; font-weight: bold" class="mt-3">{{ $ibS('success_title') }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $ibS('success_message') }}</p>

            <template x-if="successOrderNumber && {{ $ibS('success_show_order_number') ? 'true' : 'false' }}">
                <div class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-xs text-gray-500">{{ __t('instant.order_number') }}</p>
                    <p style="color: {{ $ibS('success_order_number_color') }}; font-size: {{ $ibS('success_order_number_size') }}px"
                       class="font-bold" x-text="successOrderNumber"></p>
                </div>
            </template>

            <template x-if="successDetails && {{ $ibS('success_show_order_details') ? 'true' : 'false' }}">
                <div class="mt-3 text-end text-sm bg-gray-50 rounded-xl p-3 border border-gray-200 space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">{{ __t('product.name') }}</span><span class="font-medium" x-text="successDetails.product_name"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __t('instant.total') }}</span><span class="font-bold text-green-600" x-text="successDetails.total"></span></div>
                </div>
            </template>

            <div class="mt-4 flex flex-col gap-2">
                <template x-if="successWhatsappUrl && {{ $ibS('success_show_whatsapp_button') ? 'true' : 'false' }}">
                    <a :href="successWhatsappUrl" target="_blank"
                       class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm text-white bg-green-600 hover:bg-green-700 transition">
                        <i class="fab fa-whatsapp"></i> {{ __t('instant.whatsapp_contact') }}
                    </a>
                </template>
                <button @click="resetForm()"
                        style="background: {{ $ibS('button_bg_color') }}; color: {{ $ibS('button_text_color') }};
                               border-radius: {{ $ibS('button_border_radius') }}px; height: {{ $ibS('button_height') }}px;"
                        class="w-full font-bold text-sm flex items-center justify-center">
                    {{ $ibS('success_button_text') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ============ RELATED PRODUCTS ============ --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div class="container-app py-8 border-t">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-thumbs-up text-blue-600"></i>
                {{ __t('product.related_products') }}
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($relatedProducts as $rel)
                    @include('frontend.partials.product-card', ['product' => $rel, 'symbol' => currentCurrencySymbol()])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function instantBuyForm() {
    return {
        // === Product Data ===
        product: {
            id: null,
            name: '',
            basePrice: 0,
            salePrice: 0,
            finalPrice: 0,
            discountPercent: 0,
            stock: 0,
            sku: '',
            weight: 0,
            categoryName: '',
            categorySlug: '',
        },
        images: [],
        activeImage: 0,

        // === Options ===
        @if($hasOptions)
        selectedOptions: {!! json_encode($product->options->mapWithKeys(fn($o) => [$o->id => null])->toArray()) !!},
        optionsAdjustments: {!! json_encode($product->options->reduce(function ($carry, $o) { foreach ($o->values as $v) { $carry[(int) $v->id] = (float) $v->price_adjustment; } return $carry; }, [])) !!},
        @else
        selectedOptions: {},
        optionsAdjustments: {},
        @endif
        @if($hasCustomFields)
        customFieldPrice: {{ (float) ($product->customFields->whereIn('type', ['text', 'textarea'])->first()?->price_effect ?? 0) }},
        @else
        customFieldPrice: 0,
        @endif
        customText: '',

        // === Quantity & Location ===
        quantity: 1,
        countryCode: 'DZ',
        stateCode: '',
        city: '',
        countries: {},
        currentStates: {},
        dialCode: '',
        weight: 0,

        // === Shipping & Payment ===
        shippingMethod: 'standard',
        deliveryType: 'home',
        shippingCompanyId: '',
        paymentMethod: 'cod',
        currencySymbol: '{{ __t('common.currency') }}',

        // === Conversion Rate ===
        conversionRate: window.__CONVERSION_RATE__ || 1,
        storeCountry: 'DZ',

        // === Dynamic Shipping Options ===
        shippingOptions: [],
        selectedShippingOption: null,
        fixedCompany: null,

        // === Zone-based Delivery Types ===
        supportedDeliveryTypes: [],
        zoneDeliveryType: 'home',

        // === Pricing (live) ===
        subtotal: 0,
        shippingCost: 0,
        expressCost: 0,
        shippingFree: false,
        discount: 0,
        codFee: 0,
        grandTotal: 0,
        appliedCoupon: null,

        // === Form data ===
        form: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            address: '',
            district: '',
            zip: '',
            notes: '',
        },

        // === UI State ===
        submitting: false,
        submitted: false,
        loading: false,
        couponCode: '',
        couponLoading: false,
        couponMessage: '',
        couponError: '',
        calcTimer: null,
        authUser: null,
        ibEnabled: true,
        successOrderNumber: '',
        successWhatsappUrl: '',
        successDetails: null,

        get displayPrice() {
            // The unit price (per piece) for display in the product info
            return (this.product.finalPrice + this.getOptionsAdjustmentTotal() + (this.customText && this.customFieldPrice ? this.customFieldPrice : 0)) * this.quantity;
        },

        get canSubmit() {
            @if($ibS('field_country_enabled') && $ibS('field_country_required'))
            if (!this.countryCode) return false;
            @endif
            @if($ibS('field_city_enabled') && $ibS('field_city_required'))
            if (!this.city) return false;
            @endif
            @if($ibS('field_phone_enabled') && $ibS('field_phone_required'))
            if (!this.form.phone) return false;
            @endif
            @if($ibS('field_address_enabled') && $ibS('field_address_required'))
            if (!this.form.address) return false;
            @endif
            @if($ibS('field_first_name_enabled') && $ibS('field_first_name_required'))
            if (!this.form.first_name) return false;
            @endif
            @if($ibS('field_last_name_enabled') && $ibS('field_last_name_required'))
            if (!this.form.last_name) return false;
            @endif
            @if($ibS('field_email_enabled') && $ibS('field_email_required'))
            if (!this.form.email) return false;
            @endif
            @if($ibS('field_state_enabled') && $ibS('field_state_required'))
            if (!this.stateCode) return false;
            @endif
            @if($ibS('field_district_enabled') && $ibS('field_district_required'))
            if (!this.form.district) return false;
            @endif
            @if($ibS('field_zip_enabled') && $ibS('field_zip_required'))
            if (!this.form.zip) return false;
            @endif
            return true;
        },

        setup(id, name, basePrice, finalPrice, salePrice, images, stock, sku, weight, countries, defaultCountry, defaultState, defaultSymbol, authUser, conversionRate, ibEnabled) {
            this.ibEnabled = ibEnabled;
            this.product.id = id;
            this.product.name = name;
            this.product.basePrice = parseFloat(basePrice) || 0;
            this.product.finalPrice = parseFloat(finalPrice) || parseFloat(basePrice) || 0;
            this.product.salePrice = parseFloat(salePrice) || 0;
            this.product.stock = parseInt(stock) || 0;
            this.product.sku = sku || '';
            this.product.weight = parseFloat(weight) || 0;
            this.product.discountPercent = this.product.basePrice > 0 && this.product.salePrice > 0 && this.product.salePrice < this.product.basePrice
                ? Math.round(100 - (this.product.salePrice / this.product.basePrice) * 100)
                : 0;
            this.images = images || [];
            this.countries = countries || {};
            this.countryCode = defaultCountry || 'DZ';
            this.currentStates = (this.countries[this.countryCode]?.states) || {};
            this.stateCode = defaultState || '';
            this.currencySymbol = countries[defaultCountry]?.currency_symbol || defaultSymbol || '{{ __t('common.currency') }}';
            this.dialCode = countries[defaultCountry]?.dial_code || '+213';
            this.conversionRate = parseFloat(conversionRate) || 1;
            this.storeCountry = '@json(config('ecommerce.store.default_country', 'DZ'))';
            this.authUser = authUser || null;
            @if($product->category)
            this.product.categoryName = @json($product->category->name);
            this.product.categorySlug = @json($product->category->slug);
            @endif
            // Pre-fill user data
            if (authUser && authUser.name) {
                const parts = authUser.name.split(' ');
                this.form.first_name = parts[0] || '';
                this.form.last_name = parts.slice(1).join(' ') || '';
                this.form.email = authUser.email || '';
                this.form.phone = authUser.phone || '';
            }

            // Initial calculation + shipping options
            if (this.city) {
                this.fetchShippingOptions();
            }
            this.recalculate();
        },

        selectShippingOption(opt) {
            this.selectedShippingOption = opt;
            this.shippingMethod = opt.type;
            this.shippingCompanyId = opt.company_id || '';
            this.recalculate();
        },

        async fetchShippingOptions() {
            if (!this.product.id || !this.countryCode || !this.city) return;
            try {
                const payload = {
                    product_id: this.product.id,
                    country_code: this.countryCode,
                    city: this.city,
                    delivery_type: this.deliveryType,
                };
                const res = await fetch(this.ibEnabled ? '{{ route('instant-buy.shipping-options') }}' : '{{ route('instant.shipping-options') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.success) {
                    this.shippingOptions = data.options || [];
                    this.fixedCompany = data.fixed_company || null;
                    this.supportedDeliveryTypes = data.supported_delivery_types || ['home'];
                    this.zoneDeliveryType = data.zone_delivery_type || 'home';
                    this.deliveryType = this.supportedDeliveryTypes[0];
                    // Auto-select first option if current selection is invalid
                    if (this.shippingOptions.length > 0) {
                        const currentValid = this.shippingOptions.find(o =>
                            o.type === this.selectedShippingOption?.type &&
                            o.company_id === this.selectedShippingOption?.company_id &&
                            o.delivery_type === this.deliveryType
                        );
                        if (!currentValid) {
                            this.selectShippingOption(this.shippingOptions[0]);
                        }
                    }
                }
            } catch (e) { /* silent */ }
        },

        onStateChange() {
            if (this.stateCode && this.currentStates[this.stateCode]) {
                this.city = this.currentStates[this.stateCode];
                this.fetchShippingOptions();
                this.recalculate();
            }
        },

        onCountryChange() {
            const info = this.countries[this.countryCode] || {};
            const dial = String(info.dial_code || '+249').replace(/^\+/, '');
            this.dialCode = '+' + dial;
            this.currencySymbol = info.currency_symbol || this.currencySymbol;
            this.stateCode = '';
            this.city = '';
            this.currentStates = info.states || {};
            // Recalculate conversion rate
            const baseCountry = this.storeCountry;
            const baseRate = parseFloat(this.countries[baseCountry]?.rate_to_usd) || 1;
            const targetRate = parseFloat(info.rate_to_usd) || 1;
            this.conversionRate = baseRate > 0 && targetRate > 0 ? baseRate / targetRate : 1;
            this.recalculate();
        },

        getOptionAdjustment(optionId) {
            const valueId = this.selectedOptions[optionId];
            if (!valueId) return 0;
            return parseFloat(this.optionsAdjustments[valueId] || 0);
        },

        getOptionsAdjustmentTotal() {
            let total = 0;
            for (const [optionId, valueId] of Object.entries(this.selectedOptions)) {
                if (valueId) {
                    total += parseFloat(this.optionsAdjustments[valueId] || 0);
                }
            }
            return total;
        },

        recalculate() {
            clearTimeout(this.calcTimer);
            this.calcTimer = setTimeout(() => this.sendCalculate(), 300);
        },

        async sendCalculate() {
            this.loading = true;

            // Always show optimistic local calc
            this.subtotal = (this.product.finalPrice + this.getOptionsAdjustmentTotal()) * this.quantity
                + (this.customText && this.customFieldPrice ? this.customFieldPrice : 0);

            if (!this.city || !this.countryCode) {
                this.shippingCost = 0;
                this.expressCost = 0;
                this.grandTotal = this.subtotal - this.discount + this.codFee;
                this.loading = false;
                return;
            }

            try {
                const payload = {};

                if (this.ibEnabled) {
                    // New controller format (InstantBuyOrderController)
                    payload.product_id = this.product.id;
                    payload.quantity = this.quantity;
                    payload.country_code = this.countryCode;
                    payload.city = this.city;
                    payload.shipping_method_type = this.shippingMethod;
                    payload.shipping_cost = this.selectedShippingOption?.cost ?? 0;
                    payload.delivery_type = this.deliveryType;
                    payload.coupon_code = this.appliedCoupon?.code || null;
                    payload.custom_text = this.customText || null;

                    // Convert options map to array of IDs
                    const selectedValues = [];
                    for (const [, v] of Object.entries(this.selectedOptions)) {
                        if (v) selectedValues.push(v);
                    }
                    if (selectedValues.length > 0) payload.selected_options = selectedValues;
                } else {
                    // Old controller format (InstantBuyController)
                    payload.product_id = this.product.id;
                    payload.quantity = this.quantity;
                    payload.country_code = this.countryCode;
                    payload.city = this.city;
                    payload.state_code = this.stateCode;
                    payload.shipping_method = this.shippingMethod;
                    payload.delivery_type = this.deliveryType;
                    payload.shipping_company_id = this.shippingCompanyId || null;
                    payload.coupon_code = this.appliedCoupon?.code || null;
                    payload.custom_text = this.customText || null;

                    const opts = {};
                    let hasOptions = false;
                    for (const [k, v] of Object.entries(this.selectedOptions)) {
                        if (v) { opts[k] = v; hasOptions = true; }
                    }
                    if (hasOptions) payload.options = opts;
                }

                const res = await fetch(this.ibEnabled ? '{{ route('instant-buy.calculate') }}' : '{{ route('instant.calculate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (data.success) {
                    this.subtotal = data.subtotal;
                    // Use local selected option cost for shipping (avoids race condition
                    // where first request with null selectedShippingOption overwrites)
                    if (this.selectedShippingOption) {
                        this.shippingCost = this.selectedShippingOption.cost;
                        this.shippingFree = this.selectedShippingOption.is_free;
                    } else {
                        this.shippingCost = data.shipping_cost;
                        this.shippingFree = data.shipping_free;
                    }
                    this.discount = data.discount;
                    this.weight = data.weight;
                    this.currencySymbol = data.currency_symbol || this.currencySymbol;
                    // Recalculate total locally to reflect actual shipping cost
                    this.grandTotal = Math.max(0, this.subtotal + this.shippingCost - this.discount)
                        + (this.paymentMethod === 'cod' ? this.codFee : 0);
                }
            } catch (e) {
                console.warn('Calculate error:', e);
            } finally {
                this.loading = false;
            }
        },

        async applyCoupon() {
            if (!this.couponCode) return;
            this.couponLoading = true;
            this.couponError = '';
            this.couponMessage = '';

            try {
                const res = await fetch(this.ibEnabled ? '{{ route('instant-buy.coupon') }}' : '{{ route('instant.coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ code: this.couponCode, subtotal: this.subtotal }),
                });
                const data = await res.json();
                if (data.success && data.coupon) {
                    this.appliedCoupon = data.coupon;
                    this.couponMessage = data.coupon.description;
                    this.couponError = '';
                    this.recalculate();
                } else {
                    this.couponError = data.message || '{{ __t('shop.show.coupon_invalid') }}';
                    this.appliedCoupon = null;
                }
            } catch (e) {
                this.couponError = '{{ __t('shop.show.coupon_error') }}';
            } finally {
                this.couponLoading = false;
            }
        },

        removeCoupon() {
            this.appliedCoupon = null;
            this.couponCode = '';
            this.couponMessage = '';
            this.couponError = '';
            this.recalculate();
        },

        formatMoney(amount) {
            if (isNaN(amount) || amount === null || amount === undefined) amount = 0;
            const locale = document.documentElement.lang || 'en';
            return new Intl.NumberFormat(locale, { maximumFractionDigits: 2, minimumFractionDigits: 0 }).format(Math.round(amount * this.conversionRate * 100) / 100);
        },

        async submitForm(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }
            if (!this.canSubmit) {
                event.preventDefault();
                alert('{{ __t('shop.show.fill_required') }}');
                return;
            }
            event.preventDefault();
            this.submitting = true;
            document.documentElement.classList.add('is-loading');

            try {
                const fd = new FormData(event.target);

                if (this.ibEnabled) {
                    // Convert options[optionId] to selected_options[] for new controller
                    if (fd.has('options')) {
                        const optVals = [];
                        for (const [k, v] of fd.entries()) {
                            if (k.startsWith('options[')) optVals.push(v);
                        }
                        optVals.forEach(v => fd.append('selected_options[]', v));
                        fd.delete('options');
                    }
                    if (!fd.has('delivery_type')) fd.append('delivery_type', this.deliveryType);
                    if (this.shippingMethod) fd.set('shipping_method_type', this.shippingMethod);
                    if (!fd.has('payment_method')) fd.append('payment_method', this.paymentMethod);
                    if (!fd.has('quantity')) fd.append('quantity', this.quantity);
                    if (this.selectedShippingOption) fd.set('shipping_cost', this.selectedShippingOption.cost);
                } else {
                    if (!fd.has('delivery_type')) fd.append('delivery_type', this.deliveryType);
                    if (!fd.has('shipping_method')) fd.append('shipping_method', this.shippingMethod);
                    if (!fd.has('payment_method')) fd.append('payment_method', this.paymentMethod);
                    if (!fd.has('quantity')) fd.append('quantity', this.quantity);
                    if (this.shippingCompanyId) fd.set('shipping_company_id', this.shippingCompanyId);
                    if (this.selectedShippingOption) fd.set('shipping_cost', this.selectedShippingOption.cost);
                }

                const res = await fetch(event.target.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const data = await res.json();
                    if (data.success) {
                        if (this.ibEnabled && data.data) {
                            this.successOrderNumber = '# ' + data.data.order_number;
                            this.successWhatsappUrl = data.data.whatsapp_url || '';
                            this.successDetails = {
                                product_name: data.data.product_name,
                                total: this.formatMoney(data.data.grand_total) + ' ' + this.currencySymbol,
                            };
                            this.submitted = true;
                            this.submitting = false;
                            document.documentElement.classList.remove('is-loading');
                        } else {
                            window.location.href = data.redirect || ('/order/' + data.order_number + '/thanks');
                        }
                    } else if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        alert(firstError[0] || '{{ __t('shop.show.order_error') }}');
                        this.submitting = false;
                        document.documentElement.classList.remove('is-loading');
                    } else {
                        alert(data.message || '{{ __t('shop.show.general_error') }}');
                        this.submitting = false;
                        document.documentElement.classList.remove('is-loading');
                    }
                } else if (res.redirected) {
                    window.location.href = res.url;
                } else {
                    window.location.href = event.target.action;
                }
            } catch (e) {
                console.error('Submit error:', e);
                alert('{{ __t('shop.show.submit_error') }}');
                this.submitting = false;
                document.documentElement.classList.remove('is-loading');
            }
        },

        resetForm() {
            this.submitted = false;
            this.successOrderNumber = '';
            this.successWhatsappUrl = '';
            this.successDetails = null;
            this.form.first_name = '';
            this.form.last_name = '';
            this.form.phone = '';
            this.form.address = '';
            this.form.notes = '';
            this.city = '';
            this.stateCode = '';
            this.shippingOptions = [];
            this.selectedShippingOption = null;
            this.subtotal = 0;
            this.shippingCost = 0;
            this.discount = 0;
            this.grandTotal = 0;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    };
}
</script>
@endpush
