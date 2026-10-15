<script setup>
import { Megaphone, Pin } from '@lucide/vue';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { datetime } from '@/lib/format';

defineProps({ announcements: Object });
const levelCls = {
    info: 'from-sky-400 to-indigo-500',
    success: 'from-lime-400 to-emerald-500',
    warning: 'from-amber-400 to-orange-500',
    danger: 'from-rose-500 to-pink-500',
};
</script>

<template>
    <Head title="Pengumuman" />
    <PageHeader title="Pengumuman" subtitle="Informasi resmi dari manajemen." :icon="Megaphone" />
    <div v-if="announcements.data.length" class="space-y-4">
        <article v-for="a in announcements.data" :key="a.id" class="card relative overflow-hidden p-6 pl-8">
            <span :class="levelCls[a.level] || levelCls.info" class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b"></span>
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-display text-lg font-bold">{{ a.title }}</h2>
                <Pin v-if="a.is_pinned" class="h-5 w-5 shrink-0 text-fuchsia-500" />
            </div>
            <p class="mt-2 text-sm leading-relaxed whitespace-pre-line">{{ a.body }}</p>
            <p class="mt-4 flex items-center gap-2 text-xs text-muted"><Avatar :user="a.creator" :name="a.creator?.name || 'Admin'" size="xs" /> {{ a.creator?.name || 'Admin' }} · {{ datetime(a.created_at) }}</p>
        </article>
    </div>
    <EmptyState v-else title="Belum ada pengumuman" />
    <div class="mt-5"><Pagination :paginator="announcements" /></div>
</template>
