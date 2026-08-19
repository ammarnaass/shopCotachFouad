/* ====================================================================
   AMAR STORE - CLIENT-SIDE JAVASCRIPT
   ==================================================================== */

import './bootstrap';

import Sortable from 'sortablejs';
window.Sortable = Sortable;

// Initialize Alpine.js (must run after DOM is ready)
import './alpine/index.js';

// Import Alpine stores (registered via alpine:init events)
import './alpine/stores/toast.js';
import './alpine/stores/cart.js';
import './alpine/stores/wishlist.js';
import './alpine/stores/theme.js';
import './alpine/stores/quickView.js';

// Import Alpine component factories (registers Alpine.data() factories)
import './alpine/components.js';

// Import page-specific Alpine components
import './pages/instant-buy.js';

// Import vanilla JS components
import './components/hero-slider.js';
import './components/cart.js';

/* ====================================================================
   GLOBAL HELPERS (vanilla JS, usable without Alpine)
   ==================================================================== */

/**
 * Format currency in Arabic locale
 */
window.formatCurrency = function (amount, symbol = 'ر.س') {
    return new Intl.NumberFormat('ar-SA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount) + ' ' + symbol;
};

/**
 * Debounce helper
 */
window.debounce = function (func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

/**
 * Throttle helper
 */
window.throttle = function (func, limit = 300) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
};

/**
 * Smooth scroll to element
 */
window.scrollTo = function (selector, offset = 80) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (el) {
        const top = el.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    }
};

/* ====================================================================
   GLOBAL FETCH WRAPPER (fallback for non-Alpine code)
   ==================================================================== */

window.apiRequest = async function (url, options = {}) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const config = {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
            ...(options.headers || {}),
        },
        credentials: 'same-origin',
    };
    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        config.body = JSON.stringify(options.body);
    } else if (options.body) {
        config.body = options.body;
    }
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        return { ok: response.ok, status: response.status, data };
    } catch (err) {
        console.error('API request failed:', err);
        return { ok: false, status: 0, data: { message: 'خطأ في الاتصال' } };
    }
};

/* ====================================================================
   GLOBAL EVENT LISTENERS
   ==================================================================== */

// Smooth scroll for anchor links
document.addEventListener('click', (e) => {
    const anchor = e.target.closest('a[href^="#"]');
    if (!anchor) return;
    const href = anchor.getAttribute('href');
    if (href === '#' || href.length < 2) return;
    const target = document.querySelector(href);
    if (target) {
        e.preventDefault();
        window.scrollTo(href);
    }
});

// Auto-dismiss flash messages
document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
    const timeout = parseInt(alert.dataset.autoDismiss, 10) || 4000;
    setTimeout(() => {
        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 300);
    }, timeout);
});

// Page loader
window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => loader.classList.add('fade-out'), 100);
        setTimeout(() => loader.remove(), 500);
    }
});

// Back to top button
const backToTop = document.getElementById('backToTop');
if (backToTop) {
    window.addEventListener('scroll', window.throttle(() => {
        if (window.scrollY > 400) {
            backToTop.classList.remove('opacity-0', 'invisible');
            backToTop.classList.add('opacity-100', 'visible');
        } else {
            backToTop.classList.add('opacity-0', 'invisible');
            backToTop.classList.remove('opacity-100', 'visible');
        }
    }, 100));
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}
// Start Alpine.js after all stores and components are registered
if (window.Alpine) {
    window.Alpine.start();
}

console.log('✓ Amar Store JS loaded (with Alpine.js)');
