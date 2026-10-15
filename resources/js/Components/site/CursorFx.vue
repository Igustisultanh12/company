<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { isTouch, prefersReducedMotion } from '@/directives/motion';

const dot = ref(null);
const ring = ref(null);
const enabled = ref(false);
let raf = null;
const pos = { x: -100, y: -100 };
const ringPos = { x: -100, y: -100 };

const interactive = 'a, button, [role="button"], input, textarea, select, label, [data-cursor]';

const onMove = (e) => {
    pos.x = e.clientX;
    pos.y = e.clientY;
    if (dot.value) dot.value.style.transform = `translate3d(${pos.x}px, ${pos.y}px, 0)`;
};
const onOver = (e) => {
    ring.value?.classList.toggle('is-hover', !!e.target.closest?.(interactive));
};
const onDown = () => ring.value?.classList.add('is-down');
const onUp = () => ring.value?.classList.remove('is-down');
const onLeave = () => {
    if (ring.value) ring.value.style.opacity = '0';
    if (dot.value) dot.value.style.opacity = '0';
};
const onEnter = () => {
    if (ring.value) ring.value.style.opacity = '1';
    if (dot.value) dot.value.style.opacity = '1';
};

const loop = () => {
    ringPos.x += (pos.x - ringPos.x) * 0.18;
    ringPos.y += (pos.y - ringPos.y) * 0.18;
    if (ring.value) ring.value.style.transform = `translate3d(${ringPos.x}px, ${ringPos.y}px, 0)`;
    raf = requestAnimationFrame(loop);
};

onMounted(() => {
    if (isTouch() || prefersReducedMotion()) return;
    enabled.value = true;
    window.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('mouseover', onOver, { passive: true });
    window.addEventListener('mousedown', onDown);
    window.addEventListener('mouseup', onUp);
    document.documentElement.addEventListener('mouseleave', onLeave);
    document.documentElement.addEventListener('mouseenter', onEnter);
    raf = requestAnimationFrame(loop);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    window.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseover', onOver);
    window.removeEventListener('mousedown', onDown);
    window.removeEventListener('mouseup', onUp);
    document.documentElement.removeEventListener('mouseleave', onLeave);
    document.documentElement.removeEventListener('mouseenter', onEnter);
});
</script>

<template>
    <div v-if="enabled" aria-hidden="true">
        <div ref="ring" class="cursor-ring"></div>
        <div ref="dot" class="cursor-dot"></div>
    </div>
</template>
