<script setup>
import { Briefcase, Copy, Eye, Gift } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import { toast } from '@/composables/useToast';
import { date } from '@/lib/format';

const props = defineProps({ orders: Object, tab: String, counts: Object, statuses: Object, referralUrl: String });
const current = computed({
    get: () => props.tab,
    set: (v) => router.get(route('agent.orders.index'), { tab: v }, { preserveState: true }),
});
const copy = async () => {
    try {
        await navigator.clipboard.writeText(props.referralUrl);
        toast('Tautan referral disalin!');
    } catch {
        toast(props.referralUrl, 'info');
    }
};
</script>

<template>
    <Head title="Pesanan Saya" />
    <PageHeader title="Pesanan Saya" subtitle="Proyek yang kamu tangani & klien dari referral kamu." :icon="Briefcase">
        <button v-if="referralUrl" type="button" class="btn btn-brutal" @click="copy"><Copy class="h-4 w-4" /> Tautan referral</button>
    </PageHeader>
    <Tabs v-model="current" :tabs="[{ key: 'assigned', label: 'Ditugaskan', icon: Briefcase, count: counts.assigned }, { key: 'referral', label: 'Referral saya', icon: Gift, count: counts.referral }]" class="mb-5" />
    <div class="table-wrap">
        <table v-if="orders.data.length" class="table">
            <thead><tr><th>Pesanan</th><th>Jenis</th><th>Status</th><th class="w-40">Progres</th><th>Tenggat</th><th></th></tr></thead>
            <tbody>
                <tr v-for="o in orders.data" :key="o.id">
                    <td><p class="font-bold">{{ o.company_name || o.client_name }}</p><p class="text-xs text-muted">{{ o.code }}</p></td>
                    <td class="text-sm">{{ o.type_label }}</td>
                    <td><StatusBadge :value="o.status" :map="statuses" /></td>
                    <td><ProgressBar :value="o.progress" size="sm" show-label /></td>
                    <td class="text-sm text-muted">{{ date(o.deadline) }}</td>
                    <td class="text-right"><Link :href="route('agent.orders.show', o.id)" class="btn btn-ghost btn-sm"><Eye class="h-4 w-4" /> Detail</Link></td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else :title="tab === 'referral' ? 'Belum ada klien referral' : 'Belum ada proyek'" :description="tab === 'referral' ? 'Bagikan tautan referral kamu ke calon klien.' : 'Proyek yang ditugaskan admin akan muncul di sini.'" />
    </div>
    <div class="mt-5"><Pagination :paginator="orders" /></div>
</template>
