<script setup>
import { computed } from 'vue';
import { badge, solid } from '@/lib/colors';

const props = defineProps({
    color: { type: String, default: 'slate' },
    dot: { type: Boolean, default: false },
    size: { type: String, default: 'sm' },
});
const cls = computed(() => badge[props.color] || badge.slate);
const dotCls = computed(() => solid[props.color] || solid.slate);
</script>

<template>
    <span :class="[cls, size === 'xs' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs']" class="inline-flex items-center gap-1.5 rounded-full font-bold whitespace-nowrap">
        <span v-if="dot" :class="dotCls" class="h-1.5 w-1.5 rounded-full"></span>
        <slot />
    </span>
</template>
