<script setup>
import { Globe, ScrollText, ShieldAlert } from '@lucide/vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import Switch from '@/Components/ui/Switch.vue';
import { useFilters } from '@/composables/useFilters';
import { datetime } from '@/lib/format';

const props = defineProps({ logs: Object, users: Array, actions: Array, filters: Object, failedToday: Number });
const f = useFilters(props.filters, route('admin.audit.index'));
const color = (a) => (/failed|deleted|revoked|deactivated|disabled/.test(a) ? 'rose' : /login|logout|session|2fa|password|pin/.test(a) ? 'violet' : /created|approved|uploaded|enabled|activated/.test(a) ? 'emerald' : 'sky');
const agent = (ua = '') => (ua.match(/(Edg|OPR|Chrome|Firefox|Safari)\/[\d.]+/)?.[0] || '').split('/')[0] + (/(Android|iPhone|Windows|Mac OS|Linux)/.exec(ua)?.[0] ? ` · ${/(Android|iPhone|Windows|Mac OS|Linux)/.exec(ua)[0]}` : '');
</script>

<template>
    <Head title="Log Aktivitas" />
    <PageHeader title="Log Aktivitas & Keamanan" subtitle="Jejak audit seluruh aksi penting di sistem." :icon="ScrollText" />
    <div v-if="failedToday" class="mb-5 flex items-center gap-3 rounded-2xl border-2 border-rose-300 bg-rose-50 p-4 text-sm font-semibold text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-300">
        <ShieldAlert class="h-5 w-5" /> {{ failedToday }} percobaan login / PIN gagal hari ini. Periksa bila ada aktivitas mencurigakan.
    </div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        <SearchInput v-model="f.q" placeholder="Cari deskripsi / IP…" />
        <select v-model="f.user" class="input input-sm sm:w-48"><option :value="null">Semua pengguna</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
        <select v-model="f.action" class="input input-sm sm:w-56"><option value="">Semua aksi</option><option v-for="a in actions" :key="a" :value="a">{{ a }}</option></select>
        <Switch v-model="f.security" label="Hanya keamanan" />
    </div>
    <div class="table-wrap">
        <table v-if="logs.data.length" class="table">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Deskripsi</th><th>IP / Perangkat</th></tr></thead>
            <tbody>
                <tr v-for="l in logs.data" :key="l.id">
                    <td class="text-xs whitespace-nowrap text-muted">{{ datetime(l.created_at) }}</td>
                    <td><span class="flex items-center gap-2"><Avatar :user="l.user" :name="l.user?.name || 'Tamu'" size="xs" /><span class="text-sm font-semibold">{{ l.user?.name || 'Tamu / Sistem' }}</span></span></td>
                    <td><Badge :color="color(l.action)" size="xs">{{ l.action }}</Badge></td>
                    <td class="max-w-md text-sm">{{ l.description }}</td>
                    <td class="text-xs text-muted"><span class="inline-flex items-center gap-1"><Globe class="h-3 w-3" /> {{ l.ip_address }}</span><br />{{ agent(l.user_agent || '') }}</td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else title="Belum ada log" />
    </div>
    <div class="mt-5"><Pagination :paginator="logs" /></div>
</template>
