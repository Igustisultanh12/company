<script setup>
import { CircleAlert, Eye, Plus, Receipt, Wallet } from '@lucide/vue';
import { route } from 'ziggy-js';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useFilters } from '@/composables/useFilters';
import { date, rupiah } from '@/lib/format';

const props = defineProps({ invoices: Object, filters: Object, statuses: Object, stats: Object });
const f = useFilters(props.filters, route('admin.invoices.index'));
const overdue = (inv) => inv.due_date && ['unpaid', 'partial'].includes(inv.status) && new Date(inv.due_date) < new Date(new Date().toDateString());
</script>

<template>
    <Head title="Invoice" />
    <PageHeader title="Invoice & Pembayaran" subtitle="Tagihan resmi perusahaan, pencatatan pembayaran & tautan invoice untuk klien." :icon="Receipt">
        <Link :href="route('admin.invoices.create')" class="btn btn-primary"><Plus class="h-4 w-4" /> Buat invoice</Link>
    </PageHeader>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard label="Total tagihan" :value="rupiah(stats.billed)" :icon="Receipt" color="violet" />
        <StatCard label="Sudah dibayar" :value="rupiah(stats.paid)" :icon="Wallet" color="emerald" />
        <StatCard label="Lewat jatuh tempo" :value="stats.overdue" :icon="CircleAlert" color="rose" />
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <SearchInput v-model="f.q" placeholder="Cari nomor / klien…" />
        <select v-model="f.status" class="input input-sm sm:w-48"><option value="">Semua status</option><option v-for="(s, k) in statuses" :key="k" :value="k">{{ s.label }}</option></select>
    </div>

    <div class="table-wrap">
        <table v-if="invoices.data.length" class="table">
            <thead><tr><th>Nomor</th><th>Klien</th><th>Terbit</th><th>Jatuh tempo</th><th class="text-right">Total</th><th class="text-right">Sisa</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <tr v-for="inv in invoices.data" :key="inv.id">
                    <td><Link :href="route('admin.invoices.show', inv.id)" class="font-mono text-sm font-bold hover:text-violet-600">{{ inv.number }}</Link><p v-if="inv.order" class="text-xs text-muted">{{ inv.order.code }}</p></td>
                    <td class="font-semibold">{{ inv.client_name }}</td>
                    <td class="text-sm text-muted">{{ date(inv.issue_date) }}</td>
                    <td :class="overdue(inv) ? 'font-bold text-rose-500' : 'text-muted'" class="text-sm">{{ date(inv.due_date) }}</td>
                    <td class="text-right font-bold">{{ rupiah(inv.total) }}</td>
                    <td class="text-right text-sm">{{ rupiah(inv.balance) }}</td>
                    <td><StatusBadge :value="inv.status" :map="statuses" /></td>
                    <td class="text-right"><Link :href="route('admin.invoices.show', inv.id)" class="btn btn-ghost btn-sm"><Eye class="h-4 w-4" /></Link></td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else title="Belum ada invoice" description="Buat invoice dari halaman detail pesanan atau tombol di atas." />
    </div>
    <div class="mt-5"><Pagination :paginator="invoices" /></div>
</template>
