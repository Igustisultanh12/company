import { prefersReducedMotion } from './motion';

/**
 * v-counter="1250" — angka menghitung naik saat terlihat.
 * Opsional: v-counter="{ to: 98, suffix: '%', duration: 1600 }"
 */
const fmt = new Intl.NumberFormat('id-ID');

function parse(v) {
    if (typeof v === 'object' && v) return { to: Number(v.to) || 0, suffix: v.suffix || '', prefix: v.prefix || '', duration: v.duration || 1600 };
    return { to: Number(v) || 0, suffix: '', prefix: '', duration: 1600 };
}

export default {
    mounted(el, binding) {
        const cfg = parse(binding.value);
        const render = (n) => {
            el.textContent = `${cfg.prefix}${fmt.format(Math.round(n))}${cfg.suffix}`;
        };
        if (prefersReducedMotion() || typeof IntersectionObserver === 'undefined') {
            render(cfg.to);
            return;
        }
        render(0);
        const obs = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) return;
                obs.disconnect();
                const start = performance.now();
                const tick = (now) => {
                    const t = Math.min(1, (now - start) / cfg.duration);
                    const eased = 1 - Math.pow(1 - t, 4);
                    render(cfg.to * eased);
                    if (t < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            },
            { threshold: 0.4 },
        );
        obs.observe(el);
        el._counter = obs;
    },
    updated(el, binding) {
        const next = parse(binding.value);
        const prev = parse(binding.oldValue);
        if (next.to !== prev.to || next.suffix !== prev.suffix) {
            el._counter?.disconnect();
            el.textContent = `${next.prefix}${fmt.format(Math.round(next.to))}${next.suffix}`;
        }
    },
    unmounted(el) {
        el._counter?.disconnect();
    },
};
