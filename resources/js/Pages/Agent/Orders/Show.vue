<script setup>
import { ArrowLeft, Check, Lock, MessageCircle, Send } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Badge from '@/Components/ui/Badge.vue';
import FormField from '@/Components/ui/FormField.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Switch from '@/Components/ui/Switch.vue';
import { date, datetime, waLink } from '@/lib/format';

const props = defineProps({ order: Object, isPic: Boolean, catalog: Object });
const form = useForm({ progress: props.order.progress, message: '', is_public: true, request_review: false });
const submit = () => form.post(route('agent.orders.progress', props.order.id), { preserveScroll: true, onSuccess: () => form.reset('message', 'request_review') });
</script>

<template>
    <Head :title="`Pesanan ${order.code}`" />
    <Link :href="route('agent.orders.index')" class="btn btn-ghost btn-sm mb-4"><ArrowLeft class="h-4 w-4" /> Pesanan saya</Link>
    <div class="card-brutal mb-6 flex flex-wrap items-center gap-4 p-6">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-muted">{{ order.code }}</p>
            <h1 class="font-display text-2xl font-bold">{{ order.company_name || order.client_name }}</h1>
            <p class="text-sm text-muted">{{ order.type_label }}<span v-if="order.package"> · {{ order.package.name }}</span></p>
        </div>
        <StatusBadge :value="order.status" :map="catalog.orderStatuses" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Kebutuhan klien</h2>
                <div class="mt-4 flex flex-wrap gap-1.5"><Badge v-for="k in order.features || []" :key="k" color="violet"><Check class="h-3 w-3" /> {{ catalog.websiteFeatures[k] || k }}</Badge></div>
                <p class="mt-4 rounded-2xl bg-surface-2/70 p-4 text-sm whitespace-pre-line">{{ order.description }}</p>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-muted">Tenggat</dt><dd class="font-bold">{{ date(order.deadline) }}</dd></div>
                    <div><dt class="text-muted">Warna</dt><dd class="font-bold">{{ order.color_preference || '—' }}</dd></div>
                    <div><dt class="text-muted">Halaman</dt><dd class="font-bold">{{ catalog.pagesEstimates[order.pages_estimate] || '—' }}</dd></div>
                </dl>
                <p v-if="order.reference_urls" class="mt-4 text-sm"><span class="text-muted">Referensi:</span> <span class="break-all">{{ order.reference_urls }}</span></p>
            </div>
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Timeline</h2>
                <ol class="relative mt-5 space-y-5 border-l-2 border-dashed border-line pl-6">
                    <li v-for="a in order.activities" :key="a.id" class="relative">
                        <span :class="a.is_public ? 'bg-emerald-500' : 'bg-slate-400'" class="absolute top-1 -left-[31px] h-3 w-3 rounded-full border-2 border-card"></span>
                        <p class="text-xs"><b>{{ a.user?.name || 'Sistem' }}</b> <span class="text-muted">· {{ datetime(a.created_at) }}</span> <Lock v-if="!a.is_public" class="inline h-3 w-3 text-muted" /></p>
                        <p class="mt-0.5 text-sm whitespace-pre-line">{{ a.message }}</p>
                    </li>
                </ol>
            </div>
        </div>
        <div class="space-y-6">
            <form v-if="isPic" class="card-brutal p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-bold">Update progres</h2>
                <div class="mt-4"><ProgressBar :value="form.progress" show-label /></div>
                <input v-model.number="form.progress" type="range" min="0" max="100" step="5" class="mt-3 w-full accent-violet-600" aria-label="Progres" />
                <FormField class="mt-4" label="Catatan progres" :error="form.errors.message" required><textarea v-model="form.message" rows="3" class="input" placeholder="mis. Halaman beranda & layanan selesai dibuat"></textarea></FormField>
                <div class="mt-4 space-y-3">
                    <Switch v-model="form.is_public" label="Tampilkan ke klien" description="Muncul di halaman Lacak Pesanan." />
                    <Switch v-model="form.request_review" label="Minta review admin" />
                </div>
                <button type="submit" class="btn btn-primary mt-5 w-full" :disabled="form.processing || !form.message"><Send class="h-4 w-4" /> Kirim update</button>
            </form>
            <div v-if="isPic && order.client_phone" class="card p-6">
                <p class="text-xs text-muted">Kontak klien</p>
                <p class="font-bold">{{ order.client_name }}</p>
                <p class="text-sm">{{ order.client_phone }}</p>
                <a :href="waLink(order.client_phone, `Halo ${order.client_name}, saya dari tim Kreatif Sky terkait proyek ${order.code}.`)" target="_blank" rel="noopener" class="btn btn-sm mt-3 w-full bg-[#25D366] text-white"><MessageCircle class="h-4 w-4" /> WhatsApp</a>
            </div>
            <div v-if="!isPic" class="card p-6 text-sm text-muted">Kamu melihat pesanan ini sebagai <b class="text-ink">agen referral</b>. Data kontak klien hanya tersedia untuk PIC proyek.</div>
            <div v-if="order.tasks?.length" class="card p-6">
                <h2 class="font-display text-lg font-bold">Tugas kamu</h2>
                <ul class="mt-3 space-y-2 text-sm"><li v-for="t in order.tasks" :key="t.id" class="flex items-center gap-2"><Check v-if="t.status === 'done'" class="h-4 w-4 text-emerald-500" /><span v-else class="h-4 w-4 rounded-full border-2 border-line"></span>{{ t.title }}</li></ul>
                <Link :href="route('panel.tasks.index')" class="btn btn-secondary btn-sm mt-4 w-full">Buka Kanban</Link>
            </div>
        </div>
    </div>
</template>
