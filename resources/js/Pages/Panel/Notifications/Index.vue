<script setup>
import { Bell, CheckCheck } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { solid } from '@/lib/colors';
import { datetime, relative } from '@/lib/format';

defineProps({ notifications: Object });
const levelColor = { info: 'sky', success: 'emerald', warning: 'amber', danger: 'rose' };
const open = (n) => router.post(route('panel.notifications.read', n.id));
const readAll = () => router.post(route('panel.notifications.read-all'), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Notifikasi" />
    <PageHeader title="Notifikasi" subtitle="Semua kabar terbaru untuk kamu." :icon="Bell">
        <button type="button" class="btn btn-secondary" @click="readAll"><CheckCheck class="h-4 w-4" /> Tandai semua dibaca</button>
    </PageHeader>
    <div v-if="notifications.data.length" class="card divide-y divide-line overflow-hidden">
        <button v-for="n in notifications.data" :key="n.id" type="button" :class="n.read_at ? '' : 'bg-violet-50/60 dark:bg-violet-500/5'" class="flex w-full gap-4 p-5 text-left transition hover:bg-surface-2" @click="open(n)">
            <span :class="solid[levelColor[n.level] || 'sky']" class="mt-1.5 h-3 w-3 shrink-0 rounded-full"></span>
            <span class="min-w-0 flex-1">
                <span :class="n.read_at ? 'font-semibold' : 'font-extrabold'" class="block">{{ n.title }}</span>
                <span v-if="n.body" class="block text-sm text-muted">{{ n.body }}</span>
            </span>
            <span class="shrink-0 text-xs text-muted" :title="datetime(n.created_at)">{{ relative(n.created_at) }}</span>
        </button>
    </div>
    <EmptyState v-else title="Belum ada notifikasi" description="Notifikasi tugas, pesanan, TTE & pengumuman akan muncul di sini." />
    <div class="mt-5"><Pagination :paginator="notifications" /></div>
</template>
