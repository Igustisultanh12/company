<script setup>
import { Inbox } from '@lucide/vue';

defineProps({
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <div :class="compact ? 'py-8' : 'py-16'" class="flex flex-col items-center justify-center px-6 text-center">
        <div class="relative mb-4">
            <div class="absolute inset-0 -z-10 scale-150 rounded-full bg-gradient-to-br from-violet-300/40 to-fuchsia-300/30 blur-2xl dark:from-violet-500/20 dark:to-fuchsia-500/10"></div>
            <div class="animate-bounce-soft grid h-16 w-16 place-items-center rounded-3xl border-2 border-ink bg-card shadow-brutal-sm">
                <component :is="icon || Inbox" class="h-7 w-7 text-violet-500" />
            </div>
        </div>
        <h4 class="font-display text-lg font-bold">{{ title }}</h4>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted">{{ description }}</p>
        <div v-if="$slots.default" class="mt-5"><slot /></div>
    </div>
</template>
