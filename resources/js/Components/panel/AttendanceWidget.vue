<script setup>
import { Clock, LogIn, LogOut, MapPin, MapPinOff } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { toast } from '@/composables/useToast';
import { minutesToHours, time } from '@/lib/format';

const props = defineProps({
    today: { type: Object, default: null },
    workHours: { type: Object, default: () => ({ start: '08:00', end: '16:00' }) },
    workModes: { type: Object, default: () => ({ wfo: 'Kantor (WFO)', wfh: 'Rumah (WFH)', field: 'Lapangan / Klien' }) },
    requireLocation: { type: Boolean, default: false },
});

const now = ref(new Date());
const mode = ref(props.today?.work_mode || 'wfo');
const busy = ref(false);
const located = ref(null);
let timer = null;
onMounted(() => (timer = setInterval(() => (now.value = new Date()), 1000)));
onBeforeUnmount(() => clearInterval(timer));

const clock = computed(() => now.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
const state = computed(() => (!props.today?.check_in_at ? 'in' : !props.today?.check_out_at ? 'out' : 'done'));
const leaveStatus = computed(() => ['leave', 'sick', 'permit'].includes(props.today?.status) && !props.today?.check_in_at);

const locate = () =>
    new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);
        navigator.geolocation.getCurrentPosition(
            (p) => resolve({ lat: p.coords.latitude, lng: p.coords.longitude }),
            () => resolve(null),
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 },
        );
    });

const submit = async () => {
    busy.value = true;
    const loc = await locate();
    located.value = !!loc;
    if (!loc && props.requireLocation) {
        busy.value = false;
        toast('Lokasi wajib diizinkan untuk absen. Aktifkan izin lokasi di browser.', 'error');
        return;
    }
    const payload = { lat: loc?.lat ?? null, lng: loc?.lng ?? null };
    if (state.value === 'in') payload.work_mode = mode.value;
    router.post(route(state.value === 'in' ? 'agent.attendance.check-in' : 'agent.attendance.check-out'), payload, { preserveScroll: true, onFinish: () => (busy.value = false) });
};
</script>

<template>
    <div class="card-brutal relative overflow-hidden p-6">
        <div class="bg-brand-gradient absolute -top-16 -right-16 h-40 w-40 rounded-full opacity-20 blur-2xl"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold tracking-wider text-muted uppercase">Absensi hari ini</p>
                <p class="mt-1 font-display text-4xl font-bold tabular-nums">{{ clock }}</p>
                <p class="text-xs text-muted">Jam kerja {{ workHours.start }} – {{ workHours.end }} WIB</p>
            </div>
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-300 text-zinc-900"><Clock class="h-6 w-6" /></span>
        </div>

        <div class="relative mt-5 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl bg-surface-2 p-3"><p class="text-xs text-muted">Masuk</p><p class="font-bold">{{ today?.check_in_at ? time(today.check_in_at) : '—' }}</p></div>
            <div class="rounded-2xl bg-surface-2 p-3"><p class="text-xs text-muted">Pulang</p><p class="font-bold">{{ today?.check_out_at ? time(today.check_out_at) : '—' }}</p></div>
        </div>

        <div v-if="leaveStatus" class="relative mt-5 rounded-2xl bg-sky-50 p-4 text-sm font-semibold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">Hari ini kamu tercatat cuti/izin. Selamat beristirahat 🌴</div>
        <template v-else-if="state !== 'done'">
            <div v-if="state === 'in'" class="relative mt-5 grid grid-cols-3 gap-2">
                <button v-for="(l, k) in workModes" :key="k" type="button" :class="mode === k ? 'border-ink bg-ink text-surface' : 'border-line'" class="rounded-xl border-2 px-2 py-2 text-xs font-bold transition" @click="mode = k">{{ l }}</button>
            </div>
            <button v-magnetic="0.15" v-ripple type="button" :class="state === 'in' ? 'btn-primary shine' : 'btn-dark'" class="btn btn-lg relative mt-4 w-full" :disabled="busy" @click="submit">
                <component :is="state === 'in' ? LogIn : LogOut" class="h-5 w-5" />
                {{ busy ? 'Mengambil lokasi…' : state === 'in' ? 'Absen Masuk' : 'Absen Pulang' }}
            </button>
            <p class="relative mt-2 flex items-center justify-center gap-1 text-xs text-muted">
                <component :is="located === false ? MapPinOff : MapPin" class="h-3.5 w-3.5" />
                {{ located === false ? 'Lokasi tidak diizinkan' : requireLocation ? 'Lokasi GPS wajib' : 'Lokasi GPS dicatat bila diizinkan' }}
            </p>
        </template>
        <div v-else class="relative mt-5 rounded-2xl bg-emerald-50 p-4 text-center text-sm font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            Absensi hari ini lengkap ✅ · {{ minutesToHours(today.work_minutes) }} kerja
        </div>
    </div>
</template>
