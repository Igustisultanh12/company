<script setup>
defineProps({
    value: { type: Number, default: 0 },
    showLabel: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
});
</script>

<template>
    <div class="flex items-center gap-3">
        <div :class="size === 'sm' ? 'h-1.5' : 'h-2.5'" class="relative w-full overflow-hidden rounded-full bg-surface-2">
            <div class="bg-brand-gradient absolute inset-y-0 left-0 rounded-full transition-[width] duration-700 ease-out" :style="{ width: `${Math.max(0, Math.min(100, value))}%` }"></div>
        </div>
        <span v-if="showLabel" class="w-10 shrink-0 text-right text-xs font-bold">{{ value }}%</span>
    </div>
</template>
