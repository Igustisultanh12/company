<script setup>
import { ArrowUpRight, Check } from '@lucide/vue';
import DynamicIcon from '@/Components/ui/DynamicIcon.vue';
import { gradient, softBg } from '@/lib/colors';

defineProps({
    service: { type: Object, required: true },
    index: { type: Number, default: 0 },
    detailed: { type: Boolean, default: false },
});
</script>

<template>
    <Link
        v-reveal="{ type: 'up', delay: (index % 3) * 90 }"
        v-tilt="6"
        :href="route('services.show', service.slug)"
        class="group card-brutal relative flex h-full flex-col overflow-hidden p-6 hover:-translate-y-1 hover:shadow-brutal-lg"
        data-cursor
    >
        <div :class="softBg[service.color] || softBg.violet" class="absolute inset-x-0 top-0 h-28 opacity-70 transition-all duration-500 group-hover:h-full"></div>
        <div class="relative flex items-start justify-between">
            <div :class="gradient[service.color] || gradient.violet" class="grid h-14 w-14 place-items-center rounded-2xl border-2 border-ink bg-gradient-to-br text-white shadow-brutal-sm transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-12">
                <DynamicIcon :name="service.icon" class="h-7 w-7" />
            </div>
            <span v-if="service.kbli_code" class="rounded-full border border-line bg-card px-2.5 py-1 text-[11px] font-bold text-muted">KBLI {{ service.kbli_code }}</span>
        </div>
        <h3 class="relative mt-6 font-display text-xl font-bold">{{ service.title }}</h3>
        <p class="relative mt-2 flex-1 text-sm leading-relaxed text-muted">{{ service.excerpt }}</p>
        <ul v-if="detailed && service.features?.length" class="relative mt-4 space-y-1.5">
            <li v-for="f in service.features.slice(0, 4)" :key="f" class="flex items-start gap-2 text-sm"><Check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" /> {{ f }}</li>
        </ul>
        <span class="relative mt-5 inline-flex items-center gap-1.5 text-sm font-bold">
            Selengkapnya
            <span class="grid h-7 w-7 place-items-center rounded-full border-2 border-ink bg-card transition-all duration-300 group-hover:rotate-45 group-hover:bg-yellow-300 group-hover:text-zinc-900"><ArrowUpRight class="h-4 w-4" /></span>
        </span>
    </Link>
</template>
