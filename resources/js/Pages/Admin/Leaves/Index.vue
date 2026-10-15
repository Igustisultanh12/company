<script setup>
import { CalendarDays, Check, ClipboardList, Paperclip, X } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import { confirm } from '@/composables/useConfirm';
import { date, relative } from '@/lib/format';

const props = defineProps({ leaves: Object, status: String, counts: Object, types: Object, statuses: Object });
const tab = computed({
    get: () => props.status,
    set: (v) => router.get(route('admin.leaves.index'), { status: v }, { preserveState: true }),
});
const tabs = computed(() => [
    { key: 'pending', label: 'Menunggu', count: props.counts.pending || 0 },
    { key: 'approved', label: 'Disetujui', count: props.counts.approved || 0 },
    { key: 'rejected', label: 'Ditolak', count: props.counts.rejected || 0 },
    { key: 'all', label: 'Semua' },
]);
const typeColor = { leave: 'sky', sick: 'violet', permit: 'fuchsia' };

const approve = async (l) => {
    if (await confirm({ title: 'Setujui pengajuan?', message: `${l.user.name} · ${l.days} hari. Absensi pada tanggal tersebut akan otomatis tercatat.` })) {
        router.put(route('admin.leaves.review', l.id), { status: 'approved' }, { preserveScroll: true });
    }
};
const reject = async (l) => {
    const note = await confirm({ title: 'Tolak pengajuan?', danger: true, confirmText: 'Tolak', input: { label: 'Alasan penolakan', required: true, textarea: true } });
    if (note) router.put(route('admin.leaves.review', l.id), { status: 'rejected', review_note: note }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Cuti & Izin" />
    <PageHeader title="Cuti, Izin & Sakit" subtitle="Tinjau pengajuan dari agen." :icon="ClipboardList" />
    <Tabs v-model="tab" :tabs="tabs" class="mb-5" />
    <div v-if="leaves.data.length" class="grid gap-4 lg:grid-cols-2">
        <div v-for="l in leaves.data" :key="l.id" class="card p-5">
            <div class="flex items-start gap-3">
                <Avatar :user="l.user" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="font-bold">{{ l.user.name }}</p>
                    <p class="text-xs text-muted">{{ l.user.position }} · diajukan {{ relative(l.created_at) }}</p>
                </div>
                <StatusBadge :value="l.status" :map="statuses" />
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <Badge :color="typeColor[l.type]">{{ types[l.type] }}</Badge>
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold"><CalendarDays class="h-4 w-4 text-muted" /> {{ date(l.start_date) }} – {{ date(l.end_date) }} ({{ l.days }} hari)</span>
            </div>
            <p class="mt-3 rounded-2xl bg-surface-2/70 p-3 text-sm whitespace-pre-line">{{ l.reason }}</p>
            <a v-if="l.has_attachment" :href="route('panel.leaves.file', l.id)" target="_blank" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-violet-600 hover:underline"><Paperclip class="h-4 w-4" /> Lihat lampiran</a>
            <p v-if="l.review_note" class="mt-3 text-sm text-muted">Catatan: {{ l.review_note }} <span v-if="l.reviewer">— {{ l.reviewer.name }}</span></p>
            <div v-if="l.status === 'pending'" class="mt-4 flex gap-2">
                <button type="button" class="btn btn-success btn-sm" @click="approve(l)"><Check class="h-4 w-4" /> Setujui</button>
                <button type="button" class="btn btn-secondary btn-sm text-rose-500" @click="reject(l)"><X class="h-4 w-4" /> Tolak</button>
            </div>
        </div>
    </div>
    <EmptyState v-else title="Tidak ada pengajuan" description="Pengajuan cuti/izin/sakit dari agen akan muncul di sini." />
    <div class="mt-5"><Pagination :paginator="leaves" /></div>
</template>
