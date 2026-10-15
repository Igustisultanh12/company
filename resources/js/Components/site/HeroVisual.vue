<script setup>
import { BellRing, ChartLine, CircleCheck, QrCode, Rocket, Star } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { isTouch, prefersReducedMotion } from '@/directives/motion';

const root = ref(null);
const px = ref(0);
const py = ref(0);

const onMove = (e) => {
    const r = root.value?.getBoundingClientRect();
    if (!r) return;
    px.value = (e.clientX - (r.left + r.width / 2)) / r.width;
    py.value = (e.clientY - (r.top + r.height / 2)) / r.height;
};
onMounted(() => {
    if (!isTouch() && !prefersReducedMotion()) window.addEventListener('mousemove', onMove, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('mousemove', onMove));

const layer = (depth) => ({ transform: `translate3d(${px.value * depth}px, ${py.value * depth}px, 0)` });
</script>

<template>
    <div ref="root" class="relative mx-auto aspect-square w-full max-w-[540px]" aria-hidden="true">
        <div class="animate-spin-slow absolute inset-6 rounded-full border-2 border-dashed border-violet-300/70 dark:border-violet-500/30"></div>
        <div class="absolute inset-16 rounded-full bg-gradient-to-br from-violet-500/20 via-fuchsia-500/20 to-orange-400/20 blur-2xl"></div>

        <!-- Browser mockup -->
        <div class="absolute inset-x-6 top-[18%] transition-transform duration-300 ease-out sm:inset-x-10" :style="layer(-18)">
            <div class="rotate-[-3deg] overflow-hidden rounded-3xl border-2 border-ink bg-card shadow-brutal-lg">
                <div class="flex items-center gap-1.5 border-b-2 border-ink bg-yellow-300 px-4 py-2.5">
                    <span class="h-3 w-3 rounded-full border-2 border-zinc-950 bg-rose-400"></span>
                    <span class="h-3 w-3 rounded-full border-2 border-zinc-950 bg-white"></span>
                    <span class="h-3 w-3 rounded-full border-2 border-zinc-950 bg-lime-400"></span>
                    <span class="ml-3 h-5 flex-1 rounded-full border-2 border-zinc-950 bg-white px-2 text-[10px] leading-4 font-bold text-zinc-500">bisnis-kamu.id</span>
                </div>
                <div class="space-y-3 p-5">
                    <div class="h-3 w-1/3 rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500"></div>
                    <div class="h-6 w-4/5 rounded-lg bg-ink/90"></div>
                    <div class="h-6 w-3/5 rounded-lg bg-ink/90"></div>
                    <div class="h-2.5 w-full rounded-full bg-line"></div>
                    <div class="h-2.5 w-11/12 rounded-full bg-line"></div>
                    <div class="flex gap-2 pt-1">
                        <div class="bg-brand-gradient h-8 w-28 rounded-xl"></div>
                        <div class="h-8 w-20 rounded-xl border-2 border-line"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-2">
                        <div class="h-16 rounded-xl bg-violet-100 dark:bg-violet-500/20"></div>
                        <div class="h-16 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-500/20"></div>
                        <div class="h-16 rounded-xl bg-orange-100 dark:bg-orange-500/20"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating cards -->
        <div class="absolute top-[6%] -left-1 transition-transform duration-300 ease-out sm:left-0" :style="layer(26)">
            <div class="animate-float flex items-center gap-2.5 rounded-2xl border-2 border-ink bg-card px-3.5 py-2.5 shadow-brutal-sm">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-lime-300 text-zinc-900"><Rocket class="h-5 w-5" /></span>
                <span><span class="block text-[11px] text-muted">Website live</span><span class="block text-sm font-extrabold">Super cepat ⚡</span></span>
            </div>
        </div>
        <div class="absolute top-[8%] right-0 transition-transform duration-300 ease-out" :style="layer(34)">
            <div class="animate-float-slow rounded-2xl border-2 border-ink bg-card p-3 shadow-brutal-sm">
                <div class="flex gap-0.5 text-amber-400"><Star v-for="n in 5" :key="n" class="h-4 w-4 fill-current" /></div>
                <p class="mt-1 text-[11px] font-bold">Desain kekinian ✨</p>
            </div>
        </div>
        <div class="absolute bottom-[12%] -left-2 transition-transform duration-300 ease-out sm:left-2" :style="layer(30)">
            <div class="animate-float-slow flex items-center gap-2.5 rounded-2xl border-2 border-ink bg-card px-3.5 py-2.5 shadow-brutal-sm [animation-delay:-4s]">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-fuchsia-500 text-white"><QrCode class="h-5 w-5" /></span>
                <span><span class="block text-[11px] text-muted">Dokumen TTE</span><span class="flex items-center gap-1 text-sm font-extrabold">Terverifikasi <CircleCheck class="h-4 w-4 text-emerald-500" /></span></span>
            </div>
        </div>
        <div class="absolute right-0 bottom-[6%] transition-transform duration-300 ease-out sm:right-2" :style="layer(22)">
            <div class="animate-float rounded-2xl border-2 border-ink bg-card p-3.5 shadow-brutal-sm [animation-delay:-2s]">
                <div class="flex items-center gap-2 text-xs font-bold"><ChartLine class="h-4 w-4 text-violet-500" /> Pengunjung naik</div>
                <div class="mt-2 flex h-12 items-end gap-1">
                    <span v-for="(h, i) in [30, 45, 38, 60, 52, 75, 95]" :key="i" :style="{ height: `${h}%` }" class="bg-brand-gradient w-3 rounded-t-md"></span>
                </div>
            </div>
        </div>
        <div class="absolute top-1/2 -right-2 transition-transform duration-300 ease-out" :style="layer(40)">
            <span class="animate-bounce-soft grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-cyan-300 text-zinc-900 shadow-brutal-sm"><BellRing class="h-5 w-5" /></span>
        </div>
    </div>
</template>
