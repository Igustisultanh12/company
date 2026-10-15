<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
});
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-center gap-4">
            <div v-if="icon" class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border-2 border-ink bg-yellow-300 text-zinc-900 shadow-brutal-sm">
                <component :is="icon" class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <h1 class="font-display text-2xl font-bold sm:text-3xl">{{ title }}</h1>
                <p v-if="subtitle" class="mt-0.5 text-sm text-muted">{{ subtitle }}</p>
            </div>
        </div>
        <div v-if="$slots.default" class="flex flex-wrap items-center gap-2"><slot /></div>
    </div>
</template>
