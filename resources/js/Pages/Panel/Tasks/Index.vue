<script setup>
import { CalendarClock, GripVertical, Kanban, MessageSquare, Pencil, Plus, Send, Trash } from '@lucide/vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import { confirm } from '@/composables/useConfirm';
import { useFilters } from '@/composables/useFilters';
import { toast } from '@/composables/useToast';
import http from '@/lib/http';
import { date, relative } from '@/lib/format';

const props = defineProps({ tasks: Array, users: Array, orders: Array, filters: Object, statuses: Object, priorities: Object });
const page = usePage();
const me = computed(() => page.props.auth.user);
const isAdmin = computed(() => me.value?.role === 'admin');
const f = useFilters(props.filters, route('panel.tasks.index'));

const local = ref([]);
watch(() => props.tasks, (t) => (local.value = t.map((x) => ({ ...x }))), { immediate: true });

const columns = computed(() => Object.entries(props.statuses).map(([key, s]) => ({ key, ...s, items: local.value.filter((t) => t.status === key).sort((a, b) => a.position - b.position) })));
const colAccent = { todo: 'bg-slate-400', in_progress: 'bg-sky-500', review: 'bg-amber-400', done: 'bg-emerald-500' };

// Drag & drop
const dragging = ref(null);
const overCol = ref(null);
const onDragStart = (e, task) => {
    dragging.value = task;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(task.id));
};
const onDrop = (col, beforeTask = null) => {
    const task = dragging.value;
    overCol.value = null;
    dragging.value = null;
    if (!task) return;
    move(task, col, beforeTask);
};
const move = (task, col, beforeTask = null) => {
    const ids = local.value.filter((t) => t.status === col && t.id !== task.id).sort((a, b) => a.position - b.position).map((t) => t.id);
    const at = beforeTask ? ids.indexOf(beforeTask.id) : -1;
    if (at >= 0) ids.splice(at, 0, task.id);
    else ids.push(task.id);
    const t = local.value.find((x) => x.id === task.id);
    t.status = col;
    ids.forEach((id, i) => {
        const x = local.value.find((y) => y.id === id);
        if (x) x.position = i;
    });
    router.patch(route('panel.tasks.move', task.id), { status: col, order: ids }, { preserveScroll: true, preserveState: true, onError: () => toast('Gagal memindahkan tugas', 'error') });
};

// Form tugas
const showForm = ref(false);
const editing = ref(null);
const blank = (status = 'todo') => ({ title: '', description: '', order_id: null, assigned_to: isAdmin.value ? null : me.value.id, status, priority: 'medium', due_date: '' });
const form = useForm(blank());
const openCreate = (status = 'todo') => {
    editing.value = null;
    form.defaults(blank(status));
    form.reset();
    form.clearErrors();
    showForm.value = true;
};
const openEdit = (t) => {
    editing.value = t;
    form.defaults({ title: t.title, description: t.description || '', order_id: t.order_id, assigned_to: t.assigned_to, status: t.status, priority: t.priority, due_date: t.due_date || '' });
    form.reset();
    form.clearErrors();
    detail.value = null;
    showForm.value = true;
};
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => (showForm.value = false) };
    if (editing.value) form.put(route('panel.tasks.update', editing.value.id), opts);
    else form.post(route('panel.tasks.store'), opts);
};
const canEdit = (t) => isAdmin.value || t.created_by === me.value.id;

// Detail + komentar
const detail = ref(null);
const comment = ref('');
const sending = ref(false);
const openDetail = async (t) => {
    detail.value = { ...t, comments: null };
    try {
        const { data } = await http.get(route('panel.tasks.show', t.id));
        detail.value = data.task;
    } catch {
        toast('Gagal memuat detail tugas', 'error');
    }
};
const sendComment = async () => {
    if (!comment.value.trim() || !detail.value) return;
    sending.value = true;
    try {
        const { data } = await http.post(route('panel.tasks.comment', detail.value.id), { body: comment.value });
        detail.value.comments = [data.comment, ...(detail.value.comments || [])];
        const t = local.value.find((x) => x.id === detail.value.id);
        if (t) t.comments_count = (t.comments_count || 0) + 1;
        comment.value = '';
    } catch {
        toast('Komentar gagal dikirim', 'error');
    } finally {
        sending.value = false;
    }
};
const destroy = async (t) => {
    if (await confirm({ title: 'Hapus tugas?', message: t.title, danger: true })) {
        router.delete(route('panel.tasks.destroy', t.id), { preserveScroll: true, onSuccess: () => (detail.value = null) });
    }
};
</script>

<template>
    <Head title="Tugas" />
    <PageHeader title="Papan Tugas" :subtitle="isAdmin ? 'Kanban seluruh tim — seret kartu untuk mengubah status.' : 'Tugas kamu — seret kartu untuk memperbarui progres.'" :icon="Kanban">
        <button type="button" class="btn btn-primary" @click="openCreate()"><Plus class="h-4 w-4" /> Tugas baru</button>
    </PageHeader>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row">
        <SearchInput v-model="f.q" placeholder="Cari tugas…" />
        <select v-if="isAdmin" v-model="f.assignee" class="input input-sm sm:w-56"><option :value="null">Semua anggota</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
    </div>

    <div class="-mx-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:px-0">
        <div class="grid min-w-[1000px] grid-cols-4 gap-4">
            <section
                v-for="col in columns"
                :key="col.key"
                :class="overCol === col.key ? 'border-violet-500 bg-violet-50/70 dark:bg-violet-500/10' : 'border-line bg-surface-2/50'"
                class="flex min-h-[60vh] flex-col rounded-3xl border-2 border-dashed p-3 transition"
                @dragover.prevent="overCol = col.key"
                @dragleave.self="overCol = null"
                @drop.prevent="onDrop(col.key)"
            >
                <header class="mb-3 flex items-center justify-between px-1">
                    <h2 class="flex items-center gap-2 text-sm font-extrabold"><span :class="colAccent[col.key]" class="h-2.5 w-2.5 rounded-full"></span>{{ col.label }} <span class="rounded-full bg-card px-2 text-xs">{{ col.items.length }}</span></h2>
                    <button type="button" class="btn btn-ghost btn-icon !h-8 !w-8" :aria-label="`Tambah di ${col.label}`" @click="openCreate(col.key)"><Plus class="h-4 w-4" /></button>
                </header>
                <TransitionGroup tag="div" class="flex flex-1 flex-col gap-3" move-class="transition duration-300">
                    <article
                        v-for="t in col.items"
                        :key="t.id"
                        draggable="true"
                        :class="[dragging?.id === t.id ? 'opacity-40' : '', t.is_overdue ? 'border-rose-300 dark:border-rose-500/40' : 'border-line']"
                        class="group cursor-grab rounded-2xl border-2 bg-card p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-ink hover:shadow-brutal-sm active:cursor-grabbing"
                        @dragstart="onDragStart($event, t)"
                        @dragend="dragging = null; overCol = null"
                        @drop.prevent.stop="onDrop(col.key, t)"
                        @click="openDetail(t)"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <Badge :color="priorities[t.priority]?.color" size="xs">{{ priorities[t.priority]?.label }}</Badge>
                            <GripVertical class="h-4 w-4 text-muted opacity-0 transition group-hover:opacity-100" />
                        </div>
                        <h3 :class="t.status === 'done' ? 'text-muted line-through' : ''" class="mt-2 text-sm leading-snug font-bold">{{ t.title }}</h3>
                        <p v-if="t.order" class="mt-1 truncate text-xs font-semibold text-violet-600 dark:text-violet-300">{{ t.order.code }} · {{ t.order.client_name }}</p>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span v-if="t.due_date" :class="t.is_overdue ? 'text-rose-500' : 'text-muted'" class="inline-flex items-center gap-1 text-xs font-bold"><CalendarClock class="h-3.5 w-3.5" /> {{ date(t.due_date, { day: 'numeric', month: 'short' }) }}</span>
                            <span v-else></span>
                            <span class="flex items-center gap-2">
                                <span v-if="t.comments_count" class="inline-flex items-center gap-1 text-xs text-muted"><MessageSquare class="h-3.5 w-3.5" /> {{ t.comments_count }}</span>
                                <Avatar v-if="t.assignee" :user="t.assignee" size="xs" />
                            </span>
                        </div>
                        <select class="input input-sm mt-3 lg:hidden" :value="t.status" aria-label="Pindahkan ke" @click.stop @change="move(t, $event.target.value)">
                            <option v-for="(s, k) in statuses" :key="k" :value="k">→ {{ s.label }}</option>
                        </select>
                    </article>
                </TransitionGroup>
                <p v-if="!col.items.length" class="py-8 text-center text-xs font-semibold text-muted">Seret tugas ke sini</p>
            </section>
        </div>
    </div>

    <Modal :show="showForm" :title="editing ? 'Ubah tugas' : 'Tugas baru'" size="md" @close="showForm = false">
        <div class="grid gap-4 sm:grid-cols-2">
            <FormField class="sm:col-span-2" label="Judul" :error="form.errors.title" required><input v-model="form.title" type="text" class="input" maxlength="200" /></FormField>
            <FormField class="sm:col-span-2" label="Deskripsi" :error="form.errors.description"><textarea v-model="form.description" rows="3" class="input"></textarea></FormField>
            <FormField label="Prioritas" :error="form.errors.priority"><select v-model="form.priority" class="input"><option v-for="(p, k) in priorities" :key="k" :value="k">{{ p.label }}</option></select></FormField>
            <FormField label="Tenggat" :error="form.errors.due_date"><input v-model="form.due_date" type="date" class="input" /></FormField>
            <FormField v-if="isAdmin" label="Ditugaskan ke" :error="form.errors.assigned_to"><select v-model="form.assigned_to" class="input"><option :value="null">— Belum —</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></FormField>
            <FormField label="Status"><select v-model="form.status" class="input"><option v-for="(s, k) in statuses" :key="k" :value="k">{{ s.label }}</option></select></FormField>
            <FormField class="sm:col-span-2" label="Terkait pesanan" :error="form.errors.order_id"><select v-model="form.order_id" class="input"><option :value="null">— Tidak terkait —</option><option v-for="o in orders" :key="o.id" :value="o.id">{{ o.code }} · {{ o.client_name }}</option></select></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showForm = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing" @click="submit">Simpan</button>
        </template>
    </Modal>

    <Modal :show="!!detail" :title="detail?.title || ''" size="lg" @close="detail = null">
        <template v-if="detail">
            <div class="flex flex-wrap items-center gap-2">
                <Badge :color="statuses[detail.status]?.color" dot>{{ statuses[detail.status]?.label }}</Badge>
                <Badge :color="priorities[detail.priority]?.color">{{ priorities[detail.priority]?.label }}</Badge>
                <span v-if="detail.due_date" class="chip"><CalendarClock class="h-3.5 w-3.5" /> {{ date(detail.due_date) }}</span>
                <span v-if="detail.assignee" class="chip"><Avatar :user="detail.assignee" size="xs" /> {{ detail.assignee.name }}</span>
            </div>
            <p class="mt-4 text-sm whitespace-pre-line">{{ detail.description || 'Tidak ada deskripsi.' }}</p>
            <p v-if="detail.creator" class="mt-3 text-xs text-muted">Dibuat oleh {{ detail.creator.name }} · {{ relative(detail.created_at) }}</p>

            <div class="mt-6 border-t border-line pt-5">
                <h4 class="font-bold">Komentar</h4>
                <form class="mt-3 flex gap-2" @submit.prevent="sendComment">
                    <input v-model="comment" type="text" class="input input-sm" placeholder="Tulis komentar / update…" maxlength="2000" />
                    <button type="submit" class="btn btn-primary btn-sm shrink-0" :disabled="sending || !comment.trim()" aria-label="Kirim"><Send class="h-4 w-4" /></button>
                </form>
                <p v-if="detail.comments === null" class="mt-4 text-sm text-muted">Memuat…</p>
                <ul v-else class="mt-4 space-y-3">
                    <li v-for="c in detail.comments" :key="c.id" class="flex gap-3">
                        <Avatar :user="c.user" size="sm" />
                        <div class="min-w-0 flex-1 rounded-2xl bg-surface-2/70 px-4 py-2.5"><p class="text-xs"><b>{{ c.user?.name }}</b> <span class="text-muted">· {{ relative(c.created_at) }}</span></p><p class="mt-0.5 text-sm whitespace-pre-line">{{ c.body }}</p></div>
                    </li>
                    <li v-if="!detail.comments?.length" class="text-sm text-muted">Belum ada komentar.</li>
                </ul>
            </div>
        </template>
        <template v-if="detail && canEdit(detail)" #footer>
            <button type="button" class="btn btn-ghost text-rose-500" @click="destroy(detail)"><Trash class="h-4 w-4" /> Hapus</button>
            <button type="button" class="btn btn-secondary" @click="openEdit(detail)"><Pencil class="h-4 w-4" /> Ubah</button>
        </template>
    </Modal>
</template>
