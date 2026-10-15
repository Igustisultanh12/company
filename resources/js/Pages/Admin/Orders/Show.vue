<script setup>
import {
    ArrowLeft, Building, CalendarClock, Check, Copy, ExternalLink, Eye, EyeOff, Globe, Link2, ListTodo, Mail, MessageCircle,
    Palette, Pencil, Phone, Plus, Receipt, Save, Server, Trash, Wallet,
} from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import MoneyInput from '@/Components/ui/MoneyInput.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Switch from '@/Components/ui/Switch.vue';
import { confirm } from '@/composables/useConfirm';
import { toast } from '@/composables/useToast';
import { date, datetime, rupiah, waLink } from '@/lib/format';
import OrderForm from './OrderForm.vue';

const props = defineProps({ order: Object, agents: Array, packages: Array, catalog: Object, trackUrl: String });

const base = () => ({
    client_name: props.order.client_name,
    client_email: props.order.client_email,
    client_phone: props.order.client_phone,
    company_name: props.order.company_name,
    website_type: props.order.website_type,
    package_id: props.order.package_id,
    features: [...(props.order.features || [])],
    budget_range: props.order.budget_range,
    pages_estimate: props.order.pages_estimate,
    deadline: props.order.deadline || '',
    reference_urls: props.order.reference_urls,
    description: props.order.description,
    color_preference: props.order.color_preference,
    has_domain: props.order.has_domain,
    has_hosting: props.order.has_hosting,
    status: props.order.status,
    priority: props.order.priority,
    quoted_price: props.order.quoted_price,
    progress: props.order.progress,
    assigned_to: props.order.assigned_to,
    internal_notes: props.order.internal_notes,
    status_note: '',
});
const form = useForm(base());
const save = () => form.put(route('admin.orders.update', props.order.id), { preserveScroll: true, onSuccess: () => (form.status_note = '') });

const showEdit = ref(false);
const editForm = useForm(base());
const saveEdit = () => editForm.put(route('admin.orders.update', props.order.id), { preserveScroll: true, onSuccess: () => (showEdit.value = false) });

const note = useForm({ message: '', is_public: true });
const addNote = () => note.post(route('admin.orders.activity', props.order.id), { preserveScroll: true, onSuccess: () => note.reset('message') });

const task = useForm({ title: '', priority: 'medium', assigned_to: props.order.assigned_to, order_id: props.order.id, due_date: '', status: 'todo' });
const addTask = () => task.post(route('panel.tasks.store'), { preserveScroll: true, onSuccess: () => task.reset('title', 'due_date') });

const statusChanged = computed(() => form.status !== props.order.status);
const waText = computed(() => `Halo ${props.order.client_name}, kami dari ${'Kreatif Sky'} terkait request website Anda (${props.order.code}). `);

const copyTrack = async () => {
    try {
        await navigator.clipboard.writeText(props.trackUrl);
        toast('Tautan lacak disalin. Bagikan bersama email klien untuk verifikasi.');
    } catch {
        toast(props.trackUrl, 'info');
    }
};

const destroy = async () => {
    if (await confirm({ title: 'Hapus pesanan?', message: `Pesanan ${props.order.code} akan dipindahkan ke tempat sampah.`, danger: true })) {
        router.delete(route('admin.orders.destroy', props.order.id));
    }
};

const typeColor = { status: 'violet', comment: 'sky', progress: 'emerald', system: 'slate' };
const invoiceColors = { draft: 'slate', unpaid: 'amber', partial: 'sky', paid: 'emerald', cancelled: 'rose' };
</script>

<template>
    <Head :title="`Pesanan ${order.code}`" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <Link :href="route('admin.orders.index')" class="btn btn-ghost btn-sm"><ArrowLeft class="h-4 w-4" /> Semua pesanan</Link>
        <div class="flex flex-wrap gap-2">
            <a :href="waLink(order.client_phone, waText)" target="_blank" rel="noopener" class="btn btn-sm bg-[#25D366] text-white"><MessageCircle class="h-4 w-4" /> WhatsApp</a>
            <a :href="`mailto:${order.client_email}?subject=${encodeURIComponent('Request Website ' + order.code)}`" class="btn btn-secondary btn-sm"><Mail class="h-4 w-4" /> Email</a>
            <button type="button" class="btn btn-secondary btn-sm" @click="copyTrack"><Link2 class="h-4 w-4" /> Tautan lacak</button>
            <button type="button" class="btn btn-secondary btn-sm" @click="showEdit = true"><Pencil class="h-4 w-4" /> Ubah data</button>
            <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click="destroy"><Trash class="h-4 w-4" /></button>
        </div>
    </div>

    <div class="card-brutal mb-6 overflow-hidden">
        <div class="flex flex-wrap items-center gap-4 p-6">
            <Avatar :name="order.company_name || order.client_name" size="lg" />
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-muted">{{ order.code }} · {{ datetime(order.created_at) }} · via {{ order.source === 'referral' ? `referral ${order.referral_agent?.name}` : order.source }}</p>
                <h1 class="truncate font-display text-2xl font-bold">{{ order.company_name || order.client_name }}</h1>
                <p class="text-sm text-muted">{{ order.type_label }}<span v-if="order.package"> · Paket {{ order.package.name }}</span></p>
            </div>
            <StatusBadge :value="order.status" :map="catalog.orderStatuses" />
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <!-- Detail -->
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Detail request</h2>
                <dl class="mt-5 grid gap-5 text-sm sm:grid-cols-2">
                    <div class="flex gap-3"><Phone class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Kontak</dt><dd class="font-bold">{{ order.client_name }}</dd><dd>{{ order.client_phone }}</dd><dd class="break-all">{{ order.client_email }}</dd></div></div>
                    <div class="flex gap-3"><Wallet class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Budget</dt><dd class="font-bold">{{ catalog.budgetRanges[order.budget_range] || '—' }}</dd><dd v-if="order.quoted_price" class="text-xs text-muted">Penawaran: {{ rupiah(order.quoted_price) }}</dd></div></div>
                    <div class="flex gap-3"><CalendarClock class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Tenggat</dt><dd class="font-bold">{{ order.deadline ? date(order.deadline) : 'Fleksibel' }}</dd><dd class="text-xs text-muted">{{ catalog.pagesEstimates[order.pages_estimate] || '' }}</dd></div></div>
                    <div class="flex gap-3"><Palette class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Warna</dt><dd class="font-bold">{{ order.color_preference || '—' }}</dd></div></div>
                    <div class="flex gap-3"><Globe class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Domain</dt><dd class="font-bold">{{ order.has_domain ? 'Sudah punya' : 'Belum' }}</dd></div></div>
                    <div class="flex gap-3"><Server class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Hosting</dt><dd class="font-bold">{{ order.has_hosting ? 'Sudah punya' : 'Belum' }}</dd></div></div>
                </dl>
                <div v-if="order.features?.length" class="mt-6">
                    <p class="text-sm text-muted">Fitur diminta</p>
                    <div class="mt-2 flex flex-wrap gap-1.5"><Badge v-for="k in order.features" :key="k" color="violet"><Check class="h-3 w-3" /> {{ catalog.websiteFeatures[k] || k }}</Badge></div>
                </div>
                <div class="mt-6 rounded-2xl bg-surface-2/70 p-4">
                    <p class="text-sm text-muted">Cerita kebutuhan</p>
                    <p class="mt-1 text-sm leading-relaxed whitespace-pre-line">{{ order.description }}</p>
                </div>
                <div v-if="order.reference_urls" class="mt-4">
                    <p class="text-sm text-muted">Referensi</p>
                    <ul class="mt-1 space-y-1">
                        <li v-for="u in order.reference_urls.split(/\s+/).filter((x) => /^https?:\/\//.test(x))" :key="u"><a :href="u" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 text-sm font-semibold break-all text-violet-600 hover:underline dark:text-violet-300">{{ u }} <ExternalLink class="h-3.5 w-3.5 shrink-0" /></a></li>
                    </ul>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Timeline & catatan</h2>
                <form class="mt-4 rounded-2xl border-2 border-dashed border-line p-4" @submit.prevent="addNote">
                    <textarea v-model="note.message" rows="2" class="input" placeholder="Tulis update untuk klien atau catatan internal…"></textarea>
                    <p v-if="note.errors.message" class="field-error">{{ note.errors.message }}</p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <Switch v-model="note.is_public" :label="note.is_public ? 'Terlihat klien (halaman lacak)' : 'Catatan internal'" />
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="!note.message || note.processing"><Plus class="h-4 w-4" /> Tambahkan</button>
                    </div>
                </form>
                <ol class="relative mt-6 space-y-5 border-l-2 border-dashed border-line pl-6">
                    <li v-for="a in order.activities" :key="a.id" class="relative">
                        <span class="absolute top-0.5 -left-[35px] grid h-5 w-5 place-items-center rounded-full border-2 border-card bg-card"><span :class="a.is_public ? 'bg-emerald-500' : 'bg-slate-400'" class="h-2.5 w-2.5 rounded-full"></span></span>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <Badge :color="typeColor[a.type] || 'slate'" size="xs">{{ a.type }}</Badge>
                            <span class="font-bold">{{ a.user?.name || 'Sistem' }}</span>
                            <span class="text-muted">{{ datetime(a.created_at) }}</span>
                            <span class="inline-flex items-center gap-1 text-muted"><component :is="a.is_public ? Eye : EyeOff" class="h-3 w-3" /> {{ a.is_public ? 'publik' : 'internal' }}</span>
                        </div>
                        <p class="mt-1 text-sm whitespace-pre-line">{{ a.message }}</p>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <form class="card-brutal p-6" @submit.prevent="save">
                <h2 class="font-display text-lg font-bold">Kelola pesanan</h2>
                <div class="mt-4 space-y-4">
                    <FormField label="Status" :error="form.errors.status">
                        <select v-model="form.status" class="input"><option v-for="(s, k) in catalog.orderStatuses" :key="k" :value="k">{{ s.label }}</option></select>
                    </FormField>
                    <FormField v-if="statusChanged" label="Pesan untuk klien (opsional)" help="Ditampilkan di halaman lacak pesanan.">
                        <textarea v-model="form.status_note" rows="2" class="input"></textarea>
                    </FormField>
                    <FormField :label="`Progres: ${form.progress}%`" :error="form.errors.progress">
                        <input v-model.number="form.progress" type="range" min="0" max="100" step="5" class="w-full accent-violet-600" />
                    </FormField>
                    <FormField label="PIC" :error="form.errors.assigned_to">
                        <select v-model="form.assigned_to" class="input"><option :value="null">— Belum ditentukan —</option><option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option></select>
                    </FormField>
                    <FormField label="Prioritas"><select v-model="form.priority" class="input"><option v-for="(l, k) in catalog.orderPriorities" :key="k" :value="k">{{ l }}</option></select></FormField>
                    <FormField label="Harga penawaran" :error="form.errors.quoted_price"><MoneyInput v-model="form.quoted_price" /></FormField>
                    <FormField label="Catatan internal"><textarea v-model="form.internal_notes" rows="3" class="input"></textarea></FormField>
                </div>
                <button type="submit" class="btn btn-primary mt-5 w-full" :disabled="form.processing || !form.isDirty"><Save class="h-4 w-4" /> Simpan perubahan</button>
            </form>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 font-display text-lg font-bold"><Receipt class="h-5 w-5 text-violet-500" /> Invoice</h2>
                    <Link :href="route('admin.invoices.create', { order: order.id })" class="btn btn-secondary btn-sm"><Plus class="h-4 w-4" /> Buat</Link>
                </div>
                <p v-if="!order.invoices.length" class="mt-3 text-sm text-muted">Belum ada invoice.</p>
                <Link v-for="inv in order.invoices" :key="inv.id" :href="route('admin.invoices.show', inv.id)" class="mt-3 flex items-center justify-between rounded-2xl border border-line p-3 transition hover:border-ink">
                    <span><span class="block text-sm font-bold">{{ inv.number }}</span><span class="text-xs text-muted">{{ rupiah(inv.total) }}</span></span>
                    <Badge :color="invoiceColors[inv.status]">{{ inv.status_label }}</Badge>
                </Link>
            </div>

            <div class="card p-6">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><ListTodo class="h-5 w-5 text-violet-500" /> Tugas proyek</h2>
                <ul class="mt-3 space-y-2">
                    <li v-for="t in order.tasks" :key="t.id" class="flex items-center gap-2 text-sm">
                        <Check v-if="t.status === 'done'" class="h-4 w-4 text-emerald-500" />
                        <span v-else class="h-4 w-4 rounded-full border-2 border-line"></span>
                        <span :class="t.status === 'done' ? 'text-muted line-through' : ''" class="min-w-0 flex-1 truncate">{{ t.title }}</span>
                        <Avatar v-if="t.assignee" :user="t.assignee" size="xs" />
                    </li>
                </ul>
                <form class="mt-4 space-y-2" @submit.prevent="addTask">
                    <input v-model="task.title" type="text" class="input input-sm" placeholder="Tambah tugas, mis. Desain halaman beranda" />
                    <div class="flex gap-2">
                        <select v-model="task.assigned_to" class="input input-sm"><option :value="null">Tanpa PIC</option><option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option></select>
                        <button type="submit" class="btn btn-primary btn-sm shrink-0" :disabled="!task.title || task.processing"><Plus class="h-4 w-4" /></button>
                    </div>
                </form>
            </div>

            <div v-if="order.assignee" class="card flex items-center gap-3 p-5">
                <Avatar :user="order.assignee" size="md" />
                <div><p class="text-xs text-muted">PIC proyek</p><p class="font-bold">{{ order.assignee.name }}</p><p class="text-xs text-muted">{{ order.assignee.position }}</p></div>
                <Building class="ml-auto h-5 w-5 text-muted" />
            </div>
        </div>
    </div>

    <Modal :show="showEdit" title="Ubah data pesanan" size="lg" @close="showEdit = false">
        <OrderForm :form="editForm" :catalog="catalog" :packages="packages" :agents="agents" />
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showEdit = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="editForm.processing" @click="saveEdit">Simpan</button>
        </template>
    </Modal>
</template>
