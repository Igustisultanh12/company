<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';
import CursorFx from '@/Components/site/CursorFx.vue';
import FloatingWhatsApp from '@/Components/site/FloatingWhatsApp.vue';
import ScrollProgress from '@/Components/site/ScrollProgress.vue';
import SiteFooter from '@/Components/site/SiteFooter.vue';
import SiteNavbar from '@/Components/site/SiteNavbar.vue';
import ConfirmHost from '@/Components/ui/ConfirmHost.vue';
import ToastHost from '@/Components/ui/ToastHost.vue';
import { useFlash } from '@/composables/useFlash';
import { useLenis } from '@/composables/useLenis';

const page = usePage();
useFlash();
useLenis();

let off = null;
onMounted(() => {
    // Sinkronkan posisi Lenis setelah navigasi Inertia (Inertia sendiri mengatur scroll ke atas).
    off = router.on('navigate', () => requestAnimationFrame(() => window.__lenis?.scrollTo(window.scrollY, { immediate: true, force: true })));
});
onBeforeUnmount(() => off?.());
</script>

<template>
    <div class="relative min-h-screen overflow-x-clip">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-xl focus:bg-ink focus:px-4 focus:py-2 focus:text-surface">Langsung ke konten</a>
        <ScrollProgress />
        <SiteNavbar />
        <main id="konten" :key="page.component" class="page-enter">
            <slot />
        </main>
        <SiteFooter />
        <FloatingWhatsApp />
        <CursorFx />
        <ToastHost />
        <ConfirmHost />
    </div>
</template>
