import Lenis from 'lenis';
import { onBeforeUnmount, onMounted } from 'vue';
import { prefersReducedMotion, isTouch } from '../directives/motion';

/** Smooth scrolling halus untuk halaman publik (dinonaktifkan pada perangkat sentuh / reduced motion). */
export function useLenis() {
    let lenis = null;
    let raf = null;
    onMounted(() => {
        if (prefersReducedMotion() || isTouch()) return;
        lenis = new Lenis({ duration: 1.1, smoothWheel: true, wheelMultiplier: 1 });
        const loop = (time) => {
            lenis.raf(time);
            raf = requestAnimationFrame(loop);
        };
        raf = requestAnimationFrame(loop);
        window.__lenis = lenis;
    });
    onBeforeUnmount(() => {
        cancelAnimationFrame(raf);
        lenis?.destroy();
        window.__lenis = null;
    });
}

export function scrollToTop() {
    if (window.__lenis) window.__lenis.scrollTo(0, { immediate: true });
    else window.scrollTo({ top: 0 });
}
