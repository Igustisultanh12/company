import { isTouch, prefersReducedMotion } from './motion';

/**
 * v-magnetic — elemen (tombol) "tertarik" mengikuti kursor saat di-hover.
 * v-magnetic="0.35" untuk mengatur kekuatan.
 */
export default {
    mounted(el, binding) {
        if (isTouch() || prefersReducedMotion()) return;
        const strength = Number(binding.value) || 0.3;
        el.style.transition = 'transform 0.25s cubic-bezier(0.22, 1, 0.36, 1)';
        el.style.willChange = 'transform';

        const move = (e) => {
            const r = el.getBoundingClientRect();
            const x = e.clientX - (r.left + r.width / 2);
            const y = e.clientY - (r.top + r.height / 2);
            el.style.transform = `translate3d(${x * strength}px, ${y * strength}px, 0)`;
        };
        const leave = () => {
            el.style.transform = 'translate3d(0, 0, 0)';
        };
        el.addEventListener('mousemove', move);
        el.addEventListener('mouseleave', leave);
        el._magnetic = { move, leave };
    },
    unmounted(el) {
        if (!el._magnetic) return;
        el.removeEventListener('mousemove', el._magnetic.move);
        el.removeEventListener('mouseleave', el._magnetic.leave);
    },
};
