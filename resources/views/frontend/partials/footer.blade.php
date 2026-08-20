@php
    $countries = config('ecommerce.countries', []);
    $defaultCountry = $countries[config('ecommerce.shipping.default_country', 'DZ')] ?? null;
    $countryName = $defaultCountry['name'] ?? __t('footer.favorite_store');
@endphp



{{-- Main footer --}}
<footer class="bg-secondary text-surface-dim border-t border-outline-variant/20 mt-16">
    <div class="container-app py-12 lg:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 border-b border-outline-variant/20 pb-12">
            {{-- Brand --}}
            <div class="col-span-1 sm:col-span-2 lg:col-span-2">
                <div class="mb-4">
                    @if(site('store_logo'))
                        <img src="{{ site('store_logo') }}" alt="{{ site('store_name') }}" loading="lazy" decoding="async" class="h-14 w-auto object-contain max-w-[220px] rounded-xl p-2 bg-surface-container-lowest">
                    @else
                        <a href="{{ route('home') }}" class="font-sora text-2xl font-black text-primary-container flex items-center gap-2">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
                            <span>{{ site('store_name', config('app.name')) }}</span>
                        </a>
                    @endif
                </div>
                <p class="text-sm leading-relaxed mb-6 max-w-md text-surface-dim">
                    {{ site('footer_about', __t('footer.about_store')) }}
                </p>
                @php
                    $wa = preg_replace('/[^0-9]/', '', site('social_whatsapp', site('whatsapp_number')) ?: site('store_phone'));
                    $wa = ltrim($wa, '0');
                    if(strlen($wa) < 12) $wa = '213' . $wa;
                @endphp
                <div class="flex gap-2">
                    @if(site('social_facebook', site('facebook_url')))
                        <a href="{{ site('social_facebook', site('facebook_url')) }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-brand-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Facebook">
                            <i class="fa-brands fa-facebook-f text-lg"></i>
                        </a>
                    @endif
                    @if(site('social_twitter', site('twitter_url', site('x_url'))))
                        <a href="{{ site('social_twitter', site('twitter_url', site('x_url'))) }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-brand-400 hover:text-white flex items-center justify-center transition shadow-sm" title="Twitter">
                            <i class="fa-brands fa-x-twitter text-lg"></i>
                        </a>
                    @endif
                    @if(site('social_instagram', site('instagram_url')))
                        <a href="{{ site('social_instagram', site('instagram_url')) }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-gradient-to-br hover:from-pink-500 hover:to-purple-500 hover:text-white flex items-center justify-center transition shadow-sm" title="Instagram">
                            <i class="fa-brands fa-instagram text-lg"></i>
                        </a>
                    @endif
                    @if(site('social_whatsapp', site('whatsapp_number')))
                        @php
                            $wa = preg_replace('/[^0-9]/', '', site('social_whatsapp', site('whatsapp_number')));
                            $wa = ltrim($wa, '0');
                            // If number has no country code (len < 12), assume Algeria +213
                            if(strlen($wa) < 12) $wa = '213' . $wa;
                        @endphp
                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-green-600 hover:text-white flex items-center justify-center transition shadow-sm" title="WhatsApp">
                            <img src="{{ asset('storage/icons/whatsapp.png') }}" alt="WhatsApp" loading="lazy" decoding="async" class="h-5 w-5 object-contain">
                        </a>
                    @endif
                    @if(site('social_youtube', site('youtube_url')))
                        <a href="{{ site('social_youtube', site('youtube_url')) }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-red-600 hover:text-white flex items-center justify-center transition shadow-sm" title="YouTube">
                            <i class="fa-brands fa-youtube text-lg"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h3 class="font-sora font-bold text-on-primary mb-4 text-base">{{ __t('footer.quick_links') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('nav.home') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shop.index') }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.all_products') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('orders.index') }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.my_orders') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('wishlist.index') }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.wishlist') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Customer service --}}
            <div>
                <h3 class="font-sora font-bold text-on-primary mb-4 text-base">{{ __t('footer.customer_service') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('page.show', ['slug' => 'return-policy']) }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.return_policy') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('page.show', ['slug' => 'shipping']) }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.shipping_delivery') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('page.show', ['slug' => 'faq']) }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.faq') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('page.show', ['slug' => 'privacy']) }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.privacy_policy') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('page.show', ['slug' => 'terms']) }}" class="text-surface-dim hover:text-primary-container hover:underline transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-[10px] text-primary-container">chevron_right</span> {{ __t('footer.terms_conditions') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="font-sora font-bold text-on-primary mb-4 text-base">{{ __t('footer.contact_us') }}</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary-container mt-1">phone</span>
                        <div>
                            <p class="text-secondary-fixed-dim text-xs">{{ __t('footer.tech_support') }}</p>
                            <a href="tel:{{ site('contact_phone', '+213550000000') }}" class="hover:text-primary-container transition text-on-primary" dir="ltr">{{ site('contact_phone', '+213 550 00 00 00') }}</a>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary-container mt-1">mail</span>
                        <div>
                            <p class="text-secondary-fixed-dim text-xs">{{ __t('footer.email') }}</p>
                            <a href="mailto:{{ site('contact_email', 'contact@fouadfitness.dz') }}" class="hover:text-primary-container transition text-on-primary">{{ site('contact_email', 'contact@fouadfitness.dz') }}</a>
                        </div>
                    </li>
                    @if(site('contact_whatsapp'))
                        <li class="flex items-start gap-3">
                            <i class="fa-brands fa-whatsapp text-primary-container mt-1.5 text-base"></i>
                            <div>
                                <p class="text-secondary-fixed-dim text-xs">{{ __t('footer.whatsapp') }}</p>
                                @php
                                    $cwa = preg_replace('/[^0-9]/', '', site('contact_whatsapp'));
                                    $cwa = ltrim($cwa, '0');
                                    if(strlen($cwa) < 12) $cwa = '213' . $cwa;
                                @endphp
                                <a href="https://wa.me/{{ $cwa }}" target="_blank" class="hover:text-primary-container transition text-on-primary" dir="ltr">{{ site('contact_whatsapp') }}</a>
                            </div>
                        </li>
                    @endif
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary-container mt-1">location_on</span>
                        <div>
                            <p class="text-secondary-fixed-dim text-xs">{{ __t('footer.headquarters') }}</p>
                            <p class="text-on-primary">{{ site('contact_address', $countryName) }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-surface-dim">
            <p class="font-body-md text-xs sm:text-sm">
                © {{ date('Y') }} {{ site('store_name', config('app.name')) }}. {{ site('footer_copyright', __t('footer.copyright')) }}.
            </p>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 bg-on-surface-variant/20 border border-outline-variant/30 rounded-lg text-xs inline-flex items-center gap-1 text-surface-dim">
                    <span class="material-symbols-outlined text-primary-container text-sm">credit_card</span> {{ __t('footer.cod_badge') }}
                </span>
                <span class="px-3 py-1.5 bg-on-surface-variant/20 border border-outline-variant/30 rounded-lg text-xs inline-flex items-center gap-1 text-surface-dim">
                    <span class="material-symbols-outlined text-primary-container text-sm">shield</span> {{ __t('footer.secure_payment') }}
                </span>
            </div>
        </div>
    </div>
</footer>
