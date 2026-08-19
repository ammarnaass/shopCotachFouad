{{--
    Alpine.js Global Components
    Loaded on every page via the layout.
    Uses $store.toast and global stores.
--}}

{{-- Toast Notifications (fixed top-left) --}}
<div x-data x-cloak>
    <div x-data
         x-on:toast.window="$store.toast.show($event.detail.message, $event.detail.type, $event.detail.duration)"
         class="fixed top-24 left-4 z-[9999] space-y-2 pointer-events-none"
         style="max-width: 22rem;">
        <template x-for="toast in $store.toast.items" :key="toast.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 :class="{
                    'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                    'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                    'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning',
                    'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info',
                 }"
                 class="pointer-events-auto border rounded-xl p-4 flex items-start gap-3 shadow-soft-lg"
                 role="alert">
                <span class="material-symbols-outlined mt-0.5 flex-shrink-0"
                      :class="{
                          'text-green-500': toast.type === 'success',
                          'text-red-500': toast.type === 'error',
                          'text-amber-500': toast.type === 'warning',
                          'text-blue-500': toast.type === 'info',
                      }"
                      x-text="toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'warning' : toast.type === 'warning' ? 'warning' : 'info'"></span>
                <span x-text="toast.message" class="flex-1 text-sm font-medium"></span>
                <button @click="$store.toast.dismiss(toast.id)"
                        class="flex-shrink-0 opacity-60 hover:opacity-100 transition"
                        aria-label="{{ __t('ui.close') }}">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </template>
    </div>
</div>

{{-- Quick View Modal (toggled by global $store.quickView) --}}
<div x-data
     x-show="$store.quickView?.open ?? false"
     x-cloak
     @keydown.escape.window="$store.quickView.close()"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4"
     style="display: none;">
    {{-- Backdrop --}}
    <div x-show="$store.quickView?.open ?? false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.quickView.close()"
         class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    {{-- Modal content --}}
    <div x-show="$store.quickView?.open ?? false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="$store.quickView.close()"
         class="relative bg-white text-on-surface rounded-2xl shadow-soft-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
         x-html="$store.quickView?.html ?? ''">
    </div>
</div>

{{-- Back to Top Button --}}
<button id="backToTop"
        x-data
        x-init="
            $el.classList.add('opacity-0', 'invisible', 'transition-all', 'duration-300');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 400) {
                    $el.classList.remove('opacity-0', 'invisible');
                    $el.classList.add('opacity-100', 'visible');
                } else {
                    $el.classList.add('opacity-0', 'invisible');
                    $el.classList.remove('opacity-100', 'visible');
                }
            });
        "
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 left-6 z-50 w-12 h-12 rounded-full bg-gradient-to-br from-brand-600 to-brand-500 text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-center"
        aria-label="{{ __t('ui.back_to_top') }}">
    <span class="material-symbols-outlined">expand_less</span>
</button>
