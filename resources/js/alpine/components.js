/* ====================================================================
   ALPINE.JS — REUSABLE COMPONENT FACTORIES
   ==================================================================== */

/* ----- Modal Dialog ----- */
Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    show() { this.open = true; document.body.style.overflow = 'hidden'; },
    close() { this.open = false; document.body.style.overflow = ''; },
    toggle() { this.open ? this.close() : this.show(); },
}));

/* ----- Dropdown Menu ----- */
Alpine.data('dropdown', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
}));

/* ----- Tabs ----- */
Alpine.data('tabs', (defaultTab = null) => ({
    active: defaultTab,
    is(name) { return this.active === name; },
    show(name) { this.active = name; },
}));

/* ----- Accordion (single open) ----- */
Alpine.data('accordion', () => ({
    open: null,
    toggle(name) { this.open = this.open === name ? null : name; },
    isOpen(name) { return this.open === name; },
}));

/* ----- Quantity Input with min/max ----- */
Alpine.data('quantity', (initial = 1, min = 1, max = 99) => ({
    value: initial,
    get canDecrement() { return this.value > min; },
    get canIncrement() { return this.value < max; },
    increment() { if (this.canIncrement) this.value++; },
    decrement() { if (this.canDecrement) this.value--; },
    set(v) {
        const n = parseInt(v, 10);
        if (!isNaN(n)) this.value = Math.max(min, Math.min(max, n));
    },
}));

/* ----- Search with debounce + suggestions ----- */
Alpine.data('liveSearch', (url, minChars = 2) => ({
    query: '',
    results: [],
    loading: false,
    open: false,
    timer: null,
    _search() {
        clearTimeout(this.timer);
        if (this.query.length < minChars) {
            this.results = [];
            return;
        }
        this.loading = true;
        this.timer = setTimeout(async () => {
            try {
                const res = await fetch(`${url}?q=${encodeURIComponent(this.query)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (res.ok) {
                    this.results = (await res.json()).data || [];
                }
            } finally {
                this.loading = false;
            }
        }, 300);
    },
    select(item) {
        window.location.href = item.url;
    },
    close() { this.open = false; },
    show() { this.open = true; },
}));

/* ----- Image Gallery (product page) ----- */
Alpine.data('gallery', (images) => ({
    images: images || [],
    active: 0,
    get current() { return this.images[this.active] || null; },
    select(i) { this.active = i; },
    next() { this.active = (this.active + 1) % this.images.length; },
    prev() { this.active = (this.active - 1 + this.images.length) % this.images.length; },
}));

/* ----- Color/Size Picker ----- */
Alpine.data('optionPicker', () => ({
    selected: {},
    get isComplete() {
        return Object.keys(this._required).every(k => this.selected[k]);
    },
    _required: {},
    setRequired(required) { this._required = required; },
    select(group, value, adjustment = 0) {
        this.selected[group] = { value, adjustment };
    },
    isSelected(group, value) {
        return this.selected[group]?.value === value;
    },
    get totalAdjustment() {
        return Object.values(this.selected).reduce((s, o) => s + (o.adjustment || 0), 0);
    },
    reset() { this.selected = {}; },
}));

/* ----- Countdown Timer ----- */
Alpine.data('countdown', (targetDate) => ({
    days: 0, hours: 0, minutes: 0, seconds: 0,
    finished: false,
    init() {
        this._target = new Date(targetDate).getTime();
        this._tick();
        this._interval = setInterval(() => this._tick(), 1000);
    },
    destroy() { clearInterval(this._interval); },
    _tick() {
        const diff = this._target - Date.now();
        if (diff <= 0) {
            this.finished = true;
            clearInterval(this._interval);
            return;
        }
        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
        this.hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        this.minutes = Math.floor((diff / (1000 * 60)) % 60);
        this.seconds = Math.floor((diff / 1000) % 60);
    },
}));

/* ----- Carousel/Slider ----- */
Alpine.data('carousel', (totalSlides, options = {}) => ({
    active: 0,
    total: totalSlides,
    autoplay: options.autoplay || false,
    interval: options.interval || 5000,
    timer: null,
    init() {
        if (this.autoplay) this.startAutoplay();
    },
    next() {
        this.active = (this.active + 1) % this.total;
        if (this.autoplay) { this.stopAutoplay(); this.startAutoplay(); }
    },
    prev() {
        this.active = (this.active - 1 + this.total) % this.total;
        if (this.autoplay) { this.stopAutoplay(); this.startAutoplay(); }
    },
    goTo(i) {
        this.active = i;
        if (this.autoplay) { this.stopAutoplay(); this.startAutoplay(); }
    },
    startAutoplay() {
        this.stopAutoplay();
        this.timer = setInterval(() => { this.active = (this.active + 1) % this.total; }, this.interval);
    },
    stopAutoplay() {
        if (this.timer) { clearInterval(this.timer); this.timer = null; }
    },
    pause() { this.stopAutoplay(); },
    resume() { if (this.autoplay) this.startAutoplay(); },
}));

/* ----- Form Validator ----- */
Alpine.data('formValidator', (rules = {}) => ({
    errors: {},
    validate(data) {
        this.errors = {};
        for (const [field, validators] of Object.entries(rules)) {
            for (const validator of validators) {
                const error = validator(data[field], data);
                if (error) {
                    this.errors[field] = error;
                    break;
                }
            }
        }
        return Object.keys(this.errors).length === 0;
    },
    hasError(field) { return !!this.errors[field]; },
    errorFor(field) { return this.errors[field] || ''; },
    clear() { this.errors = {}; },
}));

/* ----- Copy to Clipboard ----- */
Alpine.data('clipboard', (text) => ({
    copied: false,
    async copy() {
        try {
            await navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        } catch (e) {
            console.error('Copy failed:', e);
        }
    },
}));

/* ----- Loading Button ----- */
Alpine.data('asyncButton', (callback) => ({
    loading: false,
    async run() {
        if (this.loading) return;
        this.loading = true;
        try {
            await callback.call(this);
        } finally {
            this.loading = false;
        }
    },
}));

/* ----- Product Card (Add to Cart) ----- */
Alpine.data('productCard', (productId, inStock = true) => ({
    loading: false,
    added: false,

    async addToCart() {
        if (!inStock || this.loading || this.added) return;
        this.loading = true;
        try {
            const res = await Alpine.store('cart').add(productId, 1);
            if (res?.ok) {
                this.added = true;
                setTimeout(() => { this.added = false; }, 2500);
            }
        } catch (e) {
            // Error toast is handled by the cart store
        } finally {
            this.loading = false;
        }
    },
}));

console.log('✓ Alpine.js components registered');
