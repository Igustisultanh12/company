<script setup>
import { CalendarCheck, ChevronLeft, ChevronRight, Clock, Download, MapPin, Pencil, TriangleAlert, Users } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import { solid } from '@/lib/colors';
import { dateLong, minutesToHours, time } from '@/lib/format';

const props = defineProps({ mode: String, date: String, month: String, daysInMonth: Number, daily: Array, monthly: Array, statuses: Object, workModes: Object, summary: Object });

const tab = computed({
    get: () => props.mode,
    set: (v) => router.get(route('admin.attendance.index'), { mode: v, date: props.date, month: props.month }, { preserveState: true }),
});
const shiftDate = (n) => {
    const d = new Date(props.date);
    d.setDate(d.getDate() + n);
    router.get(route('admin.attendance.index'), { mode: 'daily', date: d.toISOString().slice(0, 10) }, { preserveState: true, preserveScroll: true });
};
const setDate = (v) => router.get(route('admin.attendance.index'), { mode: 'daily', date: v }, { preserveState: true });
const setMonth = (v) => router.get(route('admin.attendance.index'), { mode: 'monthly', month: v }, { preserveState: true });

const show = ref(false);
const target = ref(null);
const form = useForm({ user_id: null, date: '', status: 'present', check_in: '', check_out: '', work_mode: 'wfo', note: '' });
const hm = (v) => (v ? time(v).replace('.', ':') : '');
const edit = (row) => {
    target.value = row.user;
    const a = row.attendance;
    form.defaults({ user_id: row.user.id, date: props.date, status: a?.status || 'present', check_in: hm(a?.check_in_at), check_out: hm(a?.check_out_at), work_mode: a?.work_mode || 'wfo', note: a?.note || '' });
    form.reset();
    form.clearErrors();
    show.value = true;
};
const save = () => form.put(route('admin.attendance.upsert'), { preserveScroll: true, onSuccess: () => (show.value = false) });
const exportUrl = computed(() => route('admin.attendance.export', { month: props.month }));
const mapUrl = (lat, lng) => `https://www.google.com/maps?q=${lat},${lng}`;
const dayCols = computed(() => Array.from({ length: props.daysInMonth }, (_, i) => i + 1));
</script>

<template>
    <Head title="Absensi" />
    <PageHeader title="Absensi Agen" subtitle="Rekap kehadiran harian & bulanan, termasuk koreksi data." :icon="CalendarCheck">
        <a :href="exportUrl" class="btn btn-secondary"><Download class="h-4 w-4" /> Rekap CSV {{ month }}</a>
    </PageHeader>

    <Tabs v-model="tab" :tabs="[{ key: 'daily', label: 'Harian' }, { key: 'monthly', label: 'Bulanan' }]" class="mb-5" />

    <template v-if="mode === 'daily'">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button type="button" class="btn btn-secondary btn-icon" aria-label="Hari sebelumnya" @click="shiftDate(-1)"><ChevronLeft class="h-4 w-4" /></button>
            <input :value="date" type="date" class="input input-sm w-auto" @change="setDate($event.target.value)" />
            <button type="button" class="btn btn-secondary btn-icon" aria-label="Hari berikutnya" @click="shiftDate(1)"><ChevronRight class="h-4 w-4" /></button>
            <p class="ml-2 font-bold">{{ dateLong(date) }}</p>
        </div>
        <div class="mb-6 grid gap-4 sm:grid-cols-4">
            <StatCard label="Agen aktif" :value="summary.agents" :icon="Users" color="violet" />
            <StatCard label="Hadir" :value="summary.present" :icon="CalendarCheck" color="emerald" />
            <StatCard label="Terlambat" :value="summary.late" :icon="TriangleAlert" color="amber" />
            <StatCard label="Cuti / Izin / Sakit" :value="summary.leave" :icon="Clock" color="sky" />
        </div>
        <div class="table-wrap">
            <table v-if="daily.length" class="table">
                <thead><tr><th>Agen</th><th>Status</th><th>Masuk</th><th>Pulang</th><th>Durasi</th><th>Mode</th><th>Catatan</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="row in daily" :key="row.user.id">
                        <td><div class="flex items-center gap-3"><Avatar :user="row.user" size="sm" /><div><p class="font-bold">{{ row.user.name }}</p><p class="text-xs text-muted">{{ row.user.position }}</p></div></div></td>
                        <td><StatusBadge v-if="row.attendance" :value="row.attendance.status" :map="statuses" /><span v-else class="text-xs font-bold text-muted">Belum absen</span></td>
                        <td class="text-sm">
                            {{ row.attendance?.check_in_at ? time(row.attendance.check_in_at) : '—' }}
                            <a v-if="row.attendance?.check_in_lat" :href="mapUrl(row.attendance.check_in_lat, row.attendance.check_in_lng)" target="_blank" rel="noopener" class="ml-1 inline-flex text-violet-500" title="Lokasi masuk"><MapPin class="h-3.5 w-3.5" /></a>
                        </td>
                        <td class="text-sm">{{ row.attendance?.check_out_at ? time(row.attendance.check_out_at) : '—' }}</td>
                        <td class="text-sm">{{ minutesToHours(row.attendance?.work_minutes) }}</td>
                        <td class="text-sm">{{ workModes[row.attendance?.work_mode] || '—' }}</td>
                        <td class="max-w-48 truncate text-xs text-muted">{{ row.attendance?.note }}</td>
                        <td class="text-right"><button type="button" class="btn btn-ghost btn-sm" aria-label="Koreksi" @click="edit(row)"><Pencil class="h-4 w-4" /></button></td>
                    </tr>
                </tbody>
            </table>
            <EmptyState v-else title="Belum ada agen aktif" />
        </div>
    </template>

    <template v-else>
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <input :value="month" type="month" class="input input-sm w-auto" @change="setMonth($event.target.value)" />
            <div class="flex flex-wrap gap-3 text-xs font-semibold">
                <span v-for="(s, k) in statuses" :key="k" class="inline-flex items-center gap-1.5"><span :class="solid[s.color]" class="h-3 w-3 rounded"></span>{{ s.label }}</span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table text-xs">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-card">Agen</th>
                        <th v-for="d in dayCols" :key="d" class="!px-1 text-center">{{ d }}</th>
                        <th>H</th><th>T</th><th>C/I/S</th><th>A</th><th>Jam</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in monthly" :key="row.user.id">
                        <td class="sticky left-0 z-10 bg-card font-bold whitespace-nowrap">{{ row.user.name }}</td>
                        <td v-for="d in dayCols" :key="d" class="!px-1 text-center">
                            <span v-if="row.days[d]" :class="solid[statuses[row.days[d]]?.color]" class="mx-auto block h-4 w-4 rounded" :title="statuses[row.days[d]]?.label"></span>
                            <span v-else class="mx-auto block h-4 w-4 rounded bg-surface-2"></span>
                        </td>
                        <td class="font-bold text-emerald-600">{{ row.summary.present + row.summary.late }}</td>
                        <td class="font-bold text-amber-600">{{ row.summary.late }}</td>
                        <td class="font-bold text-sky-600">{{ row.summary.leave + row.summary.sick + row.summary.permit }}</td>
                        <td class="font-bold text-rose-600">{{ row.summary.absent }}</td>
                        <td class="whitespace-nowrap">{{ minutesToHours(row.minutes) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>

    <Modal :show="show" :title="`Koreksi absensi: ${target?.name || ''}`" :subtitle="dateLong(form.date)" size="sm" @close="show = false">
        <div class="grid gap-4 sm:grid-cols-2">
            <FormField class="sm:col-span-2" label="Status" :error="form.errors.status"><select v-model="form.status" class="input"><option v-for="(s, k) in statuses" :key="k" :value="k">{{ s.label }}</option></select></FormField>
            <FormField label="Jam masuk" :error="form.errors.check_in"><input v-model="form.check_in" type="time" class="input" /></FormField>
            <FormField label="Jam pulang" :error="form.errors.check_out"><input v-model="form.check_out" type="time" class="input" /></FormField>
            <FormField class="sm:col-span-2" label="Mode kerja"><select v-model="form.work_mode" class="input"><option v-for="(l, k) in workModes" :key="k" :value="k">{{ l }}</option></select></FormField>
            <FormField class="sm:col-span-2" label="Catatan" :error="form.errors.note"><input v-model="form.note" type="text" class="input" /></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="show = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing" @click="save">Simpan</button>
        </template>
    </Modal>
</template>
