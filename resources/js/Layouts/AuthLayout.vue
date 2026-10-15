<script setup>
import { ArrowLeft, FileCheck, LayoutDashboard, ShieldCheck } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeToggle from '@/Components/site/ThemeToggle.vue';
import ToastHost from '@/Components/ui/ToastHost.vue';
import Logo from '@/Components/ui/Logo.vue';
import { useFlash } from '@/composables/useFlash';

const page = usePage();
const site = computed(() => page.props.site || {});
useFlash();
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-2">
        <aside class="animate-gradient bg-brand-gradient relative hidden overflow-hidden p-12 text-white lg:flex lg:flex-col">
            <div class="dot-bg absolute inset-0 opacity-25"></div>
            <div class="animate-float absolute -right-24 -bottom-24 h-96 w-96 rounded-full border-[28px] border-white/10"></div>
            <div class="animate-float-slow absolute top-1/3 -left-20 h-64 w-64 rounded-full bg-yellow-300/30 blur-3xl"></div>
            <Link :href="route('home')" class="group relative"><Logo :name="site.short_name || 'Kreatif Sky'" light /></Link>
            <div class="relative mt-auto max-w-md">
                <h1 class="font-display text-5xl leading-[1.05] font-bold">Ruang kerja digital tim {{ site.short_name || 'Kreatif Sky' }} ✨</h1>
                <p class="mt-4 text-white/80">Kelola pesanan, arsip dokumen, tanda tangan elektronik, absensi & tugas dalam satu tempat yang aman.</p>
                <div class="mt-8 grid gap-3">
                    <div v-for="(f, i) in [{ i: LayoutDashboard, t: 'Dashboard real-time' }, { i: FileCheck, t: 'TTE dengan QR verifikasi' }, { i: ShieldCheck, t: 'Login aman + 2FA' }]" :key="f.t" :style="{ animationDelay: `${i * 120}ms` }" class="animate-fade-up flex items-center gap-3 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur">
                        <component :is="f.i" class="h-5 w-5 text-yellow-300" /> <span class="font-semibold">{{ f.t }}</span>
                    </div>
                </div>
            </div>
        </aside>
        <main class="relative flex flex-col px-5 py-6 sm:px-10">
            <div class="flex items-center justify-between">
                <Link :href="route('home')" class="btn btn-ghost btn-sm"><ArrowLeft class="h-4 w-4" /> Kembali ke website</Link>
                <ThemeToggle />
            </div>
            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
                <Link :href="route('home')" class="group mb-8 lg:hidden"><Logo :name="site.short_name || 'Kreatif Sky'" /></Link>
                <slot />
            </div>
            <p class="text-center text-xs text-muted">© {{ new Date().getFullYear() }} {{ site.name }}</p>
        </main>
        <ToastHost />
    </div>
</template>
