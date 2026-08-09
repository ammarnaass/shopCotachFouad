@extends('frontend.layout')

@section('title', $product ? __t('instant.buy_product', ['name' => $product->name]) : __t('instant.title'))

@section('description', __t('instant.description'))

@php
    use Illuminate\Support\Str;
@endphp

<div class="bg-gradient-to-l from-purple-50 via-white to-blue-50 min-h-screen py-8"
     x-data="instantBuy()"
     x-init="init()">

    <div class="container mx-auto px-4 max-w-3xl">

        {{-- ============ HEADER ============ --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 bg-purple-100 text-purple-700 px-4 py-1.5 rounded-full text-sm font-semibold mb-3">
                <span class="material-symbols-outlined text-yellow-500">bolt</span>
                <span>{{ __t('instant.title') }} — {{ __t('instant.without_register') }}</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                {{ $product ? __t('instant.complete_minute') : __t('instant.choose_product') }}
            </h1>
        </div>

        {{-- ============ NO PRODUCT SELECTED: SHOW PICKER ============ --}}
        @if(!$product)
            <div class="bg-white rounded-2xl shadow-sm p-6">
                @if($popularProducts->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($popularProducts as $p)
                            <a href="{{ route('instant.buy', ['slug' => $p->slug]) }}" class="group block border-2 border-gray-200 hover:border-purple-500 rounded-xl overflow-hidden transition">
                                <div class="aspect-square bg-gray-100 overflow-hidden">
                                    <img src="{{ $p->primaryImage ? asset('storage/' . $p->primaryImage->image) : 'https://placehold.co/300x300/e5e7eb/9ca3af?text=' . urlencode($p->name) }}" class="w-full h-full object-cover group-hover:scale-105 transition" alt="{{ $p->name }}">
                                </div>
                                <div class="p-2 text-center">
                                    <p class="font-semibold text-sm truncate" title="{{ $p->name }}">{{ $p->name }}</p>
                                    <p class="text-purple-600 text-sm font-bold">{{ number_format(convertPrice($p->final_price), 0) }} {{ currentCurrencySymbol() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <p class="text-center text-gray-500 text-sm mt-6">
                        {{ __t('common.or') }} <a href="{{ route('shop.index') }}" class="text-purple-600 font-semibold hover:underline">{{ __t('instant.browse_all') }}</a>
                    </p>
                @else
                    <p class="text-center text-gray-500 py-8">{{ __t('instant.no_products') }}</p>
                @endif
            </div>
        @else
            {{-- ============ INSTANT FORM ============ --}}
            <form method="POST" action="{{ route('instant.submit') }}" @submit.prevent="submitForm($event)" class="space-y-4" id="instant-buy-form">
                @csrf

                {{-- 1) PRODUCT SUMMARY (compact) --}}
                <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
                    <img :src="product.image" :alt="product.name" class="w-20 h-20 rounded-xl object-cover border">
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-base md:text-lg line-clamp-2" x-text="product.name"></h2>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-xl font-extrabold text-purple-600" x-text="formatMoney(product.sale_price || product.price)"></span>
                            <template x-if="product.sale_price">
                                <span class="text-sm text-gray-400 line-through" x-text="formatMoney(product.price)"></span>
                            </template>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="text-gray-400 hover:text-red-500 text-sm" title="{{ __t('common.cancel') }}">
                        <span class="material-symbols-outlined text-2xl">cancel</span>
                    </a>
                </div>

                {{-- 2) OPTIONS + QUANTITY (side by side on desktop, stacked on mobile) --}}
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    {{-- Product options (size, color, ...) --}}
                    <template x-for="(values, optionId) in (product.options || {})" :key="optionId">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span x-text="Object.keys(product.options).indexOf(String(optionId)) >= 0 ? '' : ''"></span>
                                <span class="text-gray-500 text-xs">{{ __t('common.choose') }}:</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(label, valueId) in values" :key="valueId">
                                    <label class="cursor-pointer">
                                        <input type="radio" :name="'options[' + optionId + ']'" :value="valueId"
                                               x-model="selectedOptions[optionId]"
                                               @change="recalculate()"
                                               class="sr-only peer">
                                        <span class="px-3 py-2 border-2 border-gray-200 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 text-sm font-medium inline-block"
                                              x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Custom fields (only the first one) --}}
                    <template x-if="product.custom_fields && product.custom_fields.length > 0">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span x-text="product.custom_fields[0].label"></span>
                                <template x-if="product.custom_fields[0].required">
                                    <span class="text-red-500">*</span>
                                </template>
                            </label>
                            <textarea x-model="customText" @input="recalculate()" :required="product.custom_fields[0].required"
                                      :placeholder="product.custom_fields[0].label"
                                      class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm" rows="2"></textarea>
                        </div>
                    </template>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __t('product.quantity') }}</label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1); recalculate()"
                                    class="w-10 h-10 border-2 border-gray-200 rounded-lg hover:bg-gray-50 font-bold text-lg">−</button>
                            <input type="number" name="quantity" x-model.number="quantity" @change="recalculate()"
                                   min="1" :max="product.stock"
                                   class="w-20 text-center border-2 border-gray-200 rounded-lg py-2 font-bold text-lg">
                            <button type="button" @click="quantity = Math.min(product.stock, quantity + 1); recalculate()"
                                    class="w-10 h-10 border-2 border-gray-200 rounded-lg hover:bg-gray-50 font-bold text-lg">+</button>
<span class="text-sm text-gray-500 
                            ms-2" x-text="'(' + product.stock + ' {{ __t("instant.in_stock") }})'"></span>
                        </div>
                    </div>
                </div>

                {{-- 3) SHIPPING INFO (compact grid) --}}
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-3">
                    <h3 class="font-bold text-base mb-1"><span class="material-symbols-outlined text-purple-600 me-1">local_shipping</span>{{ __t('instant.shipping_data') }}</h3>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.first_name') }} *</label>
                            <input type="text" name="first_name" x-model="form.first_name" required
                                   class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                   placeholder="{{ __t('instant.first_name_placeholder') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.last_name') }} *</label>
                            <input type="text" name="last_name" x-model="form.last_name" required
                                   class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                   placeholder="{{ __t('instant.last_name_placeholder') }}">
                        </div>
                    </div>

                    @if(site('instant_show_email', '1') === '1')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('common.email') }} {{ site('instant_req_email', '0') === '1' ? '*' : '(' . __t('common.optional') . ')' }}</label>
                        <input type="email" name="email" x-model="form.email" {{ site('instant_req_email', '0') === '1' ? 'required' : '' }}
                               class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                               placeholder="example@mail.com">
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.phone') }} *</label>
                        <div class="flex gap-2" dir="ltr">
                            <input type="text" :value="dialCode" readonly
                                   class="w-20 px-3 py-2 border-2 border-gray-200 rounded-lg bg-gray-50 text-center font-semibold text-sm">
                            <input type="tel" name="phone" x-model="form.phone" required
                                   class="flex-1 px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                   placeholder="5XXXXXXXX">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('common.country') }} *</label>
                            <select name="country_code" x-model="form.country_code" @change="onCountryChange()" required
                                    class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm">
                                @foreach($countries as $code => $info)
                                    <option value="{{ $code }}" {{ $defaultCountry == $code ? 'selected' : '' }}>
                                        {{ $info['name'] }} - {{ $info['name_en'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(site('instant_show_state', '1') === '1')
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.state') }} {{ site('instant_req_state', '0') === '1' ? '*' : '' }}</label>
                            <select name="state_code" x-model="form.state_code" @change="onStateChange()" {{ site('instant_req_state', '0') === '1' ? 'required' : '' }}
                                    class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm">
                                <option value="">— {{ __t('common.select') }} —</option>
                                <template x-for="state in (statesList || [])" :key="state.code">
                                    <option :value="state.code" x-text="state.name"></option>
                                </template>
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.city') }} *</label>
                            <input type="text" name="city" x-model="form.city" @input.debounce.400ms="fetchShippingOptions(); recalculate()" required
                                   class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                   placeholder="{{ __t('instant.city_placeholder') }}">
                        </div>
                        @if(site('instant_show_district', '1') === '1')
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.district') }} {{ site('instant_req_district', '0') === '1' ? '*' : '' }}</label>
                            <input type="text" name="district" x-model="form.district" {{ site('instant_req_district', '0') === '1' ? 'required' : '' }}
                                   class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                   placeholder="{{ __t('instant.district_placeholder') }}">
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.address') }} *</label>
                        <input type="text" name="address" x-model="form.address" required
                               class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                               placeholder="{{ __t('instant.address_placeholder') }}">
                    </div>

                    @if(site('instant_show_zip', '1') === '1')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.zip') }} {{ site('instant_req_zip', '0') === '1' ? '*' : '' }}</label>
                        <input type="text" name="zip" x-model="form.zip" {{ site('instant_req_zip', '0') === '1' ? 'required' : '' }}
                               class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                               placeholder="11111">
                    </div>
                    @endif

                    @if(site('instant_show_notes', '1') === '1')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __t('instant.notes') }}</label>
                        <textarea name="notes" x-model="form.notes"
                                  class="w-full px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm"
                                  rows="2" placeholder="{{ __t('instant.notes_placeholder') }}"></textarea>
                    </div>
                    @endif
                </div>

                {{-- 4) SHIPPING METHOD + PAYMENT (compact) --}}
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <div>
                        <h3 class="font-bold text-sm mb-2"><span class="material-symbols-outlined text-purple-600 me-1">local_shipping</span>{{ __t('instant.shipping_method') }}</h3>
                        <template x-if="shippingOptions.length === 0">
                            <div class="text-center py-4 text-sm text-gray-500">
                                <span class="material-symbols-outlined">local_shipping</span>
                                <p class="mt-1">{{ __t('instant.select_city_shipping') }}</p>
                            </div>
                        </template>
                        <template x-if="shippingOptions.length > 0">
                            <div class="space-y-2">
                                <template x-for="(opt, idx) in shippingOptions" :key="idx">
                                    <label class="cursor-pointer block">
                                        <input type="radio" name="shipping_method" :value="opt.type"
                                               x-model="selectedOptionType"
                                               @change="selectShippingOption(opt)"
                                               class="sr-only peer">
                                        <div class="p-3 border-2 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 border-gray-200 hover:border-purple-300 transition">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined" :class="opt.type === 'express' ? 'text-yellow-500' : 'text-purple-600'">local_shipping</span>
                                                    <div>
                                                        <p class="text-sm font-semibold" x-text="opt.label"></p>
                                                        <p class="text-xs text-gray-500" x-text="opt.company_name"></p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-bold" :class="opt.is_free ? 'text-green-600' : 'text-gray-700'"
                                                                                                             x-text="opt.is_free ? '{{ __t('common.free') }}' : formatMoney(opt.cost)"></span>
                                            </div>
                                            <template x-if="opt.estimated_days">
                                                <p class="text-xs text-gray-400 mt-1" x-text="'🕐 ' + opt.estimated_days"></p>
                                            </template>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>
                    </div>

                     <div>
                        <h3 class="font-bold text-sm mb-2"><span class="material-symbols-outlined text-purple-600 me-1">credit_card</span>{{ __t('instant.payment_method') }}</h3>
                        @if(site('instant_enable_bank_transfer', '0') === '1')
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="cod" x-model="form.payment_method" class="sr-only peer">
                                    <div class="p-3 border-2 rounded-lg text-center peer-checked:border-purple-600 peer-checked:bg-purple-50 border-gray-200">
                                        <span class="material-symbols-outlined text-green-600 text-lg mb-1">payments</span>
                                        <p class="text-sm font-semibold">{{ __t('instant.cod') }}</p>
                                        <p class="text-xs text-gray-500">{{ __t('instant.cod_desc') }}</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="bank_transfer" x-model="form.payment_method" class="sr-only peer">
                                    <div class="p-3 border-2 rounded-lg text-center peer-checked:border-purple-600 peer-checked:bg-purple-50 border-gray-200">
                                        <span class="material-symbols-outlined text-blue-600 text-lg mb-1">account_balance</span>
                                        <p class="text-sm font-semibold">{{ __t('instant.bank_transfer') }}</p>
                                        <p class="text-xs text-gray-500">{{ __t('instant.bank_transfer_desc') }}</p>
                                    </div>
                                </label>
                            </div>
                        @else
                            <label class="cursor-pointer block">
                                <input type="hidden" name="payment_method" value="cod">
                                <div class="p-3 border-2 border-purple-600 bg-purple-50 rounded-lg flex items-center gap-3">
                                    <span class="material-symbols-outlined text-green-600 text-2xl">payments</span>
                                    <div>
                                        <p class="text-sm font-bold">{{ __t('instant.cod') }}</p>
                                        <p class="text-xs text-gray-500">{{ __t('instant.cod_cash_desc') }}</p>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>
                </div>

                {{-- Hidden shipping_company_id --}}
                <input type="hidden" name="shipping_company_id" :value="form.shipping_company_id || ''">
                <input type="hidden" name="delivery_type" :value="form.delivery_type || ''">

                {{-- 5) SUMMARY (compact, before submit) --}}
                <div class="bg-gradient-to-l from-purple-50 to-blue-50 border-2 border-purple-200 rounded-2xl p-4 space-y-2 text-sm">
                    <h3 class="font-bold text-base mb-2"><span class="material-symbols-outlined text-purple-600 me-1">receipt</span>{{ __t('instant.order_summary') }}</h3>
                    <div class="flex justify-between"><span class="text-gray-600">{{ __t('product.name') }} × <span x-text="quantity"></span></span><span class="font-semibold" x-text="formatMoney(basePrice * quantity)"></span></div>
                    <template x-for="(item, idx) in optionsSummary" :key="idx">
                        <div class="flex justify-between text-xs"><span class="text-gray-600" x-text="item.option + ': ' + item.value"></span><span x-text="(item.adjustment >= 0 ? '+' : '') + formatMoney(item.adjustment * quantity)"></span></div>
                    </template>
                    <div class="border-t border-purple-200 my-2"></div>
                    <div class="flex justify-between"><span>{{ __t('instant.subtotal') }}</span><span class="font-semibold" x-text="formatMoney(currentSubtotal)"></span></div>
                    <div class="flex justify-between"><span>{{ __t('instant.shipping') }}</span><span x-text="shippingCost === 0 ? '{{ __t('common.free') }}' : formatMoney(shippingCost)"></span></div>
                    <template x-if="discount > 0">
                        <div class="flex justify-between text-green-600"><span>{{ __t('instant.discount') }}</span><span x-text="'−' + formatMoney(discount)"></span></div>
                    </template>
                    <div class="flex justify-between text-base font-extrabold border-t border-purple-200 pt-2 mt-2">
                        <span>{{ __t('instant.total') }}</span>
                        <span class="text-purple-600" x-text="formatMoney(grandTotal)"></span>
                    </div>
                </div>

                {{-- 6) COUPON (optional, in same card as submit) --}}
                @if(site('instant_show_coupon', '1') === '1')
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <label class="block text-sm font-semibold mb-2"><span class="material-symbols-outlined text-purple-600 me-1">confirmation_number</span>{{ __t('instant.coupon') }}</label>
                    <div class="flex gap-2">
                        <input type="text" name="coupon_code" x-model="couponCode" @input="couponCode = $event.target.value.toUpperCase()"
                               class="flex-1 px-3 py-2 border-2 border-gray-200 focus:border-purple-500 rounded-lg text-sm uppercase"
                               placeholder="{{ __t('instant.coupon_placeholder') }}">
                        <button type="button" @click="applyCoupon()" :disabled="applyingCoupon || !couponCode"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 disabled:bg-gray-300">
                            <span x-show="!applyingCoupon">{{ __t('instant.apply') }}</span>
                            <span x-show="applyingCoupon"><span class="material-symbols-outlined animate-spin">sync</span></span>
                        </button>
                    </div>
                    <p x-show="couponMessage" x-text="couponMessage" class="text-green-600 text-xs mt-1"></p>
                    <p x-show="couponError" x-text="couponError" class="text-red-600 text-xs mt-1"></p>
                </div>
                @endif

                {{-- 7) SUBMIT --}}
                <button type="submit" :disabled="submitting || !canSubmit"
                        class="w-full bg-gradient-to-l from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-4 rounded-xl font-extrabold text-base shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        :class="canSubmit ? 'hover:shadow-2xl hover:-translate-y-0.5' : 'bg-gray-400'">
                    <template x-if="!submitting">
                        <span>
<span class="material-symbols-outlined me-1">bolt</span>
                            <span>{{ __t('instant.complete_order') }} — </span>
                            <span x-text="formatMoney(grandTotal)"></span>
                        </span>
                    </template>
                    <template x-if="submitting">
                        <span><span class="material-symbols-outlined animate-spin ms-1">sync</span> {{ __t('instant.submitting') }}...</span>
                    </template>
                </button>

                <p class="text-center text-xs text-gray-500">
<span class="material-symbols-outlined text-green-600 ms-1">shield</span>
                    {{ __t('instant.secure_notice') }}
                </p>

                @csrf
            </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
function instantBuy() {
    const productData = {!! $productJson !!};
    return {
        product: productData,

        quantity: 1,
        selectedOptions: {},
        customText: '',
        couponCode: '',
        couponMessage: '',
        couponError: '',
        applyingCoupon: false,
        discount: 0,
        submitting: false,
        statesList: [],

        form: {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            country_code: @json($defaultCountry),
            state_code: '',
            city: '',
            district: '',
            address: '',
            zip: '',
            notes: '',
            shipping_method: 'standard',
            shipping_company_id: '',
            delivery_type: 'home',
            payment_method: 'cod',
        },

        countries: @json($countries),
        dialCode: '+249',
        currencySymbol: '{{ __t('common.currency') }}',
        conversionRate: window.__CONVERSION_RATE__ || 1,
        storeCountry: @json(config('ecommerce.store.default_country', 'DZ')),

        // Dynamic shipping options
        shippingOptions: [],
        selectedOptionType: 'standard',
        fixedCompany: null,

        get canSubmit() {
            const reqEmail = {{ site('instant_req_email', '0') === '1' ? 'true' : 'false' }};
            const reqState = {{ site('instant_req_state', '0') === '1' ? 'true' : 'false' }};
            const reqDistrict = {{ site('instant_req_district', '0') === '1' ? 'true' : 'false' }};
            const reqZip = {{ site('instant_req_zip', '0') === '1' ? 'true' : 'false' }};

            if (!this.form.first_name || !this.form.last_name || !this.form.phone || !this.form.city || !this.form.address) {
                return false;
            }
            if (reqEmail && !this.form.email) return false;
            if (reqState && !this.form.state_code) return false;
            if (reqDistrict && !this.form.district) return false;
            if (reqZip && !this.form.zip) return false;

            return true;
        },

        get basePrice() {
            const p = this.product;
            return parseFloat(p.sale_price || p.price || 0);
        },
        get optionsAdjustment() {
            let total = 0;
            for (const optId in this.selectedOptions) {
                const valId = this.selectedOptions[optId];
                total += parseFloat(this.product.option_adjustments[valId] || 0);
            }
            return total;
        },
        get customFieldPrice() {
            if (!this.product.custom_fields?.length) return 0;
            if (!this.customText) return 0;
            return parseFloat(this.product.custom_fields[0].price_effect || 0);
        },
        get optionsSummary() {
            const list = [];
            for (const optId in this.selectedOptions) {
                const valId = this.selectedOptions[optId];
                const opt = this.product.options[optId];
                if (opt && opt.values[valId] !== undefined) {
                    list.push({
                        option: opt.label,
                        value: opt.values[valId],
                        adjustment: parseFloat(this.product.option_adjustments[valId] || 0),
                    });
                }
            }
            return list;
        },
        get currentSubtotal() {
            return (this.basePrice + this.optionsAdjustment + this.customFieldPrice) * this.quantity;
        },
        get shippingCost() { return this._shippingCost; },
        _shippingCost: 0,
        get grandTotal() {
            return Math.max(0, this.currentSubtotal + this.shippingCost - this.discount);
        },

        init(productData) {
            if (productData) this.product = productData;
            this.updateDialCode();
            // Seed states instantly from inlined config (fetch below refreshes)
            const seed = this.countries[this.form.country_code]?.states || {};
            this.statesList = Object.entries(seed).map(([code, name]) => ({ code, name }));
            this.onCountryChange();
        },

        formatMoney(amount) {
            const converted = (parseFloat(amount) || 0) * this.conversionRate;
            const n = new Intl.NumberFormat('ar-SA', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(converted);
            return n + ' ' + this.currencySymbol;
        },

        onStateChange() {
            if (this.form.state_code && this.statesList.length > 0) {
                const match = this.statesList.find(s => s.code == this.form.state_code);
                if (match) {
                    this.form.city = match.name;
                    this.fetchShippingOptions();
                    this.recalculate();
                }
            }
        },

        async onCountryChange() {
            this.form.state_code = '';
            this.form.city = '';
            this.updateDialCode();
            this.statesList = [];
            // Recalculate conversion rate
            const info = this.countries[this.form.country_code] || {};
            const baseRate = parseFloat(this.countries[this.storeCountry]?.rate_to_usd) || 1;
            const targetRate = parseFloat(info.rate_to_usd) || 1;
            this.conversionRate = baseRate > 0 && targetRate > 0 ? baseRate / targetRate : 1;
            try {
                const statesUrl = '{{ route("api.countries.states", ["code" => "___"]) }}'.replace('___', this.form.country_code);
                const res = await fetch(statesUrl, { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this.statesList = data.states || [];
                }
            } catch (e) { /* silent */ }
            if (this.form.city) {
                await this.fetchShippingOptions();
            }
            this.recalculate();
        },

        selectShippingOption(opt) {
            this.form.shipping_method = opt.type;
            this.selectedOptionType = opt.type;
            this.form.shipping_company_id = opt.company_id || null;
            this.recalculate();
        },

        async fetchShippingOptions() {
            if (!this.product || !this.form.country_code || !this.form.city) return;
            try {
                const fd = new FormData();
                fd.append('product_id', this.product.id);
                fd.append('country_code', this.form.country_code);
                fd.append('city', this.form.city);
                fd.append('delivery_type', this.form.delivery_type || '');
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                const res = await fetch('{{ route('instant.shipping-options') }}', {
                    method: 'POST', body: fd, headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.shippingOptions = data.options || [];
                        this.fixedCompany = data.fixed_company || null;
                        // Capture delivery type from zone settings
                        if (data.supported_delivery_types && data.supported_delivery_types.length > 0) {
                            this.form.delivery_type = data.supported_delivery_types[0];
                        }

                        // Auto-select first option if current selection is invalid
                        if (this.shippingOptions.length > 0) {
                            const currentValid = this.shippingOptions.find(o => o.type === this.selectedOptionType);
                            if (!currentValid) {
                                this.selectShippingOption(this.shippingOptions[0]);
                            }
                        }
                    }
                }
            } catch (e) { /* silent */ }
        },

        updateDialCode() {
            const info = this.countries[this.form.country_code] || {};
            const code = String(info.dial_code || '249').replace(/^\+/, '');
            this.dialCode = '+' + code;
            this.currencySymbol = info.currency_symbol || '{{ __t('common.currency') }}';
            // Update conversion rate when country changes via country_code
            const baseRate = parseFloat(this.countries[this.storeCountry]?.rate_to_usd) || 1;
            const targetRate = parseFloat(info.rate_to_usd) || 1;
            this.conversionRate = baseRate > 0 && targetRate > 0 ? baseRate / targetRate : 1;
        },

        async recalculate() {
            if (!this.product) return;
            const fd = new FormData();
            fd.append('product_id', this.product.id);
            fd.append('quantity', this.quantity);
            fd.append('country_code', this.form.country_code);
            fd.append('city', this.form.city || '');
            fd.append('state_code', this.form.state_code || '');
            fd.append('shipping_method', this.form.shipping_method || 'standard');
            fd.append('delivery_type', this.form.delivery_type || '');
            fd.append('custom_text', this.customText || '');
            for (const optId in this.selectedOptions) {
                fd.append(`options[${optId}]`, this.selectedOptions[optId]);
            }
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route('instant.calculate') }}', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this._shippingCost = parseFloat(data.shipping_cost || 0);
                    if (data.discount !== undefined) this.discount = parseFloat(data.discount || 0);
                }
            } catch (e) { /* silent */ }
        },

        async applyCoupon() {
            if (!this.couponCode) return;
            this.applyingCoupon = true; this.couponError = ''; this.couponMessage = '';
            try {
                const fd = new FormData();
                fd.append('code', this.couponCode);
                fd.append('product_id', this.product.id);
                fd.append('subtotal', this.currentSubtotal);
                fd.append('country_code', this.form.country_code);
                fd.append('city', this.form.city || '');
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                const res = await fetch('{{ route('instant.coupon') }}', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.valid) {
                    this.discount = parseFloat(data.discount || 0);
                    this.couponMessage = data.message || '{{ __t('instant.coupon_applied') }}';
                } else {
                    this.couponError = data.message || '{{ __t('instant.coupon_invalid') }}';
                    this.discount = 0;
                }
            } catch (e) {
                this.couponError = '{{ __t('instant.coupon_error') }}';
            } finally {
                this.applyingCoupon = false;
                this.recalculate();
            }
        },

        async submitForm(ev) {
            if (this.submitting) return;
            this.submitting = true;
            document.documentElement.classList.add('is-loading');
            try {
                const fd = new FormData(ev.target);
                const res = await fetch('{{ route('instant.submit') }}', { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || ('/order/' + data.order_number + '/thanks');
                } else {
                    alert(data.message || '{{ __t('instant.submit_error') }}');
                    this.submitting = false;
                    document.documentElement.classList.remove('is-loading');
                }
            } catch (e) {
                alert('{{ __t('instant.submit_failed') }}');
                this.submitting = false;
                document.documentElement.classList.remove('is-loading');
            }
        }
    };
}
</script>
@endpush
