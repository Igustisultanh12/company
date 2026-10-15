<script setup>
import { CalendarDays, ClipboardList, Paperclip, Plus, Trash } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { confirm } from '@/composables/useConfirm';
import { date, todayLocal } from '@/lib/format';

const props = defineProps({ leaves: Object, usage: Object, types: Object, statuses: Object });
const show = ref(false);
const today = todayLocal();
const form = useForm({ type: 'leave', start_date: today, end_date: today, reason: '', attachment: null });
const days = computed(() => {
    const a = new Date(form.start_date);
    const b = new Date(form.end_date);
    return Number.isNaN(a) || Number.isNaN(b) || b < a ? 0 : Math.round((b - a) / 86400000) + 1;
});
const submit = () => form.post(route('agent.leaves.store'), { forceFormData: true, preserveScroll: true, onSuccess: () => { show.value = false; form.reset(); } });
const cancel = async (l) => {
    if (await confirm({ title: 'Batalkan pengajuan?', danger: true, confirmText: 'Ya, batalkan' })) router.delete(route('agent.leaves.destroy', l.id), { preserveScroll: true });
};
const typeColor = { leave: 'sky', sick: 'violet', permit: 'fuchsia' };
</script>

<template>
    <Head title="Cuti & Izin" />
    <PageHeader title="Cuti, Izin & Sakit" subtitle="Ajukan ketidakhadiran & pantau statusnya." :icon="ClipboardList">
        <button type="button" class="btn btn-primary" @click="show = true"><Plus class="h-4 w-4" /> Ajukan</button>
    </PageHeader>
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div v-for="(label, k) in types" :key="k" class="card p-5"><p class="text-xs font-bold text-muted uppercase">{{ label }} tahun ini</p><p class="mt-1 font-display text-3xl font-bold">{{ usage[k] || 0 }} <span class="text-base text-muted">hari</span></p></div>
    </div>
    <div v-if="leaves.data.length" class="space-y-3">
        <div v-for="l in leaves.data" :key="l.id" class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-center">
            <Badge :color="typeColor[l.type]">{{ types[l.type] }}</Badge>
            <div class="min-w-0 flex-1">
                <p class="flex items-center gap-1.5 font-bold"><CalendarDays class="h-4 w-4 text-muted" /> {{ date(l.start_date) }} – {{ date(l.end_date) }} · {{ l.days }} hari</p>
                <p class="truncate text-sm text-muted">{{ l.reason }}</p>
                <p v-if="l.review_note" class="text-xs text-rose-500">Catatan admin: {{ l.review_note }}</p>
            </div>
            <a v-if="l.has_attachment" :href="route('panel.leaves.file', l.id)" target="_blank" class="btn btn-ghost btn-sm"><Paperclip class="h-4 w-4" /></a>
            <StatusBadge :value="l.status" :map="statuses" />
            <button v-if="l.status === 'pending'" type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Batalkan" @click="cancel(l)"><Trash class="h-4 w-4" /></button>
        </div>
    </div>
    <EmptyState v-else title="Belum ada pengajuan" description="Semoga sehat selalu & tetap semangat! 💪" />
    <div class="mt-5"><Pagination :paginator="leaves" /></div>

    <Modal :show="show" title="Ajukan cuti / izin / sakit" size="md" @close="show = false">
        <div class="space-y-4">
            <FormField label="Jenis" :error="form.errors.type">
                <div class="grid grid-cols-3 gap-2"><button v-for="(label, k) in types" :key="k" type="button" :class="form.type === k ? 'border-ink bg-yellow-300 text-zinc-900' : 'border-line'" class="rounded-xl border-2 py-2 text-sm font-bold" @click="form.type = k">{{ label }}</button></div>
            </FormField>
            <div class="grid grid-cols-2 gap-3">
                <FormField label="Mulai" :error="form.errors.start_date"><input v-model="form.start_date" type="date" class="input" /></FormField>
                <FormField label="Selesai" :error="form.errors.end_date"><input v-model="form.end_date" type="date" :min="form.start_date" class="input" /></FormField>
            </div>
            <p class="text-sm font-semibold">Durasi: {{ days }} hari</p>
            <FormField label="Alasan" :error="form.errors.reason" required><textarea v-model="form.reason" rows="3" class="input"></textarea></FormField>
            <FormField :label="form.type === 'sick' ? 'Surat dokter (disarankan)' : 'Lampiran (opsional)'" :error="form.errors.attachment">
                <FileDrop v-model="form.attachment" accept=".pdf,.jpg,.jpeg,.png" :max-mb="4" hint="PDF/JPG/PNG maks. 4 MB" />
            </FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="show = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing || days < 1" @click="submit">Kirim pengajuan</button>
        </template>
    </Modal>
</template>
