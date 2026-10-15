import { prefersReducedMotion } from './motion';

/**
 * v-reveal — animasi muncul saat elemen masuk viewport.
 * Pemakaian: v-reveal, v-reveal="'left'", v-reveal="{ type: 'zoom', delay: 150 }"
 * Modifier .once (default) / .repeat untuk animasi ulang saat keluar-masuk.
 */
let observer = null;
const repeatSet = new WeakSet();

function getObserver() {
    if (observer || typeof IntersectionObserver === 'undefined') return observer;
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                const el = entry.target;
                if (entry.isIntersecting) {
                    el.classList.add('is-revealed');
                    if (!repeatSet.has(el)) observer.unobserve(el);
                } else if (repeatSet.has(el)) {
                    el.classList.remove('is-revealed');
                }
            }
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );
    return observer;
}

function apply(el, binding) {
    const v = binding.value;
    const type = typeof v === 'string' ? v : v?.type || 'up';
    const delay = typeof v === 'object' && v ? v.delay || 0 : 0;
    el.setAttribute('data-reveal', type);
    el.style.setProperty('--reveal-delay', `${delay}ms`);
}

export default {
    mounted(el, binding) {
        apply(el, binding);
        const obs = getObserver();
        if (!obs || prefersReducedMotion()) {
            el.classList.add('is-revealed');
            return;
        }
        if (binding.modifiers.repeat) repeatSet.add(el);
        obs.observe(el);
    },
    updated(el, binding) {
        if (binding.value !== binding.oldValue) apply(el, binding);
    },
    unmounted(el) {
        observer?.unobserve(el);
    },
};
