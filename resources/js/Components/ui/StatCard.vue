<script setup>
import { computed } from 'vue';
import { gradient } from '@/lib/colors';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    icon: { type: [Object, Function], default: null },
    color: { type: String, default: 'violet' },
    hint: { type: String, default: '' },
    href: { type: String, default: '' },
    prefix: { type: String, default: '' },
    suffix: { type: String, default: '' },
});
const isNumber = computed(() => typeof props.value === 'number');
</script>

<template>
    <component
        :is="href ? 'Link' : 'div'"
        :href="href || undefined"
        class="group card relative overflow-hidden p-5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-300 hover:shadow-glow dark:hover:border-violet-500/40"
    >
        <div :class="gradient[color] || gradient.violet" class="absolute -top-10 -right-10 h-28 w-28 rounded-full bg-gradient-to-br opacity-15 blur-xl transition-transform duration-500 group-hover:scale-150"></div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-bold tracking-wider text-muted uppercase">{{ label }}</p>
                <p class="mt-2 font-display text-2xl font-bold sm:text-3xl">
                    <span v-if="isNumber" v-counter="{ to: value, prefix, suffix }">{{ prefix }}{{ value }}{{ suffix }}</span>
                    <span v-else>{{ value }}</span>
                </p>
                <p v-if="hint" class="mt-1 truncate text-xs text-muted">{{ hint }}</p>
            </div>
            <div v-if="icon" :class="gradient[color] || gradient.violet" class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br text-white shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>
    </component>
</template>
