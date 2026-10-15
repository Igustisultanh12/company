<script setup>
import {
    ArrowRight, Briefcase, CalendarCheck, CircleAlert, ClipboardList, Inbox, PartyPopper, Plus, Receipt, Scale,
    Signature, TrendingUp, Wallet,
} from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ChartDoughnut from '@/Components/panel/ChartDoughnut.vue';
import ChartRevenue from '@/Components/panel/ChartRevenue.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import { date, greeting, relative, rupiah } from '@/lib/format';

const props = defineProps({
    stats: Object,
    chart: Array,
    statusChart: Array,
    recentOrders: Array,
    pendingTte: Array,
    upcomingTasks: Array,
    activities: Array,
    compliance: Object,
});

const page = usePage();
const firstName = computed(() => (page.props.auth.user?.name || '').split(' ')[0]);
const statusMap = computed(() => Object.fromEntries(props.statusChart.map((s) => [s.key, s])));
const priorityColor = { low: 'slate', medium: 'sky', high: 'orange', urgent: 'rose' };
const complianceColor = { missing: 'rose', overdue: 'rose', due_soon: 'amber', ok: 'emerald' };
const complianceLabel = { missing: 'Belum ada dokumen', overdue: 'Terlambat', due_soon: 'Segera jatuh tempo', ok: 'Terpenuhi' };
</script>

<template>
    <Head title="Dashboard Admin" />

    <!-- Greeting -->
    <section class="animate-gradient bg-brand-gradient relative mb-8 overflow-hidden rounded-[2rem] border-2 border-ink p-6 text-white shadow-brutal sm:p-8">
        <div class="dot-bg absolute inset-0 opacity-20"></div>
        <PartyPopper class="animate-bounce-soft absolute top-6 right-6 hidden h-16 w-16 text-yellow-300 sm:block" />
        <div class="relative">
            <p class="text-sm font-bold opacity-80">{{ greeting() }} 👋</p>
            <h1 class="mt-1 font-display text-3xl font-bold sm:text-4xl">{{ firstName }}, siap bikin hari ini produktif?</h1>
            <p class="mt-2 max-w-2xl text-white/85">
                Ada <b>{{ stats.orders_new }}</b> request baru, <b>{{ stats.tte_pending }}</b> dokumen menunggu TTE, dan <b>{{ stats.leaves_pending }}</b> pengajuan cuti.
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <Link :href="route('admin.orders.index', { status: 'new' })" class="btn btn-sm border-2 border-zinc-950 bg-yellow-300 text-zinc-950">Tinjau request <ArrowRight class="h-4 w-4" /></Link>
                <Link :href="route('admin.invoices.create')" class="btn btn-sm border-2 border-white/60 bg-white/15 text-white"><Plus class="h-4 w-4" /> Buat invoice</Link>
                <Link :href="route('panel.tte.index', { status: 'pending' })" class="btn btn-sm border-2 border-white/60 bg-white/15 text-white"><Signature class="h-4 w-4" /> Sahkan TTE</Link>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Request baru" :value="stats.orders_new" :icon="Inbox" color="sky" :hint="`${stats.orders_total} total pesanan`" :href="route('admin.orders.index', { status: 'new' })" />
        <StatCard label="Proyek berjalan" :value="stats.orders_active" :icon="Briefcase" color="fuchsia" :hint="`${stats.orders_completed} selesai`" :href="route('admin.orders.index')" />
        <StatCard label="Pendapatan bulan ini" :value="rupiah(stats.revenue_month)" :icon="Wallet" color="emerald" :hint="`Tahun ini ${rupiah(stats.revenue_year)}`" :href="route('admin.invoices.index')" />
        <StatCard label="Piutang" :value="rupiah(stats.receivable)" :icon="Receipt" color="orange" hint="Invoice belum lunas" :href="route('admin.invoices.index', { status: 'unpaid' })" />
        <StatCard label="TTE menunggu" :value="stats.tte_pending" :icon="Signature" color="violet" :href="route('panel.tte.index', { status: 'pending' })" />
        <StatCard label="Pesan masuk" :value="stats.messages_unread" :icon="Inbox" color="pink" hint="Belum dibaca" :href="route('admin.messages.index', { unread: 1 })" />
        <StatCard label="Hadir hari ini" :value="`${stats.present_today}/${stats.agents_active}`" :icon="CalendarCheck" color="lime" hint="Agen aktif" :href="route('admin.attendance.index')" />
        <StatCard label="Cuti menunggu" :value="stats.leaves_pending" :icon="ClipboardList" color="amber" :href="route('admin.leaves.index')" />
    </div>

    <!-- Charts -->
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-lg font-bold">Performa 6 bulan</h2>
                    <p class="text-sm text-muted">Pendapatan (pembayaran diterima) & jumlah pesanan masuk</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15"><TrendingUp class="h-5 w-5" /></span>
            </div>
            <div class="mt-4"><ChartRevenue :rows="chart" /></div>
        </div>
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold">Status pesanan</h2>
            <p class="text-sm text-muted">Distribusi seluruh pesanan</p>
            <div class="mt-4"><ChartDoughnut :rows="statusChart" /></div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <!-- Recent orders -->
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h2 class="font-display text-lg font-bold">Pesanan terbaru</h2>
                <Link :href="route('admin.orders.index')" class="text-sm font-bold text-violet-600 hover:underline dark:text-violet-300">Lihat semua</Link>
            </div>
            <div v-if="recentOrders.length" class="divide-y divide-line">
                <Link v-for="o in recentOrders" :key="o.id" :href="route('admin.orders.show', o.id)" class="flex items-center gap-4 px-6 py-4 transition hover:bg-surface-2/60">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold">{{ o.company_name || o.client_name }}</p>
                        <p class="text-xs text-muted">{{ o.code }} · {{ o.type_label }} · {{ relative(o.created_at) }}</p>
                    </div>
                    <div class="hidden w-32 sm:block"><ProgressBar :value="o.progress" size="sm" /></div>
                    <Badge :color="statusMap[o.status]?.color" dot>{{ o.status_label }}</Badge>
                    <Avatar v-if="o.assignee" :user="o.assignee" size="sm" />
                </Link>
            </div>
            <EmptyState v-else compact title="Belum ada pesanan" description="Request dari website akan muncul di sini." />
        </div>

        <!-- Compliance -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><Scale class="h-5 w-5 text-violet-500" /> Kepatuhan izin</h2>
                <span class="text-sm font-bold">{{ compliance.ok }}/{{ compliance.total }}</span>
            </div>
            <ProgressBar class="mt-3" :value="compliance.total ? Math.round((compliance.ok / compliance.total) * 100) : 0" />
            <ul class="mt-4 space-y-2.5">
                <li v-for="c in compliance.attention.slice(0, 4)" :key="c.key" class="flex items-start gap-2.5 text-sm">
                    <CircleAlert :class="c.status === 'due_soon' ? 'text-amber-500' : 'text-rose-500'" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span class="min-w-0"><span class="block leading-snug font-semibold">{{ c.title }}</span><Badge :color="complianceColor[c.status]" size="xs">{{ complianceLabel[c.status] }}</Badge></span>
                </li>
                <li v-if="!compliance.attention.length" class="text-sm font-semibold text-emerald-600">Semua kewajiban terpenuhi 🎉</li>
            </ul>
            <Link :href="route('admin.compliance.index')" class="btn btn-secondary btn-sm mt-5 w-full">Kelola legalitas</Link>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold">TTE menunggu</h2>
            <ul v-if="pendingTte.length" class="mt-4 space-y-3">
                <li v-for="t in pendingTte" :key="t.id">
                    <Link :href="route('panel.tte.show', t.id)" class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-surface-2">
                        <Avatar :user="t.user" size="sm" />
                        <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold">{{ t.title }}</span><span class="text-xs text-muted">{{ t.user?.name }} · {{ relative(t.created_at) }}</span></span>
                    </Link>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-muted">Tidak ada dokumen menunggu ✨</p>
        </div>
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold">Tenggat tugas terdekat</h2>
            <ul v-if="upcomingTasks.length" class="mt-4 space-y-3">
                <li v-for="t in upcomingTasks" :key="t.id" class="flex items-center gap-3">
                    <Badge :color="priorityColor[t.priority]" size="xs">{{ t.priority }}</Badge>
                    <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ t.title }}</span>
                    <span :class="t.is_overdue ? 'text-rose-500' : 'text-muted'" class="text-xs font-bold whitespace-nowrap">{{ date(t.due_date, { day: 'numeric', month: 'short' }) }}</span>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-muted">Belum ada tugas bertenggat.</p>
            <Link :href="route('panel.tasks.index')" class="btn btn-secondary btn-sm mt-5 w-full">Buka Kanban</Link>
        </div>
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold">Aktivitas terbaru</h2>
            <ul class="mt-4 space-y-3">
                <li v-for="a in activities" :key="a.id" class="flex gap-3">
                    <Avatar :user="a.user" :name="a.user?.name || 'Sistem'" size="xs" class="mt-0.5" />
                    <span class="min-w-0"><span class="line-clamp-2 block text-sm">{{ a.description || a.action }}</span><span class="text-xs text-muted">{{ relative(a.created_at) }}</span></span>
                </li>
            </ul>
            <Link :href="route('admin.audit.index')" class="btn btn-secondary btn-sm mt-5 w-full">Log lengkap</Link>
        </div>
    </div>
</template>
