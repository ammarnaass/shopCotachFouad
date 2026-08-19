/* ====================================================================
   THEME STORE — Controlled by Admin Panel Settings (Light Theme Only)
   ==================================================================== */

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: false,

        init() {
            document.documentElement.classList.remove('dark');
            try {
                localStorage.removeItem('theme');
                localStorage.removeItem('amar:theme');
            } catch (e) {}
        },

        toggle() {
            this.dark = false;
            document.documentElement.classList.remove('dark');
        },

        setDark() {
            this.dark = false;
            document.documentElement.classList.remove('dark');
        }
    });
});

