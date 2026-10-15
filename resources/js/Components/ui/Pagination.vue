<script setup>
import { computed } from 'vue';
import { number } from '@/lib/format';

const props = defineProps({
    paginator: { type: Object, required: true },
});

const links = computed(() =>
    (props.paginator?.links || []).map((l, i, arr) => ({
        ...l,
        text: i === 0 ? '‹' : i === arr.length - 1 ? '›' : l.label,
    })),
);
</script>

<template>
    <div v-if="paginator && paginator.last_page > 1" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-muted">
            Menampilkan <b class="text-ink">{{ number(paginator.from) }}–{{ number(paginator.to) }}</b> dari
            <b class="text-ink">{{ number(paginator.total) }}</b> data
        </p>
        <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">
            <template v-for="(l, i) in links" :key="i">
                <Link
                    v-if="l.url"
                    :href="l.url"
                    preserve-scroll
                    :class="l.active ? 'border-ink bg-ink text-surface' : 'border-line bg-card hover:border-violet-400 hover:text-violet-600'"
                    class="grid h-9 min-w-9 place-items-center rounded-xl border-2 px-2 text-sm font-bold transition"
                    >{{ l.text }}</Link
                >
                <span v-else class="grid h-9 min-w-9 place-items-center rounded-xl px-2 text-sm text-muted/60">{{ l.text }}</span>
            </template>
        </nav>
    </div>
</template>
