<script setup>
import { Eye, FileCheck, Plus, Settings2, Signature } from '@lucide/vue';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useFilters } from '@/composables/useFilters';
import { relative } from '@/lib/format';

const props = defineProps({ items: Object, counts: Object, filters: Object, statuses: Object, isAdmin: Boolean });
const f = useFilters(props.filters, route('panel.tte.index'));
const total = computed(() => Object.values(props.counts || {}).reduce((a, b) => a + Number(b), 0));
const tabs = computed(() => [{ key: '', label: 'Semua', count: total.value }, ...Object.entries(props.statuses).map(([k, v]) => ({ key: k, label: v.label, count: props.counts?.[k] || 0 }))]);
</script>

<template>
    <Head title="Tanda Tangan Elektronik" />
    <PageHeader title="Tanda Tangan Elektronik (TTE)" :subtitle="isAdmin ? 'Tinjau, sahkan & kelola dokumen ber-QR verifikasi.' : 'Ajukan dokumen PDF untuk ditandatangani secara elektronik.'" :icon="Signature">
        <Link v-if="isAdmin" :href="route('admin.tte.settings')" class="btn btn-secondary"><Settings2 class="h-4 w-4" /> Pengaturan</Link>
        <Link :href="route('panel.tte.create')" class="btn btn-primary"><Plus class="h-4 w-4" /> Ajukan TTE</Link>
    </PageHeader>

    <div class="-mx-1 mb-4 overflow-x-auto px-1">
        <div class="flex gap-2 pb-1">
            <button v-for="t in tabs" :key="t.key" type="button" :class="f.status === t.key ? 'border-ink bg-ink text-surface' : 'border-line bg-card hover:border-ink'" class="flex shrink-0 items-center gap-2 rounded-xl border-2 px-3 py-1.5 text-sm font-bold transition" @click="f.status = t.key">
                {{ t.label }} <span class="rounded-full bg-surface-2 px-1.5 text-[11px] text-ink">{{ t.count }}</span>
            </button>
        </div>
    </div>
    <div class="mb-4"><SearchInput v-model="f.q" placeholder="Cari perihal, nomor, kode…" /></div>

    <div v-if="items.data.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="it in items.data" :key="it.id" :href="route('panel.tte.show', it.id)" class="group card card-hover flex flex-col p-5">
            <div class="flex items-start justify-between gap-3">
                <span :class="it.status === 'approved' ? 'from-emerald-400 to-teal-500' : it.status === 'pending' ? 'from-amber-400 to-orange-500' : it.status === 'rejected' ? 'from-rose-400 to-pink-500' : 'from-slate-400 to-slate-500'" class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br text-white transition-transform group-hover:-rotate-6">
                    <FileCheck class="h-6 w-6" />
                </span>
                <StatusBadge :value="it.status" :map="statuses" />
            </div>
            <h3 class="mt-4 line-clamp-2 font-bold">{{ it.title }}</h3>
            <p class="mt-1 font-mono text-xs text-muted">{{ it.code }}</p>
            <p v-if="it.document_number" class="text-xs text-muted">No. {{ it.document_number }}</p>
            <div class="mt-auto flex items-center justify-between pt-4">
                <span class="flex items-center gap-2 text-xs text-muted"><Avatar :user="it.user" size="xs" /> {{ it.user?.name }}</span>
                <span class="flex items-center gap-1 text-xs text-muted"><Eye class="h-3.5 w-3.5" /> {{ relative(it.created_at) }}</span>
            </div>
        </Link>
    </div>
    <EmptyState v-else title="Belum ada pengajuan TTE" description="Ajukan dokumen PDF & tentukan posisi QR tanda tangan.">
        <Link :href="route('panel.tte.create')" class="btn btn-primary"><Plus class="h-4 w-4" /> Ajukan sekarang</Link>
    </EmptyState>
    <div class="mt-5"><Pagination :paginator="items" /></div>
</template>
