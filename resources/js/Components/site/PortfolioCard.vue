<script setup>
import { ArrowUpRight, ImageIcon } from '@lucide/vue';
import { gradient } from '@/lib/colors';

defineProps({
    item: { type: Object, required: true },
    index: { type: Number, default: 0 },
});
</script>

<template>
    <Link
        v-reveal="{ type: 'zoom', delay: (index % 3) * 100 }"
        :href="route('portfolio.show', item.slug)"
        class="group block"
        data-cursor
    >
        <div class="card-brutal relative aspect-[4/3] overflow-hidden group-hover:-translate-y-1 group-hover:shadow-brutal-lg">
            <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy" />
            <div v-else :class="gradient[item.color] || gradient.violet" class="relative grid h-full w-full place-items-center bg-gradient-to-br">
                <div class="dot-bg absolute inset-0 opacity-40"></div>
                <div class="relative w-3/4 rounded-2xl border-2 border-zinc-950 bg-white p-3 shadow-[4px_4px_0_#09090b] transition-transform duration-500 group-hover:-rotate-3 group-hover:scale-105">
                    <div class="mb-2 flex gap-1"><span class="h-2 w-2 rounded-full bg-rose-400"></span><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="h-2 w-2 rounded-full bg-lime-400"></span></div>
                    <div class="space-y-1.5">
                        <div class="h-2 w-2/3 rounded bg-zinc-800"></div>
                        <div class="h-2 w-full rounded bg-zinc-200"></div>
                        <div class="h-2 w-5/6 rounded bg-zinc-200"></div>
                        <div class="mt-2 grid grid-cols-3 gap-1.5"><div class="h-8 rounded bg-violet-200"></div><div class="h-8 rounded bg-fuchsia-200"></div><div class="h-8 rounded bg-orange-200"></div></div>
                    </div>
                    <ImageIcon class="absolute -top-3 -right-3 h-7 w-7 rounded-full border-2 border-zinc-950 bg-yellow-300 p-1 text-zinc-900" />
                </div>
            </div>
            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-zinc-950/80 via-zinc-950/0 to-transparent p-5 opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-zinc-900">Lihat studi kasus <ArrowUpRight class="h-3.5 w-3.5" /></span>
            </div>
        </div>
        <div class="mt-4 flex items-start justify-between gap-3 px-1">
            <div class="min-w-0">
                <p class="text-xs font-bold tracking-wider text-violet-600 uppercase dark:text-violet-300">{{ item.category }}</p>
                <h3 class="mt-1 truncate font-display text-lg font-bold">{{ item.title }}</h3>
                <p class="line-clamp-2 text-sm text-muted">{{ item.excerpt }}</p>
            </div>
            <span v-if="item.year" class="chip shrink-0">{{ item.year }}</span>
        </div>
    </Link>
</template>
