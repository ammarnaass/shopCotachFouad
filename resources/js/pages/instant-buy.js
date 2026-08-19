/* ====================================================================
   INSTANT BUY — Product Detail Page Alpine Component

   Page: frontend/shop/show.blade.php
   Data contract: setup() receives all server-injected values via x-init
   ==================================================================== */

Alpine.data('instantBuyForm', () => ({
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

    // === Options (injected via setup) ===
    selectedOptions: {},
    optionsAdjustments: {},
    customFieldPrice: 0,
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
    currencySymbol: '',

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

    // === Validation rules (injected via setup) ===
    validationRules: [],

    // === Route URLs (injected via setup) ===
    routes: {},

    // === Translations (injected via setup) ===
    t: {},

    get displayPrice() {
        return (this.product.finalPrice + this.getOptionsAdjustmentTotal() + (this.customText && this.customFieldPrice ? this.customFieldPrice : 0)) * this.quantity;
    },

    get canSubmit() {
        for (const rule of this.validationRules) {
            const val = rule.field.startsWith('form.')
                ? this.form[rule.field.replace('form.', '')]
                : this[rule.field];
            if (!val) return false;
        }
        return true;
    },

    setup(config) {
        this.ibEnabled = config.ibEnabled;
        this.product.id = config.id;
        this.product.name = config.name;
        this.product.basePrice = parseFloat(config.basePrice) || 0;
        this.product.finalPrice = parseFloat(config.finalPrice) || parseFloat(config.basePrice) || 0;
        this.product.salePrice = parseFloat(config.salePrice) || 0;
        this.product.stock = parseInt(config.stock) || 0;
        this.product.sku = config.sku || '';
        this.product.weight = parseFloat(config.weight) || 0;
        this.product.discountPercent = this.product.basePrice > 0 && this.product.salePrice > 0 && this.product.salePrice < this.product.basePrice
            ? Math.round(100 - (this.product.salePrice / this.product.basePrice) * 100)
            : 0;
        this.images = config.images || [];
        this.countries = config.countries || {};
        this.countryCode = config.defaultCountry || 'DZ';
        this.currentStates = (this.countries[this.countryCode]?.states) || {};
        this.stateCode = config.defaultState || '';
        this.currencySymbol = config.countries[config.defaultCountry]?.currency_symbol || config.defaultSymbol || '';
        this.dialCode = config.countries[config.defaultCountry]?.dial_code || '+213';
        this.conversionRate = parseFloat(config.conversionRate) || 1;
        this.storeCountry = config.storeCountry || 'DZ';
        this.authUser = config.authUser || null;

        // Inject Blade-dependent data
        this.selectedOptions = config.selectedOptions || {};
        this.optionsAdjustments = config.optionsAdjustments || {};
        this.customFieldPrice = parseFloat(config.customFieldPrice) || 0;
        this.validationRules = config.validationRules || [];
        this.routes = config.routes || {};
        this.t = config.t || {};

        // Category info
        if (config.categoryName) {
            this.product.categoryName = config.categoryName;
            this.product.categorySlug = config.categorySlug || '';
        }

        // Pre-fill user data
        if (this.authUser && this.authUser.name) {
            const parts = this.authUser.name.split(' ');
            this.form.first_name = parts[0] || '';
            this.form.last_name = parts.slice(1).join(' ') || '';
            this.form.email = this.authUser.email || '';
            this.form.phone = this.authUser.phone || '';
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
            const url = this.ibEnabled ? this.routes.shippingOptionsNew : this.routes.shippingOptions;
            const res = await fetch(url, {
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
                payload.product_id = this.product.id;
                payload.quantity = this.quantity;
                payload.country_code = this.countryCode;
                payload.city = this.city;
                payload.shipping_method_type = this.shippingMethod;
                payload.shipping_cost = this.selectedShippingOption?.cost ?? 0;
                payload.delivery_type = this.deliveryType;
                payload.coupon_code = this.appliedCoupon?.code || null;
                payload.custom_text = this.customText || null;

                const selectedValues = [];
                for (const [, v] of Object.entries(this.selectedOptions)) {
                    if (v) selectedValues.push(v);
                }
                if (selectedValues.length > 0) payload.selected_options = selectedValues;
            } else {
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

            const url = this.ibEnabled ? this.routes.calculateNew : this.routes.calculate;
            const res = await fetch(url, {
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
            const url = this.ibEnabled ? this.routes.couponNew : this.routes.coupon;
            const res = await fetch(url, {
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
                this.couponError = data.message || this.t.couponInvalid || 'Invalid coupon';
                this.appliedCoupon = null;
            }
        } catch (e) {
            this.couponError = this.t.couponError || 'Coupon error';
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
            alert(this.t.fillRequired || 'Please fill all required fields');
            return;
        }
        event.preventDefault();
        this.submitting = true;
        document.documentElement.classList.add('is-loading');

        try {
            const fd = new FormData(event.target);

            if (this.ibEnabled) {
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
                    alert(firstError[0] || this.t.orderError || 'Order error');
                    this.submitting = false;
                    document.documentElement.classList.remove('is-loading');
                } else {
                    alert(data.message || this.t.generalError || 'An error occurred');
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
            alert(this.t.submitError || 'Submission error');
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
}));
