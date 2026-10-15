<script setup>
import { computed } from 'vue';
import { resolveIcon } from '@/lib/icons';

const props = defineProps({ name: { type: String, default: 'Sparkles' } });
const icon = computed(() => resolveIcon(props.name));
</script>

<template>
    <component :is="icon" />
</template>
