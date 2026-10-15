<script setup>
import { computed } from 'vue';
import { initials } from '@/lib/format';

const props = defineProps({
    user: { type: Object, default: null },
    name: { type: String, default: '' },
    src: { type: String, default: '' },
    size: { type: String, default: 'md' },
});
const sizes = { xs: 'h-6 w-6 text-[10px]', sm: 'h-8 w-8 text-xs', md: 'h-10 w-10 text-sm', lg: 'h-14 w-14 text-lg', xl: 'h-24 w-24 text-3xl' };
const palette = ['from-violet-500 to-fuchsia-500', 'from-orange-400 to-pink-500', 'from-cyan-400 to-violet-500', 'from-lime-400 to-emerald-500', 'from-amber-400 to-orange-500', 'from-sky-400 to-indigo-500'];
const displayName = computed(() => props.user?.name || props.name || '');
const image = computed(() => props.src || props.user?.avatar_url || '');
const grad = computed(() => {
    let h = 0;
    for (const c of displayName.value) h = (h * 31 + c.charCodeAt(0)) | 0;
    return palette[Math.abs(h) % palette.length];
});
</script>

<template>
    <span :class="sizes[size] || sizes.md" class="relative inline-grid shrink-0 place-items-center overflow-hidden rounded-full font-bold ring-2 ring-card" :title="displayName">
        <img v-if="image" :src="image" :alt="displayName" class="h-full w-full object-cover" loading="lazy" />
        <span v-else :class="grad" class="grid h-full w-full place-items-center bg-gradient-to-br text-white">{{ user?.initials || initials(displayName) }}</span>
    </span>
</template>
