<script setup>
import { Briefcase, Download, Eye, Plus } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { useFilters } from '@/composables/useFilters';
import { date } from '@/lib/format';
import OrderForm from './OrderForm.vue';

const props = defineProps({ orders: Object, filters: Object, counts: Object, agents: Array, packages: Array, catalog: Object });
const f = useFilters(props.filters, route('admin.orders.index'));

const total = computed(() => Object.values(props.counts || {}).reduce((a, b) => a + Number(b), 0));
const statusTabs = computed(() => [
    { key: '', label: 'Semua', count: total.value },
    ...Object.entries(props.catalog.orderStatuses).map(([k, v]) => ({ key: k, label: v.label, count: props.counts?.[k] || 0 })),
]);

const showCreate = ref(false);
const form = useForm({
    client_name: '', client_email: '', client_phone: '', company_name: '', website_type: 'company_profile', package_id: null, features: ['responsive'],
    budget_range: null, pages_estimate: null, deadline: '', reference_urls: '', description: '', color_preference: '', has_domain: false, has_hosting: false,
    priority: 'normal', quoted_price: null, progress: 0, assigned_to: null, internal_notes: '',
});
const create = () => form.post(route('admin.orders.store'), { onSuccess: () => { showCreate.value = false; form.reset(); } });
const exportUrl = computed(() => route('admin.orders.export', Object.fromEntries(Object.entries(f).filter(([, v]) => v))));
</script>

<template>
    <Head title="Pesanan Website" />
    <PageHeader title="Pesanan Website" subtitle="Kelola request website dari calon klien hingga proyek selesai." :icon="Briefcase">
        <a :href="exportUrl" class="btn btn-secondary"><Download class="h-4 w-4" /> Ekspor CSV</a>
        <button type="button" class="btn btn-primary" @click="showCreate = true"><Plus class="h-4 w-4" /> Pesanan manual</button>
    </PageHeader>

    <div class="-mx-1 mb-4 overflow-x-auto px-1">
        <div class="flex gap-2 pb-1">
            <button
                v-for="t in statusTabs"
                :key="t.key"
                type="button"
                :class="f.status === t.key ? 'border-ink bg-ink text-surface' : 'border-line bg-card hover:border-ink'"
                class="flex shrink-0 items-center gap-2 rounded-xl border-2 px-3 py-1.5 text-sm font-bold transition"
                @click="f.status = t.key"
            >
                {{ t.label }} <span class="rounded-full bg-surface-2 px-1.5 text-[11px] text-ink">{{ t.count }}</span>
            </button>
        </div>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        <SearchInput v-model="f.q" placeholder="Cari kode, nama, email, WA…" />
        <select v-model="f.assignee" class="input input-sm sm:w-48"><option :value="null">Semua PIC</option><option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option></select>
        <select v-model="f.type" class="input input-sm sm:w-52"><option value="">Semua jenis</option><option v-for="(t, k) in catalog.websiteTypes" :key="k" :value="k">{{ t.label }}</option></select>
        <select v-model="f.source" class="input input-sm sm:w-40"><option value="">Semua sumber</option><option value="web">Website</option><option value="referral">Referral agen</option><option value="manual">Manual</option></select>
    </div>

    <div class="table-wrap">
        <table v-if="orders.data.length" class="table">
            <thead><tr><th>Pesanan</th><th>Jenis</th><th>Status</th><th class="w-40">Progres</th><th>PIC</th><th>Masuk</th><th></th></tr></thead>
            <tbody>
                <tr v-for="o in orders.data" :key="o.id">
                    <td>
                        <Link :href="route('admin.orders.show', o.id)" class="font-bold hover:text-violet-600">{{ o.company_name || o.client_name }}</Link>
                        <p class="text-xs text-muted">{{ o.code }} <span v-if="o.source === 'referral'" class="ml-1 rounded bg-lime-200 px-1 text-[10px] font-bold text-zinc-900">REF {{ o.referral_agent?.name }}</span></p>
                    </td>
                    <td class="text-sm">{{ o.type_label }}<p class="text-xs text-muted">{{ o.package?.name || '—' }}</p></td>
                    <td><StatusBadge :value="o.status" :map="catalog.orderStatuses" /></td>
                    <td><ProgressBar :value="o.progress" size="sm" show-label /></td>
                    <td><span v-if="o.assignee" class="flex items-center gap-2"><Avatar :user="o.assignee" size="xs" /><span class="text-sm">{{ o.assignee.name.split(' ')[0] }}</span></span><span v-else class="text-xs text-muted">—</span></td>
                    <td class="text-sm whitespace-nowrap text-muted">{{ date(o.created_at) }}</td>
                    <td class="text-right"><Link :href="route('admin.orders.show', o.id)" class="btn btn-ghost btn-sm"><Eye class="h-4 w-4" /> Detail</Link></td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else title="Belum ada pesanan" description="Bagikan halaman Request Website atau buat pesanan manual." />
    </div>
    <div class="mt-5"><Pagination :paginator="orders" /></div>

    <Modal :show="showCreate" title="Buat pesanan manual" subtitle="Untuk klien yang menghubungi via WhatsApp / offline." size="lg" @close="showCreate = false">
        <OrderForm :form="form" :catalog="catalog" :packages="packages" :agents="agents" />
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showCreate = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing" @click="create">Simpan pesanan</button>
        </template>
    </Modal>
</template>
