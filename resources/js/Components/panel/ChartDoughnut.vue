<script setup>
import { ArcElement, Chart, DoughnutController, Legend, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { hex } from '@/lib/colors';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const props = defineProps({ rows: { type: Array, required: true } });
const visible = computed(() => props.rows.filter((r) => r.total > 0));
const data = computed(() => ({
    labels: visible.value.map((r) => r.label),
    datasets: [{ data: visible.value.map((r) => r.total), backgroundColor: visible.value.map((r) => hex[r.color] || '#94a3b8'), borderWidth: 0, hoverOffset: 10 }],
}));
const options = { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 14 } } } };
</script>

<template>
    <div class="relative h-72">
        <Doughnut v-if="visible.length" :data="data" :options="options" />
        <p v-else class="grid h-full place-items-center text-sm text-muted">Belum ada pesanan</p>
    </div>
</template>
