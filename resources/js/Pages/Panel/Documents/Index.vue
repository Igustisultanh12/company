<script setup>
import {
    Archive, CalendarClock, Download, Eye, File, FileImage, FileSpreadsheet, FileText, FolderOpen, HardDrive, Lock,
    Pencil, Plus, Settings2, Tag, Trash, Upload,
} from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import Switch from '@/Components/ui/Switch.vue';
import TagsInput from '@/Components/ui/TagsInput.vue';
import { confirm } from '@/composables/useConfirm';
import { useFilters } from '@/composables/useFilters';
import { solid } from '@/lib/colors';
import { bytes, date } from '@/lib/format';

const props = defineProps({ documents: Object, categories: Array, filters: Object, canManage: Boolean, confidentiality: Object, complianceOptions: Array, stats: Object });
const f = useFilters(props.filters, route('panel.documents.index'));

const iconFor = (ext) => (['pdf', 'doc', 'docx', 'txt', 'odt'].includes(ext) ? FileText : ['xls', 'xlsx', 'csv', 'ods'].includes(ext) ? FileSpreadsheet : ['png', 'jpg', 'jpeg', 'webp'].includes(ext) ? FileImage : File);
const extColor = (ext) => (ext === 'pdf' ? 'from-rose-400 to-pink-500' : ['xls', 'xlsx', 'csv'].includes(ext) ? 'from-emerald-400 to-teal-500' : ['doc', 'docx'].includes(ext) ? 'from-sky-400 to-indigo-500' : ['png', 'jpg', 'jpeg', 'webp'].includes(ext) ? 'from-amber-400 to-orange-500' : 'from-slate-400 to-slate-500');

// Upload / edit
const show = ref(false);
const editing = ref(null);
const blank = () => ({ title: '', document_number: '', category_id: null, description: '', document_date: '', expires_at: '', confidentiality: 'internal', tags: [], file: null, compliance: [] });
const form = useForm(blank());

const splitTags = (tags = []) => ({
    compliance: tags.filter((t) => t.startsWith('compliance:')).map((t) => t.slice(11)),
    plain: tags.filter((t) => !t.startsWith('compliance:')),
});
const openCreate = () => {
    editing.value = null;
    form.defaults(blank());
    form.reset();
    form.clearErrors();
    show.value = true;
};
const openEdit = (d) => {
    editing.value = d;
    const t = splitTags(d.tags || []);
    form.defaults({ title: d.title, document_number: d.document_number || '', category_id: d.category_id, description: d.description || '', document_date: d.document_date || '', expires_at: d.expires_at || '', confidentiality: d.confidentiality, tags: t.plain, file: null, compliance: t.compliance });
    form.reset();
    form.clearErrors();
    show.value = true;
};
const submit = () => {
    form.transform((d) => {
        const { compliance, ...rest } = d;
        return { ...rest, tags: [...d.tags, ...compliance.map((c) => `compliance:${c}`)], file: d.file || undefined };
    }).post(editing.value ? route('admin.documents.update', editing.value.id) : route('admin.documents.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
};
const destroy = async (d) => {
    if (await confirm({ title: 'Hapus dokumen permanen?', message: `${d.title} (${d.code}) beserta berkasnya akan dihapus.`, danger: true })) router.delete(route('admin.documents.destroy', d.id), { preserveScroll: true });
};

// Kategori
const showCats = ref(false);
const cat = useForm({ id: null, name: '', color: 'violet', description: '' });
const editCat = (c) => {
    cat.id = c.id;
    cat.name = c.name;
    cat.color = c.color;
    cat.description = c.description || '';
};
const saveCat = () => {
    const opts = { preserveScroll: true, onSuccess: () => cat.reset() };
    if (cat.id) cat.put(route('admin.documents.categories.update', cat.id), opts);
    else cat.post(route('admin.documents.categories.store'), opts);
};
const deleteCat = async (c) => {
    if (await confirm({ title: 'Hapus kategori?', message: `${c.name} — dokumen di dalamnya menjadi tanpa kategori.`, danger: true })) router.delete(route('admin.documents.categories.destroy', c.id), { preserveScroll: true });
};
const colors = ['violet', 'fuchsia', 'pink', 'rose', 'orange', 'amber', 'lime', 'emerald', 'teal', 'cyan', 'sky', 'indigo', 'slate'];
const confColor = { public: 'emerald', internal: 'sky', confidential: 'rose' };
</script>

<template>
    <Head title="Arsip Dokumen" />
    <PageHeader title="Arsip Dokumen" subtitle="Penyimpanan dokumen perusahaan yang aman, terkategori & mudah dicari." :icon="Archive">
        <template v-if="canManage">
            <button type="button" class="btn btn-secondary" @click="showCats = true"><Settings2 class="h-4 w-4" /> Kategori</button>
            <button type="button" class="btn btn-primary" @click="openCreate"><Upload class="h-4 w-4" /> Unggah dokumen</button>
        </template>
    </PageHeader>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Total dokumen" :value="stats.total" :icon="Archive" color="violet" />
        <StatCard label="Ukuran arsip" :value="bytes(stats.size)" :icon="HardDrive" color="sky" />
        <StatCard label="Diunggah bulan ini" :value="stats.this_month" :icon="Plus" color="emerald" />
        <StatCard label="Kedaluwarsa ≤ 60 hari" :value="stats.expiring" :icon="CalendarClock" color="rose" />
    </div>

    <div class="grid gap-6 lg:grid-cols-[250px_1fr]">
        <aside class="card h-max p-3 lg:sticky lg:top-24">
            <p class="px-2 pb-2 text-xs font-extrabold tracking-wider text-muted uppercase">Kategori</p>
            <button type="button" :class="!f.category ? 'bg-ink text-surface' : 'hover:bg-surface-2'" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold transition" @click="f.category = null">
                <span class="flex items-center gap-2"><FolderOpen class="h-4 w-4" /> Semua</span>
            </button>
            <button v-for="c in categories" :key="c.id" type="button" :class="f.category === c.id ? 'bg-ink text-surface' : 'hover:bg-surface-2'" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold transition" @click="f.category = c.id">
                <span class="flex min-w-0 items-center gap-2"><span :class="solid[c.color]" class="h-2.5 w-2.5 shrink-0 rounded-full"></span><span class="truncate">{{ c.name }}</span></span>
                <span class="text-xs opacity-70">{{ c.documents_count }}</span>
            </button>
        </aside>

        <div>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <SearchInput v-model="f.q" placeholder="Cari judul, nomor, kode…" />
                <select v-if="canManage" v-model="f.confidentiality" class="input input-sm sm:w-44"><option value="">Semua akses</option><option v-for="(c, k) in confidentiality" :key="k" :value="k">{{ c.label }}</option></select>
                <input v-model="f.year" type="number" min="2000" max="2100" class="input input-sm sm:w-28" placeholder="Tahun" />
                <Switch v-model="f.expiring" label="Segera kedaluwarsa" />
            </div>

            <div v-if="documents.data.length" class="space-y-3">
                <div v-for="d in documents.data" :key="d.id" class="card flex flex-col gap-4 p-4 transition hover:border-violet-300 sm:flex-row sm:items-center">
                    <span :class="extColor(d.extension)" class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br text-white"><component :is="iconFor(d.extension)" class="h-6 w-6" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2"><span class="truncate font-bold">{{ d.title }}</span><Lock v-if="d.confidentiality === 'confidential'" class="h-3.5 w-3.5 text-rose-500" /></p>
                        <p class="text-xs text-muted">{{ d.code }}<span v-if="d.document_number"> · No. {{ d.document_number }}</span> · {{ d.extension.toUpperCase() }} {{ bytes(d.size) }}<span v-if="d.document_date"> · {{ date(d.document_date) }}</span></p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <Badge v-if="d.category" :color="d.category.color" size="xs">{{ d.category.name }}</Badge>
                            <Badge :color="confColor[d.confidentiality]" size="xs">{{ confidentiality[d.confidentiality]?.label }}</Badge>
                            <Badge v-if="d.expires_at" :color="d.is_expired ? 'rose' : 'amber'" size="xs"><CalendarClock class="h-3 w-3" /> {{ d.is_expired ? 'Kedaluwarsa' : 's.d.' }} {{ date(d.expires_at) }}</Badge>
                            <span v-for="t in (d.tags || []).filter((x) => !x.startsWith('compliance:'))" :key="t" class="inline-flex items-center gap-1 rounded-full bg-surface-2 px-2 py-0.5 text-[11px] font-semibold"><Tag class="h-3 w-3" />{{ t }}</span>
                            <Badge v-for="t in (d.tags || []).filter((x) => x.startsWith('compliance:'))" :key="t" color="lime" size="xs">✓ kepatuhan</Badge>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <a v-if="d.is_previewable" :href="route('panel.documents.file', [d.id, 'view'])" target="_blank" class="btn btn-ghost btn-sm" aria-label="Lihat"><Eye class="h-4 w-4" /></a>
                        <a :href="route('panel.documents.file', [d.id, 'download'])" class="btn btn-ghost btn-sm" aria-label="Unduh"><Download class="h-4 w-4" /></a>
                        <template v-if="canManage">
                            <button type="button" class="btn btn-ghost btn-sm" aria-label="Ubah" @click="openEdit(d)"><Pencil class="h-4 w-4" /></button>
                            <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="destroy(d)"><Trash class="h-4 w-4" /></button>
                        </template>
                    </div>
                </div>
            </div>
            <EmptyState v-else title="Belum ada dokumen" :description="canManage ? 'Unggah dokumen legalitas, kontrak, SOP & lainnya.' : 'Dokumen yang dibagikan akan tampil di sini.'">
                <button v-if="canManage" type="button" class="btn btn-primary" @click="openCreate"><Upload class="h-4 w-4" /> Unggah</button>
            </EmptyState>
            <div class="mt-5"><Pagination :paginator="documents" /></div>
        </div>
    </div>

    <Modal :show="show" :title="editing ? 'Ubah dokumen' : 'Unggah dokumen'" size="lg" @close="show = false">
        <div class="grid gap-4 sm:grid-cols-2">
            <FormField class="sm:col-span-2" :label="editing ? 'Ganti berkas (opsional)' : 'Berkas'" :error="form.errors.file" :required="!editing">
                <FileDrop v-model="form.file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip,.rar" :max-mb="50" hint="PDF, Office, gambar, ZIP — maks. 50 MB" />
            </FormField>
            <FormField class="sm:col-span-2" label="Judul dokumen" :error="form.errors.title" required><input v-model="form.title" type="text" class="input" /></FormField>
            <FormField label="Nomor dokumen" :error="form.errors.document_number"><input v-model="form.document_number" type="text" class="input" /></FormField>
            <FormField label="Kategori" :error="form.errors.category_id"><select v-model="form.category_id" class="input"><option :value="null">— Tanpa kategori —</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></FormField>
            <FormField label="Tanggal dokumen" :error="form.errors.document_date"><input v-model="form.document_date" type="date" class="input" /></FormField>
            <FormField label="Berlaku sampai (opsional)" :error="form.errors.expires_at"><input v-model="form.expires_at" type="date" class="input" /></FormField>
            <FormField class="sm:col-span-2" label="Tingkat akses" :error="form.errors.confidentiality">
                <div class="grid gap-2 sm:grid-cols-3">
                    <button v-for="(c, k) in confidentiality" :key="k" type="button" :class="form.confidentiality === k ? 'border-ink bg-surface-2 shadow-brutal-sm' : 'border-line'" class="rounded-2xl border-2 p-3 text-left transition" @click="form.confidentiality = k">
                        <Badge :color="c.color">{{ c.label }}</Badge>
                        <p class="mt-1 text-xs text-muted">{{ c.desc }}</p>
                    </button>
                </div>
            </FormField>
            <FormField class="sm:col-span-2" label="Kewajiban perizinan terkait" help="Tandai bila dokumen ini menjadi bukti pemenuhan kewajiban Sertifikat Standar / SPPL.">
                <div class="flex flex-wrap gap-1.5">
                    <label v-for="c in complianceOptions" :key="c.key" :class="form.compliance.includes(c.key) ? 'border-lime-500 bg-lime-100 text-lime-900 dark:bg-lime-500/15 dark:text-lime-200' : 'border-line'" class="cursor-pointer rounded-full border-2 px-3 py-1 text-xs font-bold transition">
                        <input v-model="form.compliance" type="checkbox" :value="c.key" class="hidden" /> {{ c.title }}
                    </label>
                </div>
            </FormField>
            <FormField class="sm:col-span-2" label="Tag"><TagsInput v-model="form.tags" /></FormField>
            <FormField class="sm:col-span-2" label="Keterangan" :error="form.errors.description"><textarea v-model="form.description" rows="2" class="input"></textarea></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="show = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing || (!editing && !form.file)" @click="submit">{{ form.processing ? `Mengunggah… ${form.progress?.percentage || 0}%` : 'Simpan' }}</button>
        </template>
    </Modal>

    <Modal :show="showCats" title="Kategori arsip" size="md" @close="showCats = false">
        <ul class="space-y-2">
            <li v-for="c in categories" :key="c.id" class="flex items-center gap-3 rounded-2xl border border-line p-3">
                <span :class="solid[c.color]" class="h-3 w-3 rounded-full"></span>
                <span class="min-w-0 flex-1"><span class="block font-bold">{{ c.name }}</span><span class="block truncate text-xs text-muted">{{ c.description }}</span></span>
                <button type="button" class="btn btn-ghost btn-sm" aria-label="Ubah" @click="editCat(c)"><Pencil class="h-4 w-4" /></button>
                <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="deleteCat(c)"><Trash class="h-4 w-4" /></button>
            </li>
        </ul>
        <form class="mt-5 space-y-3 rounded-2xl border-2 border-dashed border-line p-4" @submit.prevent="saveCat">
            <p class="text-sm font-bold">{{ cat.id ? 'Ubah kategori' : 'Kategori baru' }}</p>
            <input v-model="cat.name" type="text" class="input input-sm" placeholder="Nama kategori" />
            <p v-if="cat.errors.name" class="field-error">{{ cat.errors.name }}</p>
            <input v-model="cat.description" type="text" class="input input-sm" placeholder="Deskripsi (opsional)" />
            <div class="flex flex-wrap gap-1.5"><button v-for="c in colors" :key="c" type="button" :class="[solid[c], cat.color === c ? 'ring-4 ring-violet-300' : '']" class="h-6 w-6 rounded-full" :aria-label="c" @click="cat.color = c"></button></div>
            <div class="flex gap-2"><button type="submit" class="btn btn-primary btn-sm" :disabled="!cat.name">Simpan</button><button v-if="cat.id" type="button" class="btn btn-ghost btn-sm" @click="cat.reset()">Batal ubah</button></div>
        </form>
    </Modal>
</template>
