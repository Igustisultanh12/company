<script setup>
import { BarController, BarElement, CategoryScale, Chart, Filler, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { rupiah } from '@/lib/format';

Chart.register(BarController, BarElement, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler);

const props = defineProps({ rows: { type: Array, required: true } });

const data = computed(() => ({
    labels: props.rows.map((r) => r.label),
    datasets: [
        {
            type: 'bar',
            label: 'Pendapatan',
            data: props.rows.map((r) => r.revenue),
            backgroundColor: (ctx) => {
                const { chart } = ctx;
                if (!chart.chartArea) return '#8b5cf6';
                const g = chart.ctx.createLinearGradient(0, chart.chartArea.bottom, 0, chart.chartArea.top);
                g.addColorStop(0, '#8b5cf6');
                g.addColorStop(1, '#d946ef');
                return g;
            },
            borderRadius: 10,
            maxBarThickness: 38,
            yAxisID: 'y',
        },
        {
            type: 'line',
            label: 'Pesanan',
            data: props.rows.map((r) => r.orders),
            borderColor: '#f97316',
            backgroundColor: '#f97316',
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 4,
            yAxisID: 'y1',
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { labels: { usePointStyle: true, boxWidth: 8, font: { weight: 'bold' } } },
        tooltip: { callbacks: { label: (c) => (c.dataset.yAxisID === 'y' ? ` ${rupiah(c.raw)}` : ` ${c.raw} pesanan`) } },
    },
    scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.15)' }, ticks: { callback: (v) => (v >= 1e6 ? `${v / 1e6}jt` : v) } },
        y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
        x: { grid: { display: false } },
    },
};
</script>

<template>
    <div class="h-72"><Bar :data="data" :options="options" /></div>
</template>
