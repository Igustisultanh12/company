<script setup>
import { ChevronRight } from '@lucide/vue';
import Blobs from './Blobs.vue';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    highlight: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    crumbs: { type: Array, default: () => [] },
});
</script>

<template>
    <section class="relative isolate overflow-hidden pt-32 pb-14 sm:pt-40 sm:pb-20">
        <Blobs variant="soft" />
        <div class="container-x text-center">
            <nav v-if="crumbs.length" v-reveal="'down'" class="mb-6 flex flex-wrap items-center justify-center gap-1.5 text-xs font-semibold text-muted" aria-label="Breadcrumb">
                <Link :href="route('home')" class="hover:text-ink">Beranda</Link>
                <template v-for="c in crumbs" :key="c.label">
                    <ChevronRight class="h-3.5 w-3.5" />
                    <Link v-if="c.href" :href="c.href" class="hover:text-ink">{{ c.label }}</Link>
                    <span v-else class="text-ink">{{ c.label }}</span>
                </template>
            </nav>
            <span v-if="eyebrow" v-reveal="'zoom'" class="eyebrow">{{ eyebrow }}</span>
            <h1 v-reveal="{ type: 'up', delay: 80 }" class="mx-auto mt-5 max-w-4xl font-display text-4xl leading-[1.05] font-bold sm:text-6xl">
                {{ title }} <span v-if="highlight" class="text-gradient">{{ highlight }}</span>
            </h1>
            <p v-if="subtitle" v-reveal="{ type: 'up', delay: 160 }" class="mx-auto mt-5 max-w-2xl text-lg text-muted">{{ subtitle }}</p>
            <div v-if="$slots.default" v-reveal="{ type: 'up', delay: 240 }" class="mt-8"><slot /></div>
        </div>
    </section>
</template>
