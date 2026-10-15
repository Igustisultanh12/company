<script setup>
import { computed } from 'vue';
import Badge from './Badge.vue';

const props = defineProps({
    value: { type: String, default: '' },
    map: { type: Object, default: () => ({}) },
    label: { type: String, default: '' },
});
const item = computed(() => props.map?.[props.value] || { label: props.label || props.value, color: 'slate' });
</script>

<template>
    <Badge :color="item.color" dot>{{ label || item.label }}</Badge>
</template>
