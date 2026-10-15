<script setup>
import { ArrowRight, Briefcase, CalendarCheck, CalendarClock, ClipboardCheck, Copy, Gift, Kanban, Megaphone, NotebookPen, Signature, TriangleAlert } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AttendanceWidget from '@/Components/panel/AttendanceWidget.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import { toast } from '@/composables/useToast';
import { date, greeting, relative } from '@/lib/format';

const props = defineProps({ today: Object, workHours: Object, stats: Object, reportToday: Boolean, tasks: Array, orders: Array, announcements: Array, referral: Object });
const page = usePage();
const firstName = computed(() => (page.props.auth.user?.name || '').split(' ')[0]);
const priorityColor = { low: 'slate', medium: 'sky', high: 'orange', urgent: 'rose' };
const levelColor = { info: 'sky', success: 'emerald', warning: 'amber', danger: 'rose' };

const copyRef = async () => {
    try {
        await navigator.clipboard.writeText(props.referral.url);
        toast('Tautan referral disalin! Bagikan ke calon klien 🚀');
    } catch {
        toast(props.referral.url, 'info');
    }
};
</script>

<template>
    <Head title="Dashboard Agen" />
    <div class="grid gap-6 xl:grid-cols-3">
        <section class="animate-gradient bg-brand-gradient relative overflow-hidden rounded-[2rem] border-2 border-ink p-6 text-white shadow-brutal sm:p-8 xl:col-span-2">
            <div class="dot-bg absolute inset-0 opacity-20"></div>
            <div class="relative">
                <p class="text-sm font-bold opacity-80">{{ greeting() }} ✨</p>
                <h1 class="mt-1 font-display text-3xl font-bold sm:text-4xl">Semangat, {{ firstName }}!</h1>
                <p class="mt-2 max-w-xl text-white/85">Kamu punya <b>{{ stats.tasks_open }}</b> tugas aktif<span v-if="stats.tasks_overdue">, <b>{{ stats.tasks_overdue }}</b> lewat tenggat</span> dan <b>{{ stats.orders_active }}</b> proyek berjalan.</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <Link :href="route('panel.tasks.index')" class="btn btn-sm border-2 border-zinc-950 bg-yellow-300 text-zinc-950"><Kanban class="h-4 w-4" /> Buka tugas</Link>
                    <Link :href="route('agent.reports.index')" class="btn btn-sm border-2 border-white/60 bg-white/15 text-white"><NotebookPen class="h-4 w-4" /> {{ reportToday ? 'Lihat laporan' : 'Isi laporan harian' }}</Link>
                    <Link :href="route('panel.tte.create')" class="btn btn-sm border-2 border-white/60 bg-white/15 text-white"><Signature class="h-4 w-4" /> Ajukan TTE</Link>
                </div>
            </div>
        </section>
        <AttendanceWidget :today="today" :work-hours="workHours" />
    </div>

    <div v-if="!reportToday" class="mt-6 flex items-center gap-3 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
        <TriangleAlert class="h-5 w-5 shrink-0" /> Jangan lupa isi laporan harian sebelum pulang ya!
        <Link :href="route('agent.reports.index')" class="ml-auto shrink-0 font-bold underline">Isi sekarang</Link>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Tugas aktif" :value="stats.tasks_open" :icon="Kanban" color="violet" :hint="`${stats.tasks_done_month} selesai bulan ini`" :href="route('panel.tasks.index')" />
        <StatCard label="Hadir bulan ini" :value="stats.present_month" :icon="CalendarCheck" color="emerald" :hint="`${stats.late_month}x terlambat`" :href="route('agent.attendance.index')" />
        <StatCard label="Proyek aktif" :value="stats.orders_active" :icon="Briefcase" color="fuchsia" :href="route('agent.orders.index')" />
        <StatCard label="Klien referral" :value="stats.referrals" :icon="Gift" color="lime" :hint="`${stats.referrals_completed} proyek selesai`" :href="route('agent.orders.index', { tab: 'referral' })" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <div class="flex items-center justify-between"><h2 class="font-display text-lg font-bold">Tugas prioritas</h2><Link :href="route('panel.tasks.index')" class="text-sm font-bold text-violet-600 hover:underline dark:text-violet-300">Semua</Link></div>
            <ul v-if="tasks.length" class="mt-4 space-y-2">
                <li v-for="t in tasks" :key="t.id" class="flex items-center gap-3 rounded-2xl border border-line p-3">
                    <ClipboardCheck class="h-5 w-5 shrink-0 text-violet-500" />
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ t.title }}</p><p v-if="t.order" class="text-xs text-muted">{{ t.order.code }}</p></div>
                    <Badge :color="priorityColor[t.priority]" size="xs">{{ t.priority }}</Badge>
                    <span v-if="t.due_date" :class="t.is_overdue ? 'text-rose-500' : 'text-muted'" class="inline-flex items-center gap-1 text-xs font-bold whitespace-nowrap"><CalendarClock class="h-3.5 w-3.5" /> {{ date(t.due_date, { day: 'numeric', month: 'short' }) }}</span>
                </li>
            </ul>
            <EmptyState v-else compact title="Tidak ada tugas aktif 🎉" />

            <div class="mt-8 flex items-center justify-between"><h2 class="font-display text-lg font-bold">Proyek yang kamu pegang</h2><Link :href="route('agent.orders.index')" class="text-sm font-bold text-violet-600 hover:underline dark:text-violet-300">Semua</Link></div>
            <ul v-if="orders.length" class="mt-4 space-y-2">
                <li v-for="o in orders" :key="o.id">
                    <Link :href="route('agent.orders.show', o.id)" class="flex items-center gap-4 rounded-2xl border border-line p-3 transition hover:border-ink">
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ o.client_name }}</p><p class="text-xs text-muted">{{ o.code }} · {{ o.type_label }}</p></div>
                        <div class="w-28"><ProgressBar :value="o.progress" size="sm" show-label /></div>
                        <ArrowRight class="h-4 w-4 text-muted" />
                    </Link>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-muted">Belum ada proyek yang ditugaskan.</p>
        </div>

        <div class="space-y-6">
            <div v-if="referral.url" class="card-brutal bg-lime-100 p-6 dark:bg-lime-500/10">
                <Gift class="h-8 w-8 text-lime-700 dark:text-lime-300" />
                <h2 class="mt-3 font-display text-lg font-bold">Ajak klien, dapat apresiasi!</h2>
                <p class="mt-1 text-sm text-muted">Bagikan tautan referral. Request yang masuk otomatis tercatat atas namamu.</p>
                <p class="mt-3 rounded-xl bg-card px-3 py-2 font-mono text-xs break-all">{{ referral.url }}</p>
                <button type="button" class="btn btn-brutal btn-sm mt-3 w-full" @click="copyRef"><Copy class="h-4 w-4" /> Salin tautan</button>
            </div>
            <div class="card p-6">
                <div class="flex items-center justify-between"><h2 class="flex items-center gap-2 font-display text-lg font-bold"><Megaphone class="h-5 w-5 text-fuchsia-500" /> Pengumuman</h2><Link :href="route('panel.announcements.index')" class="text-sm font-bold text-violet-600 dark:text-violet-300">Semua</Link></div>
                <ul v-if="announcements.length" class="mt-4 space-y-3">
                    <li v-for="a in announcements" :key="a.id" class="rounded-2xl bg-surface-2/70 p-3">
                        <div class="flex items-center gap-2"><Badge :color="levelColor[a.level]" size="xs">{{ a.is_pinned ? '📌' : '' }} {{ a.level }}</Badge><span class="text-xs text-muted">{{ relative(a.created_at) }}</span></div>
                        <p class="mt-1 text-sm font-bold">{{ a.title }}</p>
                        <p class="line-clamp-2 text-xs text-muted">{{ a.body }}</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-muted">Belum ada pengumuman.</p>
            </div>
        </div>
    </div>
</template>
