<script setup>
import { CalendarClock, Check, CircleX, FileText, Package, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import Blobs from '@/Components/site/Blobs.vue';
import Badge from '@/Components/ui/Badge.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { date, datetime, rupiah } from '@/lib/format';

const props = defineProps({ meta: Object, order: Object, invoices: Array, statuses: Object });

const flow = ['new', 'reviewing', 'quoted', 'deal', 'in_progress', 'completed'];
const currentIndex = computed(() => {
    const s = props.order.status === 'revision' ? 'in_progress' : props.order.status;
    return flow.indexOf(s);
});
const cancelled = computed(() => props.order.status === 'cancelled');
const invoiceColors = { unpaid: 'amber', partial: 'sky', paid: 'emerald', cancelled: 'rose' };
</script>

<template>
    <Head :title="meta.title" />
    <section class="relative isolate overflow-hidden pt-32 pb-24 sm:pt-40">
        <Blobs variant="soft" />
        <div class="container-x max-w-4xl">
            <div v-reveal class="card-brutal overflow-hidden">
                <div class="bg-brand-gradient animate-gradient p-6 text-white sm:p-8">
                    <p class="text-sm font-bold opacity-80">Pesanan {{ order.code }}</p>
                    <h1 class="mt-1 font-display text-3xl font-bold">Halo, {{ order.client_name.split(' ')[0] }} 👋</h1>
                    <p class="mt-1 text-white/85">{{ order.type_label }}<span v-if="order.package"> · Paket {{ order.package }}</span></p>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <StatusBadge :value="order.status" :map="statuses" />
                        <span class="text-sm text-muted">Masuk {{ datetime(order.created_at) }}</span>
                    </div>
                    <div class="mt-6">
                        <div class="mb-2 flex justify-between text-sm font-bold"><span>Progres pengerjaan</span><span>{{ order.progress }}%</span></div>
                        <ProgressBar :value="order.progress" />
                    </div>

                    <div v-if="cancelled" class="mt-8 flex items-center gap-3 rounded-2xl bg-rose-50 p-4 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300"><CircleX class="h-6 w-6" /> Pesanan ini dibatalkan. Hubungi kami bila ada pertanyaan.</div>
                    <ol v-else class="mt-8 grid gap-4 sm:grid-cols-6">
                        <li v-for="(s, i) in flow" :key="s" class="flex items-center gap-3 sm:flex-col sm:text-center">
                            <span
                                :class="i < currentIndex ? 'bg-lime-300 text-zinc-900' : i === currentIndex ? 'bg-yellow-300 text-zinc-900 animate-bounce-soft' : 'bg-card text-muted'"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border-2 border-ink"
                            >
                                <Check v-if="i < currentIndex" class="h-5 w-5" />
                                <Sparkles v-else-if="i === currentIndex" class="h-5 w-5" />
                                <span v-else class="text-sm font-bold">{{ i + 1 }}</span>
                            </span>
                            <span :class="i <= currentIndex ? 'text-ink' : 'text-muted'" class="text-xs font-bold">{{ statuses[s]?.label }}</span>
                        </li>
                    </ol>
                    <p v-if="order.deadline" class="mt-6 flex items-center gap-2 text-sm text-muted"><CalendarClock class="h-4 w-4" /> Target selesai: <b class="text-ink">{{ date(order.deadline) }}</b></p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-5">
                <div v-reveal="'left'" class="card p-6 lg:col-span-3">
                    <h2 class="flex items-center gap-2 font-display text-xl font-bold"><Package class="h-5 w-5 text-violet-500" /> Riwayat update</h2>
                    <ol class="relative mt-6 space-y-6 border-l-2 border-dashed border-line pl-6">
                        <li v-for="a in order.activities" :key="a.id" class="relative">
                            <span class="bg-brand-gradient absolute top-1 -left-[33px] h-4 w-4 rounded-full border-2 border-card"></span>
                            <p class="text-sm leading-relaxed">{{ a.message }}</p>
                            <p class="mt-1 text-xs font-semibold text-muted">{{ datetime(a.created_at) }}</p>
                        </li>
                    </ol>
                </div>
                <div v-reveal="'right'" class="card p-6 lg:col-span-2">
                    <h2 class="flex items-center gap-2 font-display text-xl font-bold"><FileText class="h-5 w-5 text-fuchsia-500" /> Invoice</h2>
                    <p v-if="!invoices.length" class="mt-4 text-sm text-muted">Belum ada invoice. Invoice terbit setelah penawaran disepakati.</p>
                    <a v-for="inv in invoices" :key="inv.number" :href="inv.url" target="_blank" rel="noopener" class="mt-4 block rounded-2xl border-2 border-line p-4 transition hover:border-ink">
                        <div class="flex items-center justify-between gap-2"><span class="text-sm font-bold">{{ inv.number }}</span><Badge :color="invoiceColors[inv.status] || 'slate'">{{ inv.status_label }}</Badge></div>
                        <p class="mt-2 font-display text-xl font-bold">{{ rupiah(inv.total) }}</p>
                        <p v-if="inv.balance > 0" class="text-xs text-muted">Sisa {{ rupiah(inv.balance) }}<span v-if="inv.due_date"> · jatuh tempo {{ date(inv.due_date) }}</span></p>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
