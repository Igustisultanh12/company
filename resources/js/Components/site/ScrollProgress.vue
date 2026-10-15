<script setup>
import { ArrowUp } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const progress = ref(0);
const showTop = ref(false);

const onScroll = () => {
    const h = document.documentElement;
    const max = h.scrollHeight - h.clientHeight;
    progress.value = max > 0 ? (h.scrollTop / max) * 100 : 0;
    showTop.value = h.scrollTop > 700;
};
const toTop = () => {
    if (window.__lenis) window.__lenis.scrollTo(0, { duration: 1.2 });
    else window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <div class="fixed inset-x-0 top-0 z-[70] h-1 bg-transparent" aria-hidden="true">
        <div class="bg-brand-gradient h-full origin-left rounded-r-full" :style="{ width: `${progress}%` }"></div>
    </div>
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition duration-200" leave-to-class="opacity-0 translate-y-4">
        <button
            v-if="showTop"
            v-magnetic="0.4"
            type="button"
            class="fixed right-5 bottom-24 z-40 grid h-12 w-12 place-items-center rounded-2xl border-2 border-ink bg-card shadow-brutal-sm transition hover:bg-yellow-300 hover:text-zinc-900"
            aria-label="Kembali ke atas"
            @click="toTop"
        >
            <ArrowUp class="h-5 w-5" />
        </button>
    </Transition>
</template>
