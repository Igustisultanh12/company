<script setup>
import { computed } from 'vue';

const props = defineProps({ value: { type: String, default: '' } });
const score = computed(() => {
    const v = props.value || '';
    let s = 0;
    if (v.length >= 8) s++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
    return v ? s : 0;
});
const labels = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'];
const colors = ['bg-line', 'bg-rose-500', 'bg-amber-400', 'bg-lime-500', 'bg-emerald-500'];
</script>

<template>
    <div v-if="value">
        <div class="flex gap-1.5">
            <span v-for="i in 4" :key="i" :class="i <= score ? colors[score] : 'bg-line'" class="h-1.5 flex-1 rounded-full transition-colors duration-300"></span>
        </div>
        <p class="mt-1 text-xs font-semibold text-muted">Kekuatan: {{ labels[score] }}</p>
    </div>
</template>
