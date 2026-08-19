/* ====================================================================
   CART AJAX COMPONENT & HELPER
   Laravel 13 + Vite
   ==================================================================== */

/**
 * Add product to cart via Fetch API
 * @param {number|string} productId 
 * @param {number} quantity 
 * @param {object} options 
 */
export async function addToCart(productId, quantity = 1, options = {}) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const response = await fetch('/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf || '',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                ...options,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success !== false) {
            // Update reactive Alpine cart store if present
            if (window.Alpine && window.Alpine.store('cart')) {
                const store = window.Alpine.store('cart');
                if (data.cart_count !== undefined) store.count = data.cart_count;
                if (data.cart) store._updateFromResponse(data);
            }

            // Update DOM counter badges directly
            updateCartCounter(data.cart_count);

            // Trigger Toast Notification
            showToast('success', data.message || 'تمت إضافة المنتج إلى السلة');

            return { ok: true, data };
        } else {
            showToast('error', data.message || 'تعذر إضافة المنتج للسلة');
            return { ok: false, data };
        }
    } catch (error) {
        console.error('Cart Add Error:', error);
        showToast('error', 'حدث خطأ أثناء الاتصال بالخادم');
        return { ok: false, error };
    }
}

/**
 * Update all cart counter elements in the DOM
 * @param {number} count 
 */
export function updateCartCounter(count) {
    if (count === undefined || count === null) return;
    const counters = document.querySelectorAll('.cart-count, [data-cart-count], #cart-count, #cartCount');
    counters.forEach(el => {
        el.textContent = count;
        el.classList.remove('hidden');
        // Add subtle pop animation
        el.classList.add('scale-125');
        setTimeout(() => el.classList.remove('scale-125'), 200);
    });
}

/**
 * Trigger Toast message
 */
export function showToast(type, message) {
    if (window.Alpine && window.Alpine.store('toast')) {
        window.Alpine.store('toast').show(message, type);
    } else {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white font-bold text-sm transition-all duration-300 transform translate-y-2 opacity-0 ${type === 'error' ? 'bg-red-600' : 'bg-emerald-600'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Register global helper on window object
if (typeof window !== 'undefined') {
    window.cartAdd = addToCart;
    window.updateCartCounter = updateCartCounter;
}
