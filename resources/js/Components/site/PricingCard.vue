<script setup>
import { ArrowRight, Check, Clock, Flame } from '@lucide/vue';
import { computed } from 'vue';
import { gradient } from '@/lib/colors';
import { rupiah } from '@/lib/format';

const props = defineProps({
    pkg: { type: Object, required: true },
    index: { type: Number, default: 0 },
});
const discount = computed(() => (props.pkg.original_price && props.pkg.price ? Math.round((1 - props.pkg.price / props.pkg.original_price) * 100) : 0));
</script>

<template>
    <div v-reveal="{ type: 'up', delay: index * 100 }" class="relative h-full">
        <div v-if="pkg.is_popular" class="animate-gradient bg-brand-gradient absolute -inset-[3px] rounded-[30px] opacity-90 blur-[1px]"></div>
        <div
            :class="pkg.is_popular ? 'border-transparent' : 'border-ink'"
            class="group relative flex h-full flex-col rounded-[28px] border-2 bg-card p-7 shadow-brutal transition-all duration-300 hover:-translate-y-2 hover:shadow-brutal-lg"
        >
            <div v-if="pkg.is_popular" class="absolute -top-4 left-1/2 inline-flex -translate-x-1/2 items-center gap-1 rounded-full border-2 border-ink bg-yellow-300 px-3 py-1 text-xs font-extrabold text-zinc-900 shadow-brutal-sm">
                <Flame class="h-3.5 w-3.5" /> PALING LARIS
            </div>
            <div class="flex items-center justify-between">
                <div :class="gradient[pkg.color] || gradient.violet" class="rounded-xl bg-gradient-to-r px-3 py-1 text-sm font-extrabold text-white">{{ pkg.name }}</div>
                <span v-if="discount > 0" class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-extrabold text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">Hemat {{ discount }}%</span>
            </div>
            <p class="mt-4 min-h-10 text-sm text-muted">{{ pkg.description }}</p>
            <div class="mt-5">
                <p v-if="pkg.original_price && pkg.price" class="text-sm text-muted line-through">{{ rupiah(pkg.original_price) }}</p>
                <p v-if="pkg.price > 0" class="font-display text-4xl font-bold tracking-tight">{{ rupiah(pkg.price) }}</p>
                <p v-else class="font-display text-3xl font-bold tracking-tight">Hubungi Kami</p>
                <p class="mt-1 text-xs font-semibold text-muted">{{ pkg.price_note || 'sekali bayar / proyek' }}</p>
            </div>
            <p v-if="pkg.delivery_time" class="mt-4 inline-flex items-center gap-1.5 self-start rounded-full bg-surface-2 px-3 py-1 text-xs font-bold"><Clock class="h-3.5 w-3.5" /> {{ pkg.delivery_time }}</p>
            <ul class="mt-6 flex-1 space-y-2.5">
                <li v-for="f in pkg.features || []" :key="f" class="flex items-start gap-2.5 text-sm">
                    <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15"><Check class="h-3.5 w-3.5" /></span>
                    {{ f }}
                </li>
            </ul>
            <Link
                v-magnetic="0.15"
                :href="route('order.create', { paket: pkg.slug })"
                :class="pkg.is_popular ? 'btn-primary shine' : 'btn-brutal'"
                class="btn btn-lg mt-8 w-full"
                data-cursor
            >
                Pilih {{ pkg.name }} <ArrowRight class="h-4 w-4 transition group-hover:translate-x-1" />
            </Link>
        </div>
    </div>
</template>
