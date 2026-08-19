/**
 * Hero Slider Component
 * Supports: auto-play, pause on hover/focus, prev/next, dots, touch swipe, keyboard navigation, RTL
 * Plus: per-slide transition effects (fade, slide-left, slide-right, zoom, flip)
 * and content entrance animations with staggered delays.
 */
class HeroSlider {
    constructor(element) {
        this.element = element;
        this.track = element.querySelector('.hero-slider-track');
        this.slides = Array.from(element.querySelectorAll('.hero-slide'));
        this.prevBtn = element.querySelector('.hero-prev');
        this.nextBtn = element.querySelector('.hero-next');
        this.dots = Array.from(element.querySelectorAll('.hero-dot'));

        this.currentIndex = 0;
        this.isAnimating = false;
        this.autoplayInterval = null;
        this.autoplayDelay = parseInt(element.dataset.autoplay) || 5000;
        this.pauseOnHover = element.dataset.pauseOnHover !== 'false';
        this.isPaused = false;
        this.isRtl = document.dir === 'rtl';

        this.animDuration = parseInt(element.dataset.duration) || 500;
        this.entranceStagger = parseInt(element.dataset.stagger) || 80;
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (this.prefersReducedMotion) {
            this.animDuration = 0;
            this.entranceStagger = 0;
        }

        element.style.setProperty('--hero-slide-duration', this.animDuration + 'ms');
        element.style.setProperty('--hero-content-stagger', this.entranceStagger + 'ms');

        if (this.slides.length <= 1) {
            this.disableControls();
            this.triggerEntrance(0);
            return;
        }

        this.init();
        this.triggerEntrance(0);
    }

    disableControls() {
        this.prevBtn?.classList.add('hidden');
        this.nextBtn?.classList.add('hidden');
        this.element.querySelector('.hero-dots')?.classList.add('hidden');
    }

    init() {
        this.bindEvents();
        this.startAutoplay();
        this.updateDots();
    }

    bindEvents() {
        // Prev/Next buttons
        this.prevBtn?.addEventListener('click', () => this.goToPrev());
        this.nextBtn?.addEventListener('click', () => this.goToNext());

        // Dot navigation
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });

        // Pause on hover/focus
        if (this.pauseOnHover) {
            this.element.addEventListener('mouseenter', () => this.pauseAutoplay());
            this.element.addEventListener('mouseleave', () => this.resumeAutoplay());
            this.element.addEventListener('focusin', () => this.pauseAutoplay());
            this.element.addEventListener('focusout', () => this.resumeAutoplay());
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.element.matches(':hover') && !this.element.querySelector(':focus')) return;

            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.isRtl ? this.goToNext() : this.goToPrev();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.isRtl ? this.goToPrev() : this.goToNext();
            }
        });

        // Touch swipe
        this.initTouchSwipe();

        // Visibility change - pause when tab not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoplay();
            } else {
                this.resumeAutoplay();
            }
        });
    }

    initTouchSwipe() {
        let startX = 0;
        let startY = 0;
        let isSwiping = false;

        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwiping = true;
            this.pauseAutoplay();
        }, { passive: true });

        this.track.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;
            const deltaX = e.touches[0].clientX - startX;
            const deltaY = e.touches[0].clientY - startY;

            // Only handle horizontal swipes
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 20) {
                e.preventDefault();
            }
        }, { passive: false });

        this.track.addEventListener('touchend', (e) => {
            if (!isSwiping) return;
            isSwiping = false;

            const deltaX = e.changedTouches[0].clientX - startX;
            const threshold = 50;

            if (Math.abs(deltaX) > threshold) {
                if (this.isRtl) {
                    deltaX > 0 ? this.goToPrev() : this.goToNext();
                } else {
                    deltaX > 0 ? this.goToNext() : this.goToPrev();
                }
            }

            this.resumeAutoplay();
        }, { passive: true });
    }

    resolveEffect(effect) {
        if (this.prefersReducedMotion) return 'fade';

        const rtlEffects = {
            'slide-left': 'slide-right',
            'slide-right': 'slide-left',
        };

        if (this.isRtl && rtlEffects[effect]) {
            return rtlEffects[effect];
        }

        return effect;
    }

    goToSlide(index) {
        if (this.isAnimating || index === this.currentIndex || index < 0 || index >= this.slides.length) return;

        this.isAnimating = true;
        const direction = index > this.currentIndex ? 'next' : 'prev';

        const outSlide = this.slides[this.currentIndex];
        const inSlide = this.slides[index];

        const outEffect = this.resolveEffect(outSlide.dataset.effect || 'fade');
        const inEffect = this.resolveEffect(inSlide.dataset.effect || 'fade');

        // Show incoming slide immediately (z-index, remove hidden state)
        inSlide.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
        inSlide.classList.add('opacity-100', 'z-10');
        inSlide.setAttribute('aria-hidden', 'false');

        // Apply exit animation to outgoing slide
        const exitClass = `hero-slide-exit-${outEffect}`;
        outSlide.classList.add(exitClass);

        // Apply enter animation to incoming slide
        const enterClass = `hero-slide-enter-${inEffect}`;
        inSlide.classList.add(enterClass);

        const duration = this.animDuration;

        // Wait for enter animation to complete
        const onEnterComplete = () => {
            inSlide.classList.remove(enterClass);
            inSlide.removeEventListener('animationend', onEnterComplete);

            // Hide outgoing slide after transition
            outSlide.classList.remove(exitClass, 'opacity-100', 'z-10');
            outSlide.classList.add('opacity-0', 'z-0', 'pointer-events-none');
            outSlide.setAttribute('aria-hidden', 'true');

            // Trigger content entrance on the new active slide
            this.triggerEntrance(index);

            // Ensure the incoming slide is visible without animation
            inSlide.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
            inSlide.classList.add('opacity-100', 'z-10');

            this.currentIndex = index;
            this.updateDots();

            // Reset animation lock after transition (use a small buffer)
            setTimeout(() => {
                this.isAnimating = false;
            }, Math.max(duration, 50));
        };

        if (duration === 0) {
            // Reduced motion or instant
            setTimeout(onEnterComplete, 0);
        } else {
            inSlide.addEventListener('animationend', onEnterComplete);
            // Safety timeout in case animationend doesn't fire
            setTimeout(() => {
                if (inSlide.classList.contains(enterClass)) {
                    inSlide.classList.remove(enterClass);
                    inSlide.removeEventListener('animationend', onEnterComplete);
                    onEnterComplete();
                }
            }, duration + 200);
        }
    }

    triggerEntrance(slideIndex) {
        const slide = this.slides[slideIndex];
        const content = slide.querySelector('.hero-slide-content');
        if (!content) return;

        const entranceEffect = content.dataset.entrance || 'fade-up';
        const reduced = this.prefersReducedMotion;
        const effectClass = reduced ? 'hero-content-enter-none' : `hero-content-enter-${entranceEffect}`;

        const children = content.querySelectorAll('[data-animate-index]');
        children.forEach((child) => {
            // Remove any existing animation classes
            child.classList.remove(
                'hero-content-hidden',
                'hero-content-enter-none',
                'hero-content-enter-fade-up',
                'hero-content-enter-fade-down',
                'hero-content-enter-fade-left',
                'hero-content-enter-fade-right',
                'hero-content-enter-zoom'
            );

            // Set staggered delay
            const idx = parseInt(child.dataset.animateIndex) || 0;
            child.style.animationDelay = reduced ? '0ms' : `${idx * this.entranceStagger}ms`;

            // Force reflow to reset animation, then apply the entrance class
            if (child.style.animationName) {
                child.style.animationName = 'none';
                child.offsetHeight; // eslint-disable-line no-unused-expressions
            }

            child.classList.add(effectClass);
        });
    }

    goToNext() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.goToSlide(nextIndex);
    }

    goToPrev() {
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.goToSlide(prevIndex);
    }

    updateDots() {
        this.dots.forEach((dot, index) => {
            const isActive = index === this.currentIndex;
            dot.setAttribute('aria-selected', isActive);
            dot.classList.toggle('bg-white', isActive);
            dot.classList.toggle('scale-125', isActive);
            dot.classList.toggle('bg-white/50', !isActive);
            dot.classList.toggle('hover:bg-white/75', !isActive);
        });
    }

    startAutoplay() {
        this.stopAutoplay();
        this.autoplayInterval = setInterval(() => {
            if (!this.isPaused && !document.hidden) {
                this.goToNext();
            }
        }, this.autoplayDelay);
    }

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }

    pauseAutoplay() {
        this.isPaused = true;
    }

    resumeAutoplay() {
        this.isPaused = false;
    }

    destroy() {
        this.stopAutoplay();
        // Remove event listeners would go here if needed
    }
}

// Auto-initialize all hero sliders on page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hero-slider').forEach(slider => {
        new HeroSlider(slider);
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HeroSlider;
}
