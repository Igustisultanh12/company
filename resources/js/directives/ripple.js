/**
 * v-ripple — gelombang klik ala material pada tombol.
 */
export default {
    mounted(el) {
        if (getComputedStyle(el).position === 'static') el.style.position = 'relative';
        el.style.overflow = 'hidden';
        const down = (e) => {
            const r = el.getBoundingClientRect();
            const size = Math.max(r.width, r.height);
            const wave = document.createElement('span');
            wave.className = 'ripple-wave';
            wave.style.width = wave.style.height = `${size}px`;
            wave.style.left = `${e.clientX - r.left - size / 2}px`;
            wave.style.top = `${e.clientY - r.top - size / 2}px`;
            el.appendChild(wave);
            setTimeout(() => wave.remove(), 700);
        };
        el.addEventListener('pointerdown', down);
        el._ripple = down;
    },
    unmounted(el) {
        if (el._ripple) el.removeEventListener('pointerdown', el._ripple);
    },
};
