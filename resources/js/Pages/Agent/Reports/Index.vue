<script setup>
import { NotebookPen, Save, Trash } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FormField from '@/Components/ui/FormField.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { confirm } from '@/composables/useConfirm';
import { dateLong, todayLocal } from '@/lib/format';

const props = defineProps({ reports: Object, today: Object });
const moods = [
    { key: 'great', emoji: '🤩', label: 'Luar biasa' },
    { key: 'good', emoji: '😊', label: 'Baik' },
    { key: 'okay', emoji: '😐', label: 'Biasa' },
    { key: 'tired', emoji: '😴', label: 'Lelah' },
    { key: 'stressed', emoji: '😵', label: 'Tertekan' },
];
const iso = todayLocal();
const form = useForm({
    date: iso,
    summary: props.today?.summary || '',
    blockers: props.today?.blockers || '',
    plan_tomorrow: props.today?.plan_tomorrow || '',
    hours: props.today?.hours || 8,
    mood: props.today?.mood || 'good',
});
const submit = () => form.post(route('agent.reports.store'), { preserveScroll: true });
const edit = (r) => {
    Object.assign(form, { date: r.date, summary: r.summary, blockers: r.blockers || '', plan_tomorrow: r.plan_tomorrow || '', hours: r.hours, mood: r.mood || 'good' });
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
const destroy = async (r) => {
    if (await confirm({ title: 'Hapus laporan?', message: dateLong(r.date), danger: true })) router.delete(route('agent.reports.destroy', r.id), { preserveScroll: true });
};
const moodOf = (k) => moods.find((m) => m.key === k);
</script>

<template>
    <Head title="Laporan Harian" />
    <PageHeader title="Laporan Harian" subtitle="Ceritakan apa yang kamu kerjakan hari ini — singkat saja." :icon="NotebookPen" />
    <div class="grid gap-6 xl:grid-cols-5">
        <form class="card-brutal h-max p-6 xl:col-span-2" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3">
                <FormField label="Tanggal" :error="form.errors.date"><input v-model="form.date" type="date" :max="iso" class="input" /></FormField>
                <FormField label="Jam kerja" :error="form.errors.hours"><input v-model.number="form.hours" type="number" min="0" max="24" step="0.5" class="input" /></FormField>
            </div>
            <FormField class="mt-4" label="Yang dikerjakan" :error="form.errors.summary" required><textarea v-model="form.summary" rows="5" class="input" placeholder="- Membuat halaman beranda klien X&#10;- Revisi warna sesuai feedback"></textarea></FormField>
            <FormField class="mt-4" label="Kendala" :error="form.errors.blockers"><textarea v-model="form.blockers" rows="2" class="input"></textarea></FormField>
            <FormField class="mt-4" label="Rencana besok" :error="form.errors.plan_tomorrow"><textarea v-model="form.plan_tomorrow" rows="2" class="input"></textarea></FormField>
            <FormField class="mt-4" label="Mood hari ini">
                <div class="flex justify-between gap-1">
                    <button v-for="m in moods" :key="m.key" type="button" :class="form.mood === m.key ? 'scale-110 border-ink bg-yellow-300' : 'border-line opacity-70 hover:opacity-100'" class="grid flex-1 place-items-center rounded-2xl border-2 py-2 text-2xl transition" :title="m.label" @click="form.mood = m.key">{{ m.emoji }}</button>
                </div>
            </FormField>
            <button type="submit" class="btn btn-primary mt-6 w-full" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan laporan</button>
        </form>
        <div class="space-y-4 xl:col-span-3">
            <article v-for="r in reports.data" :key="r.id" class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-bold">{{ dateLong(r.date) }}</p>
                    <div class="flex items-center gap-1">
                        <span class="text-xl" :title="moodOf(r.mood)?.label">{{ moodOf(r.mood)?.emoji }}</span>
                        <span class="chip">{{ r.hours }} jam</span>
                        <button type="button" class="btn btn-ghost btn-sm" @click="edit(r)">Ubah</button>
                        <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="destroy(r)"><Trash class="h-4 w-4" /></button>
                    </div>
                </div>
                <p class="mt-3 text-sm whitespace-pre-line">{{ r.summary }}</p>
                <p v-if="r.blockers" class="mt-2 text-sm text-rose-600 dark:text-rose-400"><b>Kendala:</b> {{ r.blockers }}</p>
                <p v-if="r.plan_tomorrow" class="mt-1 text-sm text-violet-600 dark:text-violet-300"><b>Besok:</b> {{ r.plan_tomorrow }}</p>
            </article>
            <EmptyState v-if="!reports.data.length" title="Belum ada laporan" description="Laporan pertama kamu akan tampil di sini." />
            <Pagination :paginator="reports" />
        </div>
    </div>
</template>
