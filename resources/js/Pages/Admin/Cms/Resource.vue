<script setup>
import { Check, ExternalLink, LayoutList, Pencil, Plus, Star, Trash } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import DynamicIcon from '@/Components/ui/DynamicIcon.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import MoneyInput from '@/Components/ui/MoneyInput.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import Switch from '@/Components/ui/Switch.vue';
import TagsInput from '@/Components/ui/TagsInput.vue';
import { confirm } from '@/composables/useConfirm';
import { useFilters } from '@/composables/useFilters';
import { gradient, solid } from '@/lib/colors';
import { date, rupiah } from '@/lib/format';

const props = defineProps({
    resource: String,
    schema: Object,
    options: Object,
    items: Object,
    filters: Object,
    menu: Array,
    colors: Array,
    icons: Array,
});

const f = useFilters(props.filters, route('admin.cms.index', props.resource));

const editing = ref(null);
const show = ref(false);
const imageFields = computed(() => props.schema.fields.filter((x) => x.type === 'image'));

const blank = () => {
    const data = {};
    for (const fld of props.schema.fields) {
        if (fld.type === 'image') {
            data[fld.name] = null;
            data[`remove_${fld.name}`] = false;
        } else if (fld.type === 'list') data[fld.name] = [];
        else if (fld.type === 'switch') data[fld.name] = false;
        else if (fld.type === 'select') data[fld.name] = null;
        else data[fld.name] = '';
    }
    return { ...data, ...JSON.parse(JSON.stringify(props.schema.defaults || {})) };
};

const form = useForm(blank());

const toLocalInput = (v) => {
    if (!v) return '';
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return '';
    const p = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
};

const openCreate = () => {
    editing.value = null;
    form.defaults(blank());
    form.reset();
    form.clearErrors();
    show.value = true;
};

const openEdit = (item) => {
    editing.value = item;
    const data = blank();
    for (const fld of props.schema.fields) {
        if (fld.type === 'image') continue;
        let v = item[fld.name];
        if (fld.type === 'list') v = Array.isArray(v) ? [...v] : [];
        else if (fld.type === 'switch') v = !!v;
        else if (fld.type === 'datetime') v = toLocalInput(v);
        else if (fld.type === 'date') v = v ? String(v).slice(0, 10) : '';
        else if (v === null || v === undefined) v = fld.type === 'select' ? null : '';
        data[fld.name] = v;
    }
    form.defaults(data);
    form.reset();
    form.clearErrors();
    show.value = true;
};

const submit = () => {
    const url = editing.value ? route('admin.cms.update', [props.resource, editing.value.id]) : route('admin.cms.store', props.resource);
    // datetime-local dikirim apa adanya (waktu lokal WIB) agar tidak bergeser zona waktu.
    form.post(url, {
        forceFormData: imageFields.value.length > 0,
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
};

const toggle = (item) => router.patch(route('admin.cms.toggle', [props.resource, item.id]), {}, { preserveScroll: true });
const destroy = async (item) => {
    const label = item.title || item.name || item.question;
    if (await confirm({ title: `Hapus ${props.schema.singular}?`, message: `"${label}" akan dihapus permanen.`, danger: true })) {
        router.delete(route('admin.cms.destroy', [props.resource, item.id]), { preserveScroll: true });
    }
};

const currentImage = (fld) => (editing.value && !form[`remove_${fld.name}`] ? editing.value[fld.url_key] : '');
const selectOptions = (fld) => fld.options || props.options?.[fld.name] || {};
</script>

<template>
    <Head :title="schema.title" />
    <PageHeader :title="schema.title" :subtitle="schema.description" :icon="LayoutList">
        <button type="button" class="btn btn-primary" @click="openCreate"><Plus class="h-4 w-4" /> Tambah {{ schema.singular }}</button>
    </PageHeader>

    <div class="-mx-1 mb-5 overflow-x-auto px-1">
        <div class="flex gap-2 pb-1">
            <Link
                v-for="m in menu"
                :key="m.key"
                :href="route('admin.cms.index', m.key)"
                :class="m.key === resource ? 'border-ink bg-ink text-surface' : 'border-line bg-card hover:border-ink'"
                class="shrink-0 rounded-xl border-2 px-3 py-1.5 text-sm font-bold transition"
                >{{ m.title }}</Link
            >
        </div>
    </div>

    <div class="mb-4"><SearchInput v-model="f.q" :placeholder="`Cari ${schema.singular}…`" /></div>

    <div class="table-wrap">
        <table v-if="items.data.length" class="table">
            <thead>
                <tr>
                    <th v-for="c in schema.columns" :key="c.key">{{ c.label }}</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items.data" :key="item.id">
                    <td v-for="c in schema.columns" :key="c.key">
                        <div v-if="c.type === 'title'" class="flex min-w-[220px] items-center gap-3">
                            <img v-if="c.image && item[c.image]" :src="item[c.image]" alt="" class="h-11 w-11 shrink-0 rounded-xl object-cover" />
                            <span v-else-if="c.icon" :class="gradient[item[c.color]] || gradient.violet" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-white"><DynamicIcon :name="item[c.icon]" class="h-5 w-5" /></span>
                            <Avatar v-else-if="c.image" :name="item[c.key]" size="md" />
                            <span v-else-if="c.color" :class="solid[item[c.color]] || solid.violet" class="h-3 w-3 shrink-0 rounded-full"></span>
                            <span class="min-w-0">
                                <span class="block truncate font-bold">{{ item[c.key] }}</span>
                                <span v-if="c.sub && item[c.sub]" class="line-clamp-1 block max-w-md text-xs text-muted">{{ item[c.sub] }}</span>
                            </span>
                        </div>
                        <span v-else-if="c.type === 'money'" class="font-bold whitespace-nowrap">{{ item[c.key] > 0 ? rupiah(item[c.key]) : 'Custom' }}</span>
                        <Check v-else-if="c.type === 'bool'" :class="item[c.key] ? 'text-emerald-500' : 'text-line'" class="h-5 w-5" />
                        <button v-else-if="c.type === 'toggle'" type="button" :class="item[c.key] ? 'bg-gradient-to-r from-violet-500 to-fuchsia-500' : 'bg-line'" class="relative inline-flex h-6 w-11 items-center rounded-full transition" :aria-label="`Ubah ${c.label}`" @click="toggle(item)">
                            <span :class="item[c.key] ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 rounded-full bg-white shadow transition"></span>
                        </button>
                        <Badge v-else-if="c.type === 'badge'" :color="c.map?.[item[c.key]]?.[1] || 'slate'">{{ c.map?.[item[c.key]]?.[0] || item[c.key] }}</Badge>
                        <span v-else-if="c.type === 'date'" class="text-sm whitespace-nowrap text-muted">{{ date(item[c.key]) }}</span>
                        <span v-else-if="c.type === 'rating'" class="flex text-amber-400"><Star v-for="n in item[c.key]" :key="n" class="h-4 w-4 fill-current" /></span>
                        <span v-else class="text-sm">{{ item[c.key] ?? '—' }}</span>
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <a v-if="item.public_url" :href="item.public_url" target="_blank" class="btn btn-ghost btn-sm" aria-label="Lihat di website"><ExternalLink class="h-4 w-4" /></a>
                        <button type="button" class="btn btn-ghost btn-sm" aria-label="Ubah" @click="openEdit(item)"><Pencil class="h-4 w-4" /></button>
                        <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="destroy(item)"><Trash class="h-4 w-4" /></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else :title="`Belum ada ${schema.singular}`" description="Klik tombol tambah untuk membuat data pertama.">
            <button type="button" class="btn btn-primary" @click="openCreate"><Plus class="h-4 w-4" /> Tambah</button>
        </EmptyState>
    </div>
    <div class="mt-5"><Pagination :paginator="items" /></div>

    <Modal :show="show" :title="`${editing ? 'Ubah' : 'Tambah'} ${schema.singular}`" size="lg" @close="show = false">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
            <template v-for="fld in schema.fields" :key="fld.name">
                <FormField :class="fld.span === 2 ? 'sm:col-span-2' : ''" :label="fld.type === 'switch' ? '' : fld.label" :help="fld.help" :error="form.errors[fld.name] || form.errors[`${fld.name}.0`]">
                    <input v-if="['text', 'url'].includes(fld.type)" v-model="form[fld.name]" :type="fld.type === 'url' ? 'url' : 'text'" class="input" :class="{ 'input-error': form.errors[fld.name] }" />
                    <input v-else-if="fld.type === 'number'" v-model.number="form[fld.name]" type="number" class="input" />
                    <MoneyInput v-else-if="fld.type === 'money'" v-model="form[fld.name]" :invalid="!!form.errors[fld.name]" />
                    <input v-else-if="fld.type === 'date'" v-model="form[fld.name]" type="date" class="input" />
                    <input v-else-if="fld.type === 'datetime'" v-model="form[fld.name]" type="datetime-local" class="input" />
                    <textarea v-else-if="fld.type === 'textarea'" v-model="form[fld.name]" :rows="fld.rows || 3" class="input"></textarea>
                    <div v-else-if="fld.type === 'markdown'">
                        <textarea v-model="form[fld.name]" :rows="fld.rows || 8" class="input font-mono text-[13px] leading-relaxed"></textarea>
                        <p class="help">Mendukung Markdown: <code>## Judul</code>, <code>**tebal**</code>, <code>- daftar</code>, <code>[tautan](https://…)</code></p>
                    </div>
                    <select v-else-if="fld.type === 'select'" v-model="form[fld.name]" class="input">
                        <option :value="null">— Pilih —</option>
                        <option v-for="(label, val) in selectOptions(fld)" :key="val" :value="isNaN(Number(val)) ? val : Number(val)">{{ label }}</option>
                    </select>
                    <Switch v-else-if="fld.type === 'switch'" v-model="form[fld.name]" :label="fld.label" />
                    <TagsInput v-else-if="fld.type === 'list'" v-model="form[fld.name]" />
                    <div v-else-if="fld.type === 'color'" class="flex flex-wrap gap-2">
                        <button v-for="c in colors" :key="c" type="button" :class="[solid[c], form[fld.name] === c ? 'ring-4 ring-offset-2 ring-violet-400 ring-offset-card scale-110' : '']" class="h-8 w-8 rounded-full transition" :aria-label="c" :title="c" @click="form[fld.name] = c"></button>
                    </div>
                    <div v-else-if="fld.type === 'icon'" class="grid grid-cols-6 gap-1.5 sm:grid-cols-8">
                        <button v-for="ic in icons" :key="ic" type="button" :class="form[fld.name] === ic ? 'border-ink bg-yellow-300 text-zinc-900' : 'border-line hover:border-ink'" class="grid aspect-square place-items-center rounded-xl border-2 transition" :title="ic" @click="form[fld.name] = ic"><DynamicIcon :name="ic" class="h-5 w-5" /></button>
                    </div>
                    <FileDrop
                        v-else-if="fld.type === 'image'"
                        v-model="form[fld.name]"
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                        :max-mb="4"
                        image
                        :current-url="currentImage(fld)"
                        hint="JPG/PNG/WebP, maks. 4 MB. Otomatis dioptimasi ke WebP."
                        @remove-current="form[`remove_${fld.name}`] = true"
                    />
                </FormField>
            </template>
        </form>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="show = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing" @click="submit">{{ form.processing ? 'Menyimpan…' : 'Simpan' }}</button>
        </template>
    </Modal>
</template>
