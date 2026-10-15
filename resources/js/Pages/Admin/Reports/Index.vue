<script setup>
import { NotebookPen } from '@lucide/vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { useFilters } from '@/composables/useFilters';
import { dateLong } from '@/lib/format';

const props = defineProps({ reports: Object, users: Array, filters: Object, todayCount: Number, agentCount: Number });
const f = useFilters(props.filters, route('admin.reports.index'));
const moods = { great: '🤩 Luar biasa', good: '😊 Baik', okay: '😐 Biasa', tired: '😴 Lelah', stressed: '😵 Tertekan' };
</script>

<template>
    <Head title="Laporan Harian" />
    <PageHeader title="Laporan Harian" :subtitle="`${todayCount} dari ${agentCount} agen sudah mengisi laporan hari ini.`" :icon="NotebookPen" />
    <div class="mb-5 flex flex-col gap-3 sm:flex-row">
        <select v-model="f.user" class="input input-sm sm:w-56"><option :value="null">Semua agen</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
        <input v-model="f.date" type="date" class="input input-sm sm:w-48" />
    </div>
    <div v-if="reports.data.length" class="space-y-4">
        <article v-for="r in reports.data" :key="r.id" class="card p-5">
            <div class="flex flex-wrap items-center gap-3">
                <Avatar :user="r.user" size="md" />
                <div class="flex-1"><p class="font-bold">{{ r.user.name }}</p><p class="text-xs text-muted">{{ dateLong(r.date) }} · {{ r.hours }} jam</p></div>
                <span v-if="r.mood" class="chip">{{ moods[r.mood] }}</span>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2"><p class="text-xs font-bold text-muted uppercase">Yang dikerjakan</p><p class="mt-1 text-sm whitespace-pre-line">{{ r.summary }}</p></div>
                <div class="space-y-3">
                    <div v-if="r.blockers"><p class="text-xs font-bold text-rose-500 uppercase">Kendala</p><p class="mt-1 text-sm whitespace-pre-line">{{ r.blockers }}</p></div>
                    <div v-if="r.plan_tomorrow"><p class="text-xs font-bold text-violet-500 uppercase">Rencana besok</p><p class="mt-1 text-sm whitespace-pre-line">{{ r.plan_tomorrow }}</p></div>
                </div>
            </div>
        </article>
    </div>
    <EmptyState v-else title="Belum ada laporan" />
    <div class="mt-5"><Pagination :paginator="reports" /></div>
</template>
