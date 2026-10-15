<script setup>
import { CalendarCheck, MapPin } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AttendanceWidget from '@/Components/panel/AttendanceWidget.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { solid } from '@/lib/colors';
import { date, minutesToHours, time } from '@/lib/format';

const props = defineProps({ today: Object, history: Array, month: String, summary: Object, totalMinutes: Number, config: Object, statuses: Object, workModes: Object });
const setMonth = (v) => router.get(route('agent.attendance.index'), { month: v }, { preserveState: true });
</script>

<template>
    <Head title="Absensi" />
    <PageHeader title="Absensi" subtitle="Catat kehadiran harian & lihat riwayatmu." :icon="CalendarCheck" />
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <AttendanceWidget :today="today" :work-hours="{ start: config.start, end: config.end }" :work-modes="workModes" :require-location="config.require_location" />
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Ringkasan {{ month }}</h2>
                <ul class="mt-4 space-y-2">
                    <li v-for="(s, k) in statuses" :key="k" class="flex items-center justify-between text-sm"><span class="flex items-center gap-2"><span :class="solid[s.color]" class="h-2.5 w-2.5 rounded-full"></span>{{ s.label }}</span><b>{{ summary[k] || 0 }}</b></li>
                </ul>
                <p class="mt-4 border-t border-line pt-4 text-sm">Total jam kerja: <b>{{ minutesToHours(totalMinutes) }}</b></p>
                <p class="mt-2 text-xs text-muted">Toleransi terlambat {{ config.tolerance }} menit dari jam {{ config.start }}.</p>
            </div>
        </div>
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-line p-5">
                <h2 class="font-display text-lg font-bold">Riwayat</h2>
                <input :value="month" type="month" class="input input-sm w-auto" @change="setMonth($event.target.value)" />
            </div>
            <div v-if="history.length" class="table-wrap !rounded-none !border-0">
                <table class="table">
                    <thead><tr><th>Tanggal</th><th>Status</th><th>Masuk</th><th>Pulang</th><th>Durasi</th><th>Mode</th></tr></thead>
                    <tbody>
                        <tr v-for="h in history" :key="h.id">
                            <td class="font-semibold whitespace-nowrap">{{ date(h.date, { weekday: 'short', day: 'numeric', month: 'short' }) }}</td>
                            <td><StatusBadge :value="h.status" :map="statuses" /></td>
                            <td class="text-sm">{{ h.check_in_at ? time(h.check_in_at) : '—' }} <MapPin v-if="h.check_in_lat" class="inline h-3 w-3 text-violet-500" /></td>
                            <td class="text-sm">{{ h.check_out_at ? time(h.check_out_at) : '—' }}</td>
                            <td class="text-sm">{{ minutesToHours(h.work_minutes) }}</td>
                            <td class="text-sm">{{ workModes[h.work_mode] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState v-else compact title="Belum ada riwayat bulan ini" />
        </div>
    </div>
</template>
