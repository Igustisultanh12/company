import { isTouch, prefersReducedMotion } from './motion';

/**
 * v-tilt — efek kartu 3D miring mengikuti kursor + kilau (glare).
 * v-tilt="8" = derajat maksimal.
 */
export default {
    mounted(el, binding) {
        if (isTouch() || prefersReducedMotion()) return;
        const max = Number(binding.value) || 8;
        el.classList.add('tilt');
        if (getComputedStyle(el).position === 'static') el.style.position = 'relative';
        const glare = document.createElement('span');
        glare.className = 'tilt-glare';
        el.appendChild(glare);

        let raf = null;
        const move = (e) => {
            const r = el.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width;
            const py = (e.clientY - r.top) / r.height;
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(() => {
                el.style.transform = `perspective(900px) rotateX(${(0.5 - py) * max}deg) rotateY(${(px - 0.5) * max}deg) translateZ(0)`;
                el.style.setProperty('--gx', `${px * 100}%`);
                el.style.setProperty('--gy', `${py * 100}%`);
            });
        };
        const leave = () => {
            cancelAnimationFrame(raf);
            el.style.transform = '';
        };
        el.addEventListener('mousemove', move);
        el.addEventListener('mouseleave', leave);
        el._tilt = { move, leave, glare };
    },
    unmounted(el) {
        if (!el._tilt) return;
        el.removeEventListener('mousemove', el._tilt.move);
        el.removeEventListener('mouseleave', el._tilt.leave);
        el._tilt.glare.remove();
    },
};
